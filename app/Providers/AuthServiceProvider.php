<?php

namespace App\Providers;

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
                if ($user->hasRole('Administrador')) {
                    return true; // Admin puede para cualquiera
                }
                if ($user->hasRole('Director de Carrera') && $carreraPeriodo->director_id === $user->id) {
                    return true;
                }
                if ($user->hasRole('Docente de Apoyo') && $carreraPeriodo->docente_apoyo_id === $user->id) {
                    return true;
                }
            }
            return false;
        });

        // Gate para CRUD de tribunales (contextual)
        // Podrías necesitar Gates más granulares: 'crear-tribunal-en-carrera-periodo', 'eliminar-tribunal-de-carrera-periodo'
        Gate::define('gestionar-tribunales-en-carrera-periodo', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            // Usaremos permisos más granulares como 'crear tribunales', 'eliminar tribunales'
            // Este Gate podría ser para ver el listado o acceder a la sección
            if ($user->hasPermissionTo('ver listado tribunales')) { // O un permiso más genérico
                if ($user->hasRole('Administrador')) {
                    return true;
                }
                if ($user->hasRole('Director de Carrera') && $carreraPeriodo->director_id === $user->id) {
                    return true;
                }
                if ($user->hasRole('Docente de Apoyo') && $carreraPeriodo->docente_apoyo_id === $user->id) {
                    return true;
                }
            }
            return false;
        });

        // Gate para ver los detalles de un tribunal específico donde el usuario es miembro
        Gate::define('ver-detalles-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('ver detalles mi tribunal')) {
                return $tribunal->miembrosTribunales()->where('user_id', $user->id)->exists();
            }
            return false;
        });

        // Gate para calificar (ingresar/editar propias calificaciones)
        Gate::define('calificar-este-tribunal', function (User $user, Tribunale $tribunal) {
            // Asumimos que 'calificar mi tribunal' es el permiso base
            if ($user->hasPermissionTo('calificar mi tribunal')) {
                // Adicionalmente, verificar si es miembro de ESTE tribunal
                return $tribunal->miembrosTribunales()->where('user_id', $user->id)->exists();
                // Aquí podrías añadir lógica de si el tribunal está "abierto para calificación"
            }
            return false;
        });

        // Gate para que el Presidente edite datos básicos de SU tribunal
        Gate::define('editar-datos-basicos-este-tribunal-como-presidente', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('editar datos basicos mi tribunal (presidente)')) {
                return $tribunal->miembrosTribunales()
                    ->where('user_id', $user->id)
                    ->where('status', 'PRESIDENTE')
                    ->exists();
                // Aquí podrías añadir lógica de si el tribunal está "abierto para edición"
            }
            return false;
        });

        // Gate para que el Presidente exporte el acta de SU tribunal
        Gate::define('exportar-acta-este-tribunal-como-presidente', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('exportar acta mi tribunal (presidente)')) {
                return $tribunal->miembrosTribunales()
                    ->where('user_id', $user->id)
                    ->where('status', 'PRESIDENTE')
                    ->exists();
            }
            return false;
        });

        //Gate para que cualquier miembro de tribunal, calificador general director o apoyo pueda exportar el acta de SU tribunal
        // En AuthServiceProvider.php
        Gate::define('puede-exportar-acta-de-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasRole('Administrador')) {
                return true;
            }

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

        // Gate para ver todas las calificaciones de un tribunal (Admin, o Director/Apoyo de ESA carrera-periodo)
        Gate::define('ver-todas-calificaciones-de-este-tribunal', function (User $user, Tribunale $tribunal) {
            if ($user->hasPermissionTo('ver todas las calificaciones de un tribunal')) {
                if ($user->hasRole('Administrador')) {
                    return true;
                }
                $carreraPeriodo = $tribunal->carreraPeriodo; // Asegúrate que la relación exista y se cargue
                if ($carreraPeriodo) {
                    if (($user->hasRole('Director de Carrera') && $carreraPeriodo->director_id === $user->id) ||
                        ($user->hasRole('Docente de Apoyo') && $carreraPeriodo->docente_apoyo_id === $user->id)
                    ) {
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('gestionar-calificadores-generales', function (User $user, CarrerasPeriodo $carreraPeriodo) {
            if ($user->hasPermissionTo('configurar plan evaluacion')) { // O un permiso más específico
                // if ($user->hasRole('Administrador')) return true;
                if ($user->hasRole('Director de Carrera') && $carreraPeriodo->director_id === $user->id) return true;
                // Podrías añadir Docente de Apoyo si también puede
                if ($user->hasRole('Docente de Apoyo') && $carreraPeriodo->docente_apoyo_id === $user->id) return true;
            }
            return false;
        });
    }
}
