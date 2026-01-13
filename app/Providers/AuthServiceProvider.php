<?php

namespace App\Providers;

use App\Helpers\ContextualAuth;
use App\Models\CalificadorGeneralCarreraPeriodo;
use App\Models\PlanEvaluacion;
use App\Models\User;
use App\Models\CarrerasPeriodo;
use App\Models\Tribunale; // Asegúrate que el namespace sea App\Models\Tribunale
use App\Models\MiembrosTribunale; // Para verificar el rol de presidente
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Model::class => ModelPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Gate para 'configurar plan evaluacion' (contextual)
        Gate::define('configurar-plan-para-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('configurar plan evaluacion')) { // Primero, ¿tiene el permiso base?
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        // Gate para CRUD de tribunales (contextual)
        Gate::define('gestionar-tribunales-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('ver listado tribunales')) { // O un permiso más genérico
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        // Gate para ver los detalles de un tribunal específico donde el usuario es miembro
        Gate::define('ver-detalles-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('ver detalles mi tribunal')) {
                return ContextualAuth::isMemberOfTribunal($user, $tribunal->id);
            }
            return false;
        });

        // Gate para calificar (ingresar/editar propias calificaciones)
        Gate::define('calificar-este-tribunal', function (User $user, Tribunale $tribunal) {
            // Verificar permisos base de calificación
            if ($user->hasPermissionTo('calificar mi tribunal') || $user->hasPermissionTo('calificar en tribunal')) {
                // Verificar si es miembro de ESTE tribunal
                if (ContextualAuth::isMemberOfTribunal($user, $tribunal->id)) {
                    return true;
                }

                // Verificar si tiene asignaciones de calificación en el plan de evaluación de este tribunal
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    if (ContextualAuth::isDirectorOf($user, $carreraPeriodo->id) ||
                        ContextualAuth::isApoyoOf($user, $carreraPeriodo->id)) {
                        return true;
                    }

                    $esCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
                        ->where('user_id', $user->id)->exists();

                    if ($esCalificadorGeneral) {
                        return true;
                    }
                }
            }
            return false;
        });

        // Gate para que el Presidente edite datos básicos de SU tribunal
        Gate::define('editar-datos-basicos-este-tribunal-como-presidente', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('editar datos basicos mi tribunal (presidente)')) {
                return ContextualAuth::isPresidentOfTribunal($user, $tribunal->id);
            }
            return false;
        });

        // Gate para que el Presidente exporte el acta de SU tribunal
        Gate::define('exportar-acta-este-tribunal-como-presidente', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('exportar acta mi tribunal (presidente)')) {
                return ContextualAuth::isPresidentOfTribunal($user, $tribunal->id);
            }
            return false;
        });

        //Gate para que cualquier miembro de tribunal, calificador general director o apoyo pueda exportar el acta de SU tribunal
        // En AuthServiceProvider.php
        Gate::define('puede-exportar-acta-de-este-tribunal', function (User $user, Tribunale $tribunal) {
            // Verificar si el usuario es miembro físico del tribunal (Presidente usualmente tiene más derechos)
            $miembroEnTribunal = $tribunal->miembrosTribunales()->where('user_id', $user->id)->first();
            if ($miembroEnTribunal && $miembroEnTribunal->status === 'PRESIDENTE') { // El presidente siempre puede
                return true;
            }
            // O si el permiso base lo permite para cualquier miembro
            // if ($miembroEnTribunal && $user->hasPermissionTo('exportar acta mi tribunal (presidente)')) {
            // return true;
            // }


            // Lógica para verificar si el usuario (Director, Apoyo, Calificador General)
            // tiene asignado CUALQUIER ítem o componente para calificar en el plan de este tribunal.
            // Esta lógica es similar a la de `determinarResponsabilidadesDeCalificacion` en el componente.
            $planEvaluacion = PlanEvaluacion::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
                ->with(['itemsPlanEvaluacion.asignacionesCalificadorComponentes'])
                ->first();

            if (!$planEvaluacion) {
                return false;
            }

            $esDirectorActual = $tribunal->carrerasPeriodo && $tribunal->carrerasPeriodo->director_id === $user->id;
            $esApoyoActual = $tribunal->carrerasPeriodo && $tribunal->carrerasPeriodo->docente_apoyo_id === $user->id;
            $esCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
                ->where('user_id', $user->id)->exists();

            foreach ($planEvaluacion->itemsPlanEvaluacion as $itemPlan) {
                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    if (($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $esDirectorActual) ||
                        ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $esApoyoActual)
                    ) {
                        return true; // Tiene al menos una nota directa que calificar
                    }
                } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                    if ($itemPlan->rubricaPlantilla) {
                        foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                            $asignacion = $itemPlan->asignacionesCalificadorComponentes
                                ->firstWhere('componente_rubrica_id', $componenteR->id);
                            if ($asignacion) {
                                if (($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL' && $miembroEnTribunal) || // Si es miembro físico y asignado
                                    ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) ||
                                    ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $esDirectorActual) ||
                                    ($asignacion->calificado_por === 'DOCENTE_APOYO' && $esApoyoActual)
                                ) {
                                    return true; // Tiene al menos un componente que calificar
                                }
                            }
                        }
                    }
                }
            }
            return false; // No encontró nada que el usuario deba calificar
        });

        // Gate para ver todas las calificaciones de un tribunal (Director/Apoyo de ESA carrera-periodo)
        Gate::define('ver-todas-calificaciones-de-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('ver todas las calificaciones de un tribunal')) {
                $carreraPeriodo = $tribunal->carreraPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        Gate::define('gestionar-calificadores-generales', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('configurar plan evaluacion')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        // === GATES PARA GESTIÓN DE ESTUDIANTES ===
        Gate::define('gestionar-estudiantes-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('gestionar estudiantes') || $user->hasPermissionTo('ver listado estudiantes')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        Gate::define('importar-estudiantes-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('importar estudiantes')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        // === GATES PARA GESTIÓN DE RÚBRICAS ===
        Gate::define('gestionar-rubricas-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('gestionar rubricas') || $user->hasPermissionTo('ver rubricas')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        Gate::define('asignar-rubricas-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('asignar rubricas a carrera-periodo')) {
                return ContextualAuth::isDirectorOf($user, $carreraPeriodo->id);
            }
            return false;
        });

        // === GATES PARA OPERACIONES ESPECÍFICAS DE TRIBUNALES ===
        Gate::define('crear-tribunal-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('crear tribunales')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        Gate::define('editar-tribunal-en-carrera-periodo', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('editar tribunales')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        Gate::define('eliminar-tribunal-en-carrera-periodo', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('eliminar tribunales')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        Gate::define('gestionar-estado-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('gestionar estado tribunales')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        Gate::define('asignar-miembros-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('asignar miembros tribunales')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        // === GATES PARA REPORTES Y ESTADÍSTICAS ===
        Gate::define('ver-reportes-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('ver resumenes y reportes academicos') || $user->hasPermissionTo('ver estadisticas carrera-periodo')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        Gate::define('exportar-reportes-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('exportar reportes')) {
                return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
            }
            return false;
        });

        // === GATES PARA CALIFICACIONES ===
        Gate::define('exportar-calificaciones-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('exportar calificaciones')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });

        // === GATES PARA ADMINISTRACIÓN GLOBAL ===
        Gate::define('acceder-dashboard-administrativo', function (User $user) {
            return $user->hasPermissionTo('ver dashboard administrativo');
        });

        Gate::define('gestionar-estructura-academica', function (User $user) {
            return $user->hasPermissionTo('gestionar periodos') ||
                   $user->hasPermissionTo('gestionar carreras') ||
                   $user->hasPermissionTo('gestionar departamentos');
        });

        Gate::define('gestionar-plantillas-sistema', function (User $user) {
            return $user->hasPermissionTo('gestionar plantillas rubricas');
        });

        // === GATES PARA ACTAS FIRMADAS ===
        // Gate para que el Presidente suba acta firmada de SU tribunal
        Gate::define('subir-acta-firmada-este-tribunal-como-presidente', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('subir acta firmada mi tribunal (presidente)')) {
                return ContextualAuth::isPresidentOfTribunal($user, $tribunal->id) && $tribunal->estado === 'CERRADO';
            }
            return false;
        });

        // Gate para que Director/Apoyo descarguen actas firmadas de tribunales de su carrera-periodo
        Gate::define('descargar-acta-firmada-de-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('descargar actas firmadas')) {
                $carreraPeriodo = $tribunal->carrerasPeriodo;
                if ($carreraPeriodo) {
                    return ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodo->id);
                }
            }
            return false;
        });
    }
}
