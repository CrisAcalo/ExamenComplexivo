<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
use App\Models\Tribunale;
use App\Models\PlanEvaluacion;
use App\Models\ItemPlanEvaluacion;
use App\Models\MiembroCalificacion;
use App\Models\CalificacionCriterio;
use App\Models\TribunalLog;
use App\Models\User;
use App\Models\AsignacionCalificadorComponentePlan;
use App\Models\CalificadorGeneralCarreraPeriodo;
use App\Models\MiembrosTribunal;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class TribunalesCalificar extends Component
{
    public $tribunalId;
    public ?Tribunale $tribunal = null;
    public ?PlanEvaluacion $planEvaluacionActivo = null;
    public $calificaciones = []; // Estructura de datos para el formulario

    // Propiedades para la vista (breadcrumbs, títulos)
    public $carreraNombre;
    public $periodoCodigo;
    public $estudianteNombreCompleto;
    public $carreraPeriodoIdDelTribunal; // Para la ruta "volver"

    // Estado del usuario actual en relación con el tribunal
    public ?User $usuarioActual = null;
    public $tipoAsignacionUsuario = []; // Información del tipo de asignación
    public $usuarioEsMiembroFisicoDelTribunal = false; // Si está en la tabla miembros_tribunales
    public $rolUsuarioActualEnTribunal = null; // PRESIDENTE, INTEGRANTE1, INTEGRANTE2, o null

    // Indica si el usuario tiene algo que calificar en este tribunal
    public $tieneAlgoQueCalificar = false;

    // Almacena qué ítems y componentes puede calificar el usuario actual
    public $itemsACalificarPorUsuario = [];       // [itemPlanId => true/false]
    public $componentesACalificarPorUsuario = []; // [itemPlanId => [componenteRId => true/false]]

    public $esCalificadorGeneral = false;

    protected function rules()
    {
        $rules = [];
        if (!$this->planEvaluacionActivo || !$this->tieneAlgoQueCalificar) {
            return $rules;
        }

        foreach ($this->calificaciones as $itemPlanId => $datosItem) {
            // Solo validar si el usuario tiene este ítem asignado para calificar
            if (!($this->itemsACalificarPorUsuario[$itemPlanId] ?? false)) {
                continue;
            }

            $itemPlan = $this->planEvaluacionActivo->itemsPlanEvaluacion->find($itemPlanId);
            if (!$itemPlan) continue;

            // Observación general del ítem es siempre opcional para todos los que pueden calificar el ítem
            $rules["calificaciones.{$itemPlanId}.observacion_general_item"] = 'nullable|string|max:1000';

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // La lógica de quién califica ya está en $this->itemsACalificarPorUsuario[$itemPlanId]
                $rules["calificaciones.{$itemPlanId}.nota_directa"] = 'required|numeric|min:0|max:20';
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                if (isset($datosItem['componentes_evaluados'])) {
                    foreach ($datosItem['componentes_evaluados'] as $componenteRId => $datosComponente) {
                        // Solo añadir reglas para los componentes que este usuario debe calificar
                        if (!($this->componentesACalificarPorUsuario[$itemPlanId][$componenteRId] ?? false)) {
                            continue;
                        }
                        if (isset($datosComponente['criterios_evaluados'])) {
                            foreach ($datosComponente['criterios_evaluados'] as $criterioRId => $datosCriterio) {
                                $rules["calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteRId}.criterios_evaluados.{$criterioRId}.calificacion_criterio_id"] = 'required|exists:calificaciones_criterio,id';
                                $rules["calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteRId}.criterios_evaluados.{$criterioRId}.observacion_criterio"] = 'nullable|string|max:500';
                            }
                        }
                    }
                }
            }
        }
        return $rules;
    }

    public function validationAttributes()
    {
        $attributes = [];
        if (!$this->planEvaluacionActivo || !$this->tieneAlgoQueCalificar) {
            return $attributes;
        }
        foreach ($this->calificaciones as $itemPlanId => $datosItem) {
            if (!($this->itemsACalificarPorUsuario[$itemPlanId] ?? false)) continue;

            $itemPlan = $this->planEvaluacionActivo->itemsPlanEvaluacion->find($itemPlanId);
            if (!$itemPlan) continue;

            $attributes["calificaciones.{$itemPlanId}.observacion_general_item"] = "observación general para '{$itemPlan->nombre_item}'";

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                $attributes["calificaciones.{$itemPlanId}.nota_directa"] = "nota para '{$itemPlan->nombre_item}'";
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla && isset($datosItem['componentes_evaluados'])) {
                foreach ($datosItem['componentes_evaluados'] as $componenteRId => $datosComponente) {
                    if (!($this->componentesACalificarPorUsuario[$itemPlanId][$componenteRId] ?? false)) continue;

                    $componenteRubricaObj = $itemPlan->rubricaPlantilla->componentesRubrica->find($componenteRId);
                    if ($componenteRubricaObj && isset($datosComponente['criterios_evaluados'])) {
                        foreach ($datosComponente['criterios_evaluados'] as $criterioRId => $datosCriterio) {
                            $criterioRubricaObj = $componenteRubricaObj->criteriosComponente->find($criterioRId);
                            if ($criterioRubricaObj) {
                                $attributes["calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteRId}.criterios_evaluados.{$criterioRId}.calificacion_criterio_id"] = "calificación para criterio '{$criterioRubricaObj->nombre}' ({$componenteRubricaObj->nombre})";
                                $attributes["calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteRId}.criterios_evaluados.{$criterioRId}.observacion_criterio"] = "observación para criterio '{$criterioRubricaObj->nombre}'";
                            }
                        }
                    }
                }
            }
        }
        return $attributes;
    }

    public function mount($tribunalId)
    {
        $this->tribunalId = $tribunalId;
        $this->usuarioActual = Auth::user();

        $this->tribunal = Tribunale::with([
            'carrerasPeriodo.carrera',
            'carrerasPeriodo.periodo',
            'carrerasPeriodo.director',
            'carrerasPeriodo.docenteApoyo',
            'estudiante',
            'miembrosTribunales.user'
        ])->find($this->tribunalId);

        if (!$this->tribunal || !$this->usuarioActual) {
            session()->flash('danger', 'Tribunal no encontrado o usuario no autenticado.');
            return; // La vista mostrará el mensaje
        }

        // Verificar acceso usando ContextualAuth
        $puedeCalificar = ContextualAuth::canCalifyInTribunal($this->usuarioActual, $this->tribunal);

        // Debug temporal para usuarios que no pueden acceder
        if (!$puedeCalificar) {
            Log::info('DEBUG ACCESO DENEGADO - Usuario: ' . $this->usuarioActual->id . ' (' . $this->usuarioActual->name . ')');
            Log::info('DEBUG ACCESO DENEGADO - Tribunal: ' . $this->tribunal->id . ', CarreraPeriodo: ' . $this->tribunal->carrera_periodo_id);
            Log::info('DEBUG ACCESO DENEGADO - Es Director: ' . (ContextualAuth::isDirectorOf($this->usuarioActual, $this->tribunal->carrera_periodo_id) ? 'SÍ' : 'NO'));
            Log::info('DEBUG ACCESO DENEGADO - Es Apoyo: ' . (ContextualAuth::isApoyoOf($this->usuarioActual, $this->tribunal->carrera_periodo_id) ? 'SÍ' : 'NO'));
            Log::info('DEBUG ACCESO DENEGADO - Es Calificador General: ' . (ContextualAuth::isCalificadorGeneralOf($this->usuarioActual, $this->tribunal->carrera_periodo_id) ? 'SÍ' : 'NO'));
            Log::info('DEBUG ACCESO DENEGADO - Es Miembro Físico: ' . (ContextualAuth::isMemberOfTribunal($this->usuarioActual, $this->tribunal->id) ? 'SÍ' : 'NO'));
            Log::info('DEBUG ACCESO DENEGADO - Es Super Admin: ' . (ContextualAuth::isSuperAdminOrAdmin($this->usuarioActual) ? 'SÍ' : 'NO'));
        }

        if (!$puedeCalificar) {
            session()->flash('danger', 'No tienes permisos para calificar en este tribunal.');
            return;
        }

        // Obtener información del tipo de asignación
        $this->tipoAsignacionUsuario = ContextualAuth::getTipoAsignacionEnTribunal($this->usuarioActual, $this->tribunal);

        // Verificar si el tribunal está cerrado
        if ($this->tribunal->estado === 'CERRADO') {
            session()->flash('danger', 'Este tribunal está cerrado. No se pueden realizar calificaciones.');
            return; // La vista mostrará el mensaje
        }

        if ($this->tribunal && $this->usuarioActual && $this->tribunal->carrerasPeriodo) {
            $this->esCalificadorGeneral = ContextualAuth::isCalificadorGeneralOf($this->usuarioActual, $this->tribunal->carrera_periodo_id);
        }

        $this->carreraPeriodoIdDelTribunal = $this->tribunal->carrera_periodo_id;
        $this->carreraNombre = $this->tribunal->carrerasPeriodo?->carrera?->nombre ?? 'N/A';
        $this->periodoCodigo = $this->tribunal->carrerasPeriodo?->periodo?->codigo_periodo ?? 'N/A';
        $this->estudianteNombreCompleto = $this->tribunal->estudiante?->nombres_completos_id ?? 'Estudiante N/A';

        $miembroActualEnTribunal = $this->tribunal->miembrosTribunales->firstWhere('user_id', $this->usuarioActual->id);
        $this->usuarioEsMiembroFisicoDelTribunal = (bool) $miembroActualEnTribunal;
        $this->rolUsuarioActualEnTribunal = $miembroActualEnTribunal?->status;

        if ($this->tribunal->carrerasPeriodo) {
            $this->planEvaluacionActivo = PlanEvaluacion::with([
                'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica.criteriosComponente.calificacionesCriterio',
                'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
            ])
                ->where('carrera_periodo_id', $this->tribunal->carrera_periodo_id)
                ->first();
        }

        if (!$this->planEvaluacionActivo) {
            session()->flash('warning', 'No existe un Plan de Evaluación activo para este tribunal.');
            return; // La vista mostrará el mensaje
        }

        $this->determinarResponsabilidadesDeCalificacion();
        $this->initializeCalificacionesArray();

        // Solo cargar calificaciones si el usuario tiene alguna responsabilidad
        if ($this->tieneAlgoQueCalificar) {
            $this->loadCalificacionesExistentes();
        } else {
            session()->flash('info', 'Usted no tiene ítems o componentes asignados para calificar en este tribunal.');
        }
    }

    protected function determinarResponsabilidadesDeCalificacion()
    {
        $this->itemsACalificarPorUsuario = [];
        $this->componentesACalificarPorUsuario = [];
        $this->tieneAlgoQueCalificar = false; // Resetear

        if (!$this->planEvaluacionActivo || !$this->usuarioActual || !$this->tribunal->carrerasPeriodo) return;

        $esDirectorActual = ContextualAuth::isDirectorOf($this->usuarioActual, $this->carreraPeriodoIdDelTribunal);
        $esApoyoActual = ContextualAuth::isApoyoOf($this->usuarioActual, $this->carreraPeriodoIdDelTribunal);
        $esCalificadorGeneral = ContextualAuth::isCalificadorGeneralOf($this->usuarioActual, $this->carreraPeriodoIdDelTribunal);

        // Debug específico para Calificadores Generales
        if ($esCalificadorGeneral) {
            Log::info('DEBUG CALIFICADOR GENERAL - Usuario: ' . $this->usuarioActual->id . ' (' . $this->usuarioActual->name . ')');
            Log::info('DEBUG CALIFICADOR GENERAL - CarreraPeriodo: ' . $this->carreraPeriodoIdDelTribunal);
            Log::info('DEBUG CALIFICADOR GENERAL - Total items en plan: ' . $this->planEvaluacionActivo->itemsPlanEvaluacion->count());

            // Debug de todas las asignaciones disponibles
            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $item) {
                if ($item->tipo_item === 'RUBRICA_TABULAR' && $item->asignacionesCalificadorComponentes) {
                    Log::info('DEBUG CALIFICADOR GENERAL - Item ' . $item->id . ' tiene ' . $item->asignacionesCalificadorComponentes->count() . ' asignaciones');
                    foreach ($item->asignacionesCalificadorComponentes as $asig) {
                        Log::info('DEBUG CALIFICADOR GENERAL - Asignación: ComponenteR=' . $asig->componente_rubrica_id . ', CalificadoPor=' . $asig->calificado_por);
                    }
                }
            }
        }

        foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $puedeCalificarEsteItemGlobal = false;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // Lógica de negocio: Tanto Director como Docente de Apoyo pueden calificar items de NOTA_DIRECTA
                // independientemente de la configuración específica
                if (($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && ($esDirectorActual || $esApoyoActual)) ||
                    ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && ($esDirectorActual || $esApoyoActual))
                ) {
                    $puedeCalificarEsteItemGlobal = true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                $this->componentesACalificarPorUsuario[$itemPlan->id] = [];
                if ($itemPlan->rubricaPlantilla) {
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        $asignacion = $itemPlan->asignacionesCalificadorComponentes
                            ->firstWhere('componente_rubrica_id', $componenteR->id);

                        $puedeCalificarEsteComponenteIndividual = false;
                        if ($asignacion) {
                            if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL' && $this->usuarioEsMiembroFisicoDelTribunal) {
                                $puedeCalificarEsteComponenteIndividual = true;
                            }
                            if ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) {
                                $puedeCalificarEsteComponenteIndividual = true;
                                if ($esCalificadorGeneral) {
                                    Log::info('DEBUG CALIFICADOR GENERAL - ✓ Puede calificar componente: ' . $componenteR->id . ' del item: ' . $itemPlan->id);
                                }
                            }
                            // Lógica de negocio: Tanto Director como Docente de Apoyo pueden calificar los mismos componentes
                            if (($asignacion->calificado_por === 'DIRECTOR_CARRERA' || $asignacion->calificado_por === 'DOCENTE_APOYO') &&
                                ($esDirectorActual || $esApoyoActual)) {
                                $puedeCalificarEsteComponenteIndividual = true;
                            }
                        } else if ($esCalificadorGeneral) {
                            Log::info('DEBUG CALIFICADOR GENERAL - ✗ NO encontrada asignación para componente: ' . $componenteR->id . ' del item: ' . $itemPlan->id);
                        }
                        $this->componentesACalificarPorUsuario[$itemPlan->id][$componenteR->id] = $puedeCalificarEsteComponenteIndividual;
                        if ($puedeCalificarEsteComponenteIndividual) {
                            $puedeCalificarEsteItemGlobal = true; // Si puede calificar al menos un componente de la rúbrica
                        }
                    }
                }
            }
            $this->itemsACalificarPorUsuario[$itemPlan->id] = $puedeCalificarEsteItemGlobal;
            if ($puedeCalificarEsteItemGlobal) {
                $this->tieneAlgoQueCalificar = true;
                if ($esCalificadorGeneral) {
                    Log::info('DEBUG CALIFICADOR GENERAL - ✓ Tiene algo que calificar en item: ' . $itemPlan->id);
                }
            } else if ($esCalificadorGeneral) {
                Log::info('DEBUG CALIFICADOR GENERAL - ✗ NO tiene nada que calificar en item: ' . $itemPlan->id);
            }
        }

        if ($esCalificadorGeneral) {
            Log::info('DEBUG CALIFICADOR GENERAL - RESULTADO FINAL - tieneAlgoQueCalificar: ' . ($this->tieneAlgoQueCalificar ? 'SÍ' : 'NO'));
        }
    }

    protected function initializeCalificacionesArray()
    {
        $this->calificaciones = [];
        if (!$this->planEvaluacionActivo) return;

        foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $itemPlanId = $itemPlan->id;
            $this->calificaciones[$itemPlanId] = [
                'tipo' => $itemPlan->tipo_item,
                'observacion_general_item' => '',
            ];

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                $this->calificaciones[$itemPlanId]['nota_directa'] = null;
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                $this->calificaciones[$itemPlanId]['componentes_evaluados'] = [];
                foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                    $this->calificaciones[$itemPlanId]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'] = [];
                    foreach ($componenteR->criteriosComponente as $criterioR) {
                        $opciones = $criterioR->calificacionesCriterio ? $criterioR->calificacionesCriterio->sortByDesc('valor') : collect();
                        $this->calificaciones[$itemPlan->id]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'][$criterioR->id] = [
                            'db_id_calif_criterio' => null,
                            'calificacion_criterio_id' => null,
                            'observacion_criterio' => '',
                            'opciones_calificacion' => $opciones, // $opciones ES una colección de modelos CalificacionCriterio
                        ];
                    }
                }
            }
        }
    }

    public function loadCalificacionesExistentes() // Ya no necesita $miembroTribunalIdDelUsuarioActual
    {
        if (!$this->planEvaluacionActivo || !$this->tieneAlgoQueCalificar) return;

        $calificacionesGuardadas = MiembroCalificacion::where('tribunal_id', $this->tribunal->id)
            ->where('user_id', $this->usuarioActual->id)
            ->get();

        foreach ($this->calificaciones as $itemPlanId => &$datosItemActual) {
            if (!($this->itemsACalificarPorUsuario[$itemPlanId] ?? false)) continue;

            $califGeneralItemGuardada = $calificacionesGuardadas
                ->where('item_plan_evaluacion_id', $itemPlanId)
                ->whereNull('criterio_id')
                ->first();

            $datosItemActual['observacion_general_item'] = $califGeneralItemGuardada?->observacion ?? $datosItemActual['observacion_general_item'];

            if ($datosItemActual['tipo'] === 'NOTA_DIRECTA') {
                $datosItemActual['nota_directa'] = $califGeneralItemGuardada?->nota_obtenida_directa ?? $datosItemActual['nota_directa'];
            } elseif ($datosItemActual['tipo'] === 'RUBRICA_TABULAR') {
                if (isset($datosItemActual['componentes_evaluados'])) {
                    foreach ($datosItemActual['componentes_evaluados'] as $componenteRId => &$datosComponenteActual) {
                        if (!($this->componentesACalificarPorUsuario[$itemPlanId][$componenteRId] ?? false)) continue;

                        if (isset($datosComponenteActual['criterios_evaluados'])) {
                            foreach ($datosComponenteActual['criterios_evaluados'] as $criterioRId => &$datosCriterioActual) {
                                $califCritGuardada = $calificacionesGuardadas
                                    ->where('item_plan_evaluacion_id', $itemPlanId)
                                    ->where('criterio_id', $criterioRId)
                                    ->first();

                                $datosCriterioActual['calificacion_criterio_id'] = $califCritGuardada?->calificacion_criterio_id ?? $datosCriterioActual['calificacion_criterio_id'];
                                $datosCriterioActual['observacion_criterio'] = $califCritGuardada?->observacion ?? $datosCriterioActual['observacion_criterio'];
                            }
                        }
                    }
                }
            }
        }
    }

    public function guardarCalificaciones()
    {
        if (!$this->tieneAlgoQueCalificar || !$this->planEvaluacionActivo) {
            session()->flash('danger', 'No hay nada asignado para calificar o el plan no está activo.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar si el tribunal está cerrado
        if ($this->tribunal->estado === 'CERRADO') {
            session()->flash('danger', 'Este tribunal está cerrado. No se pueden realizar calificaciones.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            $validatedData = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return; // Livewire maneja los errores de validación
        }

        DB::transaction(function () use ($validatedData) {
            foreach ($validatedData['calificaciones'] as $itemPlanId => $datosItemValidados) {
                if (!($this->itemsACalificarPorUsuario[$itemPlanId] ?? false)) continue;

                $itemPlan = $this->planEvaluacionActivo->itemsPlanEvaluacion->find($itemPlanId);
                if (!$itemPlan) continue;

                // Guardar/Actualizar la observación general del ítem y/o nota directa
                $calificacionGeneralData = [
                    'observacion' => $datosItemValidados['observacion_general_item'] ?? null,
                ];
                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    $calificacionGeneralData['nota_obtenida_directa'] = $datosItemValidados['nota_directa'];
                }

                MiembroCalificacion::updateOrCreate(
                    [
                        'tribunal_id' => $this->tribunal->id,
                        'user_id' => $this->usuarioActual->id,
                        'item_plan_evaluacion_id' => $itemPlanId,
                        'criterio_id' => null // Para la calificación/observación general del ítem
                    ],
                    $calificacionGeneralData
                );

                // Guardar/Actualizar calificaciones de los criterios para rúbricas
                if ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && isset($datosItemValidados['componentes_evaluados'])) {
                    foreach ($datosItemValidados['componentes_evaluados'] as $componenteRId => $datosComponente) {
                        if (!($this->componentesACalificarPorUsuario[$itemPlanId][$componenteRId] ?? false)) continue;

                        if (isset($datosComponente['criterios_evaluados'])) {
                            foreach ($datosComponente['criterios_evaluados'] as $criterioRId => $datosCriterio) {
                                MiembroCalificacion::updateOrCreate(
                                    [
                                        'tribunal_id' => $this->tribunal->id,
                                        'user_id' => $this->usuarioActual->id,
                                        'item_plan_evaluacion_id' => $itemPlanId,
                                        'criterio_id' => $criterioRId,
                                    ],
                                    [
                                        'calificacion_criterio_id' => $datosCriterio['calificacion_criterio_id'],
                                        'observacion' => $datosCriterio['observacion_criterio'] ?? null,
                                        'nota_obtenida_directa' => null, // Asegurar null para calificaciones de criterio
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            TribunalLog::create([
                'tribunal_id' => $this->tribunal->id,
                'user_id' => $this->usuarioActual->id,
                'accion' => 'REGISTRO_CALIFICACION',
                'descripcion' => Str::title($this->usuarioActual->name) . ' registró/actualizó sus calificaciones para el tribunal del estudiante ' . $this->estudianteNombreCompleto,
            ]);
        });

        session()->flash('success', 'Calificaciones guardadas exitosamente.');
        $this->loadCalificacionesExistentes(); // Recargar para reflejar el estado actual
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function render()
    {
        // La vista se encarga de mostrar mensajes si $tribunal o $planEvaluacionActivo son null
        // o si $tieneAlgoQueCalificar es false.
        return view('livewire.tribunales.principal.calificar');
    }
}
