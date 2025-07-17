<?php

namespace App\Http\Livewire;

use App\Models\MiembrosTribunal;
use App\Models\PlanEvaluacion;
use App\Models\MiembroCalificacion;
use App\Models\CalificadorGeneralCarreraPeriodo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TribunalesPrincipal extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';
    public $filtroEstado = 'PENDIENTES'; // Opciones: PENDIENTES, COMPLETADOS, TODOS
    public $tribunalesActuales;

    public function mount()
    {
        // No es necesario cargar todos los tribunales aquí si se hace en render
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->hasRole(['Super Admin', 'Administrador'])) {
            // Administradores no deberían ver esta lista, tienen otras vistas
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(), // Colección vacía
                'mensajeNoAutorizado' => 'Esta vista es para docentes y calificadores asignados.'
            ])->layout('layouts.panel');
        }

        // Consulta directa y simple: obtener todos los tribunales donde el usuario es miembro
        $tribunalesDondeEsMiembro = MiembrosTribunal::where('user_id', $user->id)
            ->with(['tribunal.estudiante', 'tribunal.carrerasPeriodo.carrera', 'tribunal.carrerasPeriodo.periodo'])
            ->get();

        if ($tribunalesDondeEsMiembro->isEmpty()) {
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(),
            ])->layout('layouts.panel');
        }

        // Extraer los tribunales de la relación
        $tribunales = $tribunalesDondeEsMiembro->map(function ($miembro) {
            return $miembro->tribunal;
        })->filter(); // Filtrar nulls si los hay

        // Cargar planes de evaluación de una vez para evitar consultas N+1
        $carreraPeriodoIds = $tribunales->pluck('carrera_periodo_id')->unique();
        $planes = PlanEvaluacion::whereIn('carrera_periodo_id', $carreraPeriodoIds)
            ->with(['itemsPlanEvaluacion.asignacionesCalificadorComponentes'])
            ->get()
            ->keyBy('carrera_periodo_id');

        // Verificar si el usuario es calificador general en algún carrera_periodo
        $calificadorGeneralIds = CalificadorGeneralCarreraPeriodo::whereIn('carrera_periodo_id', $carreraPeriodoIds)
            ->where('user_id', $user->id)
            ->pluck('carrera_periodo_id')
            ->toArray();

        // Precargar todas las calificaciones del usuario para estos tribunales
        $tribunalIds = $tribunales->pluck('id');
        $calificacionesUsuario = MiembroCalificacion::whereIn('tribunal_id', $tribunalIds)
            ->where('user_id', $user->id)
            ->get()
            ->groupBy(function($calificacion) {
                return $calificacion->tribunal_id . '_' . $calificacion->item_plan_evaluacion_id;
            });

        // Aplicar filtros de búsqueda si existe
        if (!empty($this->searchTerm)) {
            $tribunales = $tribunales->filter(function ($tribunal) {
                $estudiante = $tribunal->estudiante;
                $carrera = $tribunal->carrerasPeriodo->carrera ?? null;
                $periodo = $tribunal->carrerasPeriodo->periodo ?? null;

                $searchTerm = strtolower($this->searchTerm);

                // Buscar en nombres del estudiante
                if ($estudiante) {
                    if (str_contains(strtolower($estudiante->nombres), $searchTerm) ||
                        str_contains(strtolower($estudiante->apellidos), $searchTerm) ||
                        str_contains(strtolower($estudiante->ID_estudiante ?? ''), $searchTerm)) {
                        return true;
                    }
                }

                // Buscar en nombre de carrera
                if ($carrera && str_contains(strtolower($carrera->nombre), $searchTerm)) {
                    return true;
                }

                // Buscar en código de período
                if ($periodo && str_contains(strtolower($periodo->codigo_periodo), $searchTerm)) {
                    return true;
                }

                return false;
            });
        }

        // Aplicar filtro de estado
        if ($this->filtroEstado !== 'TODOS') {
            $tribunales = $tribunales->filter(function ($tribunal) use ($user, $planes, $calificadorGeneralIds, $calificacionesUsuario) {
                $estadoTribunal = $this->determinarEstadoTribunalOptimizado($tribunal, $user, $planes, $calificadorGeneralIds, $calificacionesUsuario);

                if ($this->filtroEstado === 'PENDIENTES') {
                    return $estadoTribunal === 'PENDIENTE';
                } elseif ($this->filtroEstado === 'COMPLETADOS') {
                    return $estadoTribunal === 'COMPLETADO';
                }

                return true; // Para cualquier otro caso
            });
        }

        // Ordenar tribunales
        $tribunales = $tribunales->sortByDesc(function ($tribunal) {
            return $tribunal->fecha . ' ' . $tribunal->hora_inicio;
        });

        // Paginación manual de la colección filtrada
        $page = $this->resolvePage();
        $perPage = 10;
        $itemsForCurrentPage = $tribunales->slice(($page - 1) * $perPage, $perPage);
        $paginatedTribunales = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $tribunales->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        // Mantener la consulta simple para debugging
        $this->tribunalesActuales = $tribunalesDondeEsMiembro;

        return view('livewire.tribunales.principal.view', [
            'tribunalesAsignados' => $paginatedTribunales,
        ])->layout('layouts.panel');
    }

    /**
     * Determina el estado de un tribunal para un usuario específico (versión optimizada)
     * @param $tribunal
     * @param $user
     * @param $planes Collection de planes precargados
     * @param $calificadorGeneralIds Array de IDs donde el usuario es calificador general
     * @param $calificacionesUsuario Collection de calificaciones precargadas agrupadas
     * @return string 'PENDIENTE' o 'COMPLETADO'
     */
    private function determinarEstadoTribunalOptimizado($tribunal, $user, $planes, $calificadorGeneralIds, $calificacionesUsuario)
    {
        // Obtener el plan de evaluación desde la colección precargada
        $plan = $planes->get($tribunal->carrera_periodo_id);

        if (!$plan) {
            return 'PENDIENTE'; // Si no hay plan, consideramos pendiente
        }

        $itemsQueDebeCalificar = [];
        $itemsCalificados = [];

        // Verificar si el usuario es calificador general (desde array precargado)
        $esCalificadorGeneral = in_array($tribunal->carrera_periodo_id, $calificadorGeneralIds);

        // Verificar si es director o docente de apoyo
        $esDirector = $tribunal->carrerasPeriodo->director_id == $user->id;
        $esDocenteApoyo = $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id;

        foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
            $debeCalificarEsteItem = false;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // Para items de nota directa, verificar quién debe calificar
                if ($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $esDirector) {
                    $debeCalificarEsteItem = true;
                } elseif ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                    $debeCalificarEsteItem = true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                // Para items de rúbrica, verificar las asignaciones de componentes
                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL') {
                        // Ya sabemos que el usuario es miembro del tribunal (por la consulta inicial)
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $esDirector) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                        $debeCalificarEsteItem = true;
                        break;
                    }
                }
            }

            if ($debeCalificarEsteItem) {
                $itemsQueDebeCalificar[] = $itemPlan->id;

                // Verificar si ya calificó este ítem usando datos precargados
                $claveCalificacion = $tribunal->id . '_' . $itemPlan->id;
                if ($calificacionesUsuario->has($claveCalificacion)) {
                    $itemsCalificados[] = $itemPlan->id;
                }
            }
        }

        // Si no debe calificar nada, consideramos como PENDIENTE
        if (empty($itemsQueDebeCalificar)) {
            return 'PENDIENTE';
        }

        // Si calificó todos los items que debe calificar, está COMPLETADO
        if (count($itemsCalificados) === count($itemsQueDebeCalificar)) {
            return 'COMPLETADO';
        }

        // Si aún le faltan items por calificar, está PENDIENTE
        return 'PENDIENTE';
    }

    /**
     * Determina el estado de un tribunal para un usuario específico
     * @param $tribunal
     * @param $user
     * @return string 'PENDIENTE' o 'COMPLETADO'
     */
    private function determinarEstadoTribunal($tribunal, $user)
    {
        // Obtener el plan de evaluación para este tribunal
        $plan = PlanEvaluacion::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
            ->with([
                'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
            ])
            ->first();

        if (!$plan) {
            return 'PENDIENTE'; // Si no hay plan, consideramos pendiente
        }

        $itemsQueDebeCalificar = [];
        $itemsCalificados = [];

        // Verificar si el usuario es calificador general
        $esCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
            ->where('user_id', $user->id)
            ->exists();

        $esDirector = $tribunal->carrerasPeriodo->director_id == $user->id;
        $esDocenteApoyo = $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id;

        foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
            $debeCalificarEsteItem = false;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                if ($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $esDirector) {
                    $debeCalificarEsteItem = true;
                } elseif ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                    $debeCalificarEsteItem = true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL') {
                        $esMiembroTribunal = MiembrosTribunal::where('tribunal_id', $tribunal->id)
                            ->where('user_id', $user->id)
                            ->exists();
                        if ($esMiembroTribunal) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                    } elseif ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $esDirector) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                        $debeCalificarEsteItem = true;
                        break;
                    }
                }
            }

            if ($debeCalificarEsteItem) {
                $itemsQueDebeCalificar[] = $itemPlan->id;

                $calificacionExistente = MiembroCalificacion::where('tribunal_id', $tribunal->id)
                    ->where('user_id', $user->id)
                    ->where('item_plan_evaluacion_id', $itemPlan->id)
                    ->exists();

                if ($calificacionExistente) {
                    $itemsCalificados[] = $itemPlan->id;
                }
            }
        }

        if (empty($itemsQueDebeCalificar)) {
            return 'PENDIENTE';
        }

        if (count($itemsCalificados) === count($itemsQueDebeCalificar)) {
            return 'COMPLETADO';
        }

        return 'PENDIENTE';
    }
}
