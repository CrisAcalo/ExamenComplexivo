<?php

namespace App\Http\Livewire;

use App\Models\MiembrosTribunal;
use App\Models\Tribunale;
use App\Models\PlanEvaluacion;
use App\Models\ItemPlanEvaluacion;
use App\Models\AsignacionCalificadorComponentePlan;
use App\Models\CalificadorGeneralCarreraPeriodo;
use App\Models\MiembroCalificacion; // Para verificar calificaciones existentes
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB; // Para subqueries si es necesario

class TribunalesPrincipal extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';
    public $filtroEstado = 'PENDIENTES'; // Opciones: PENDIENTES, COMPLETADOS, TODOS

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

        // Obtener todos los carrera_periodo_id donde el usuario tiene algún rol de calificación
        // Esta parte puede ser compleja y optimizable.
        // 1. CarreraPeriodos donde es Director o Apoyo
        $carreraPeriodosComoDirectorOApoyo = DB::table('carreras_periodos')
            ->where('director_id', $user->id)
            ->orWhere('docente_apoyo_id', $user->id)
            ->pluck('id');

        // 2. CarreraPeriodos donde es Calificador General
        $carreraPeriodosComoCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('user_id', $user->id)
            ->pluck('carrera_periodo_id');

        // 3. Tribunales donde es Miembro (y obtener sus carrera_periodo_id)
        $tribunalesDondeEsMiembro = MiembrosTribunal::where('user_id', $user->id)
            ->with('tribunal') // Cargar la relación tribunal
            ->get();
        $carreraPeriodosDeSusTribunales = $tribunalesDondeEsMiembro->map(function ($miembro) {
            return $miembro->tribunal?->carrera_periodo_id;
        })->filter()->unique();


        $todosLosCarreraPeriodosRelevantes = $carreraPeriodosComoDirectorOApoyo
            ->merge($carreraPeriodosComoCalificadorGeneral)
            ->merge($carreraPeriodosDeSusTribunales)
            ->unique()
            ->values();

        if ($todosLosCarreraPeriodosRelevantes->isEmpty()) {
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(),
            ])->layout('layouts.panel');
        }

        // Obtener todos los planes de evaluación para esos carrera_periodos
        $planes = PlanEvaluacion::whereIn('carrera_periodo_id', $todosLosCarreraPeriodosRelevantes)
            ->with([
                'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica', // Para saber qué componentes hay
                'itemsPlanEvaluacion.asignacionesCalificadorComponentes'   // Para saber quién califica qué
            ])->get()->keyBy('carrera_periodo_id');


        // Ahora, buscar tribunales en esos carrera_periodos y filtrar
        $query = Tribunale::query()
            ->whereIn('carrera_periodo_id', $todosLosCarreraPeriodosRelevantes)
            ->with([
                'estudiante',
                'carrerasPeriodo.carrera',
                'carrerasPeriodo.periodo',
                'miembrosTribunales' => function ($q) use ($user) {
                    $q->where('user_id', $user->id); // Rol del usuario en este tribunal
                }
            ]);

        if (!empty($this->searchTerm)) {
            // ... (lógica de búsqueda como la tenías) ...
            $query->where(function ($q) {
                $q->whereHas('estudiante', function ($sq) {
                    $sq->where('nombres', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('apellidos', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('ID_estudiante', 'like', '%' . $this->searchTerm . '%');
                })
                    ->orWhereHas('carrerasPeriodo.carrera', function ($sq) {
                        $sq->where('nombre', 'like', '%' . $this->searchTerm . '%');
                    })
                    ->orWhereHas('carrerasPeriodo.periodo', function ($sq) {
                        $sq->where('codigo_periodo', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        $tribunalesPotenciales = $query->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'asc')->get();

        $tribunalesFiltradosFinal = $tribunalesPotenciales->filter(function ($tribunal) use ($user, $planes) {
            $plan = $planes->get($tribunal->carrera_periodo_id);
            if (!$plan) return false; // No hay plan, no puede calificar

            $tieneItemsPendientes = false;
            $miembroTribunalRegistro = $tribunal->miembrosTribunales->first(); // El rol del user en este tribunal (si es miembro)

            foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
                $debeCalificarEsteItem = false;

                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    if (($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $tribunal->carrerasPeriodo->director_id == $user->id) ||
                        ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id)
                    ) {
                        $debeCalificarEsteItem = true;
                    }
                } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                    foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                        if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL' && $miembroTribunalRegistro) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                        if ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)->where('user_id', $user->id)->exists()) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                        if ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $tribunal->carrerasPeriodo->director_id == $user->id) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                        if ($asignacion->calificado_por === 'DOCENTE_APOYO' && $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                    }
                }

                if ($debeCalificarEsteItem) {
                    // Verificar si ya calificó este ítem
                    $calificacionExistente = MiembroCalificacion::where('tribunal_id', $tribunal->id)
                        ->where('user_id', $user->id)
                        ->where('item_plan_evaluacion_id', $itemPlan->id)
                        ->exists();
                    if (!$calificacionExistente) {
                        $tieneItemsPendientes = true;
                        break; // Si hay al menos un ítem pendiente, incluimos el tribunal (para filtro PENDIENTES)
                    }
                }
            }

            if ($this->filtroEstado === 'PENDIENTES') {
                return $tieneItemsPendientes;
            } elseif ($this->filtroEstado === 'COMPLETADOS') {
                // Para 'COMPLETADOS', necesitaríamos verificar que *todos* los ítems que debe calificar estén calificados.
                // Esto es más complejo: primero identificar todos los ítems que debe calificar, luego ver si todos tienen entrada.
                // Por ahora, simplificamos: si no tiene ítems pendientes, y debe calificar algo, lo consideramos "completado" para este filtro.
                // Una lógica más precisa sería: si $debeCalificarAlgoEnGeneral && !$tieneItemsPendientes
                $debeCalificarAlgoEnGeneral = $this->usuarioDebeCalificarAlgoEnTribunal($user, $tribunal, $plan);
                return $debeCalificarAlgoEnGeneral && !$tieneItemsPendientes;
            }
            // Para filtroEstado === 'TODOS' (o si no hay PENDIENTES ni COMPLETADOS estrictos)
            return $this->usuarioDebeCalificarAlgoEnTribunal($user, $tribunal, $plan);
        });

        // Paginación manual de la colección filtrada
        $page = $this->resolvePage();
        $perPage = 10;
        $itemsForCurrentPage = $tribunalesFiltradosFinal->slice(($page - 1) * $perPage, $perPage);
        $paginatedTribunales = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $tribunalesFiltradosFinal->count(),
            $perPage,
            $page,
            // Laravel usualmente puede inferir la ruta actual,
            // o puedes especificarla explícitamente si es necesario.
            // Para componentes Livewire, a menudo es mejor dejar que maneje la URL
            // o usar la URL actual si hay problemas con la inferencia.
            ['path' => request()->url()] // Usa la URL actual para los enlaces de paginación
            // Alternativamente, si tu componente siempre se renderiza en la misma ruta nombrada:
            // ['path' => route(request()->route()->getName())]
            // O incluso a menudo puedes omitir la opción 'path' y Laravel lo manejará.
        );


        return view('livewire.tribunales.principal.view', [
            'tribunalesAsignados' => $paginatedTribunales,
        ])->layout('layouts.panel');
    }

    // Helper para determinar si el usuario tiene *alguna* responsabilidad de calificación en este tribunal
    protected function usuarioDebeCalificarAlgoEnTribunal($user, $tribunal, $plan)
    {
        if (!$plan) return false;
        $miembroTribunalRegistro = $tribunal->miembrosTribunales->where('user_id', $user->id)->first();

        foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                if (($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $tribunal->carrerasPeriodo->director_id == $user->id) ||
                    ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id)
                ) {
                    return true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL' && $miembroTribunalRegistro) return true;
                    if ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)->where('user_id', $user->id)->exists()) return true;
                    if ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $tribunal->carrerasPeriodo->director_id == $user->id) return true;
                    if ($asignacion->calificado_por === 'DOCENTE_APOYO' && $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id) return true;
                }
            }
        }
        return false;
    }
}
