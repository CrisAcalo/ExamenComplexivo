<?php

namespace App\Http\Livewire;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\ItemPlanEvaluacion;
use App\Models\Periodo;
use App\Models\PlanEvaluacion;
use App\Models\Rubrica;
use App\Models\ComponenteRubrica;
use App\Models\AsignacionCalificadorComponentePlan;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PlanEvaluacionManager extends Component
{
    // ... (propiedades como estaban) ...
    public $carreraPeriodoId;
    public ?CarrerasPeriodo $carreraPeriodo = null;
    public ?Carrera $carrera = null;
    public ?Periodo $periodo = null;

    public ?PlanEvaluacion $planEvaluacion = null;
    public $nombrePlan;
    public $descripcionPlan;

    public $items = [];

    public $plantillasRubricasDisponibles = [];
    public $tiposItemDisponibles = [
        'NOTA_DIRECTA' => 'Nota Directa (Ej: Cuestionario)',
        'RUBRICA_TABULAR' => 'Basado en Rúbrica Tabular (Ej: Parte Escrita)'
    ];

    public $opcionesCalificadoPorComponenteRubricaFiltradas = [
        'MIEMBROS_TRIBUNAL'       => 'Miembros del Tribunal',
        'CALIFICADORES_GENERALES' => 'Calificadores Generales',
    ];
    const CALIFICADO_POR_NOTA_DIRECTA_DEFAULT = 'DIRECTOR_CARRERA';


    protected function rules() // Sin cambios respecto a tu última versión
    {
        $rules = [
            'nombrePlan' => 'required|string|max:255',
            'descripcionPlan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.nombre_item' => 'required|string|max:255',
            'items.*.tipo_item' => 'required|in:' . implode(',', array_keys($this->tiposItemDisponibles)),
            'items.*.ponderacion_global' => 'required|numeric|min:0|max:100',
        ];

        foreach ($this->items as $index => $item) {
            if (!isset($item['tipo_item'])) continue;

            if ($item['tipo_item'] === 'RUBRICA_TABULAR') {
                $rules["items.{$index}.rubrica_plantilla_id"] = 'required|exists:rubricas,id';

                // Usar la colección de plantilla_componentes que ya debería estar en el item
                $plantillaComponentes = $item['plantilla_componentes'] ?? collect();
                if ($plantillaComponentes instanceof Collection) {
                    foreach($plantillaComponentes as $compPlantilla) {
                        if (is_object($compPlantilla) && property_exists($compPlantilla, 'id')) {
                             $rules["items.{$index}.asignaciones_componentes.{$compPlantilla->id}"] = 'required|in:' . implode(',', array_keys($this->opcionesCalificadoPorComponenteRubricaFiltradas));
                        }
                    }
                }
            } elseif ($item['tipo_item'] === 'NOTA_DIRECTA') {
                $rules["items.{$index}.rubrica_plantilla_id"] = 'nullable';
            }
        }
        return $rules;
    }

    protected $messages = [ // Sin cambios respecto a tu última versión
        'nombrePlan.required' => 'El nombre del plan es obligatorio.',
        'items.required' => 'Debe definir al menos un ítem para el plan de evaluación.',
        'items.min' => 'Debe definir al menos un ítem para el plan de evaluación.',
        'items.*.nombre_item.required' => 'El nombre del ítem es obligatorio.',
        'items.*.tipo_item.required' => 'El tipo de ítem es obligatorio.',
        'items.*.ponderacion_global.required' => 'La ponderación global es obligatoria.',
        'items.*.ponderacion_global.numeric' => 'La ponderación debe ser un número.',
        'items.*.ponderacion_global.min' => 'La ponderación no puede ser negativa.',
        'items.*.ponderacion_global.max' => 'La ponderación no puede exceder 100.',
        'items.*.rubrica_plantilla_id.required' => 'Debe seleccionar una plantilla de rúbrica para este tipo de ítem.',
        'items.*.rubrica_plantilla_id.exists' => 'La plantilla de rúbrica seleccionada no es válida.',
        'items.*.asignaciones_componentes.*.required' => 'Debe seleccionar quién califica este componente de la rúbrica.',
        'items.*.asignaciones_componentes.*.in' => 'La opción seleccionada para calificar el componente no es válida.',
    ];

    public function mount($carreraPeriodoId) // Sin cambios respecto a tu última versión
    {
        $this->carreraPeriodoId = $carreraPeriodoId;
        $this->carreraPeriodo = CarrerasPeriodo::with('carrera', 'periodo')->find($carreraPeriodoId);

        if (!$this->carreraPeriodo) {
            session()->flash('danger', 'Contexto de Carrera-Periodo no válido.');
            return redirect()->route('periodos.');
        }
        $this->carrera = $this->carreraPeriodo->carrera;
        $this->periodo = $this->carreraPeriodo->periodo;

        $this->planEvaluacion = PlanEvaluacion::with([
            'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica',
            'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
        ])
        ->where('carrera_periodo_id', $this->carreraPeriodoId)
        ->first();

        $this->nombrePlan = $this->planEvaluacion ? $this->planEvaluacion->nombre : "Plan de Evaluación para " . ($this->carrera->nombre ?? 'N/A') . " - " . ($this->periodo->codigo_periodo ?? 'N/A');
        $this->descripcionPlan = $this->planEvaluacion ? $this->planEvaluacion->descripcion : '';

        if ($this->planEvaluacion) {
            $this->items = [];
            foreach ($this->planEvaluacion->itemsPlanEvaluacion->sortBy('orden') as $itemDB) {
                $this->items[] = $this->mapItemDBToArray($itemDB);
            }
        }
        $this->plantillasRubricasDisponibles = Rubrica::orderBy('nombre')->get();
    }

    protected function mapItemDBToArray(ItemPlanEvaluacion $itemDB): array // Sin cambios
    {
        $componentesRubricaSeleccionada = [];
        $asignacionesComponentes = [];
        $plantillaComponentes = collect();

        if ($itemDB->tipo_item === 'RUBRICA_TABULAR' && $itemDB->rubricaPlantilla) {
            $plantillaComponentes = $itemDB->rubricaPlantilla->componentesRubrica ?? collect();
            foreach ($plantillaComponentes as $compPlantilla) {
                $componentesRubricaSeleccionada[] = (object) [ // Asegurar stdClass
                    'id' => $compPlantilla->id,
                    'nombre' => $compPlantilla->nombre,
                    'ponderacion_interna' => (float) $compPlantilla->ponderacion,
                    'ponderacion_calculada_global' => round(((float) $itemDB->ponderacion_global * (float) $compPlantilla->ponderacion) / 100, 2)
                ];
                $asignacionExistente = $itemDB->asignacionesCalificadorComponentes
                                         ->firstWhere('componente_rubrica_id', $compPlantilla->id);
                $asignacionesComponentes[$compPlantilla->id] = $asignacionExistente ? $asignacionExistente->calificado_por : array_key_first($this->opcionesCalificadoPorComponenteRubricaFiltradas);
            }
        }

        return [
            'id_temporal' => 'item_' . uniqid(),
            'db_id' => $itemDB->id,
            'nombre_item' => $itemDB->nombre_item,
            'tipo_item' => $itemDB->tipo_item,
            'ponderacion_global' => (float) $itemDB->ponderacion_global,
            'rubrica_plantilla_id' => $itemDB->rubrica_plantilla_id,
            'componentes_rubrica_seleccionada' => $componentesRubricaSeleccionada, // Array de stdClass
            'asignaciones_componentes' => $asignacionesComponentes,
            'orden' => $itemDB->orden,
            'plantilla_componentes' => $plantillaComponentes, // Colección de Modelos
        ];
    }

    public function addItem() // Sin cambios
    {
        $this->items[] = [
            'id_temporal' => 'item_' . uniqid(),
            'db_id' => null,
            'nombre_item' => '',
            'tipo_item' => 'NOTA_DIRECTA',
            'ponderacion_global' => 0,
            'rubrica_plantilla_id' => null,
            'componentes_rubrica_seleccionada' => [],
            'asignaciones_componentes' => [],
            'orden' => count($this->items),
            'plantilla_componentes' => collect(),
        ];
    }

    public function removeItem($index) // Sin cambios
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1];

        if (!isset($this->items[$index])) {
            return;
        }
        $itemActual =& $this->items[$index];

        if ($field === 'tipo_item') {
            $itemActual['rubrica_plantilla_id'] = null;
            $itemActual['componentes_rubrica_seleccionada'] = [];
            $itemActual['asignaciones_componentes'] = [];
            $itemActual['plantilla_componentes'] = collect();
        }

        if ($field === 'rubrica_plantilla_id' && $itemActual['tipo_item'] === 'RUBRICA_TABULAR') {
            $itemActual['componentes_rubrica_seleccionada'] = [];
            $itemActual['asignaciones_componentes'] = [];
            $itemActual['plantilla_componentes'] = collect();

            if (!empty($itemActual['rubrica_plantilla_id'])) {
                $plantilla = Rubrica::with('componentesRubrica')->find($itemActual['rubrica_plantilla_id']);
                if ($plantilla && $plantilla->componentesRubrica->isNotEmpty()) {
                    $itemActual['plantilla_componentes'] = $plantilla->componentesRubrica;
                    foreach ($plantilla->componentesRubrica as $comp) {
                        $itemActual['componentes_rubrica_seleccionada'][] = (object) [ // Asegurar stdClass
                            'id' => $comp->id,
                            'nombre' => $comp->nombre,
                            'ponderacion_interna' => (float) $comp->ponderacion,
                            'ponderacion_calculada_global' => round(((float) ($itemActual['ponderacion_global'] ?? 0) * (float) $comp->ponderacion) / 100, 2)
                        ];
                        $itemActual['asignaciones_componentes'][$comp->id] = array_key_first($this->opcionesCalificadoPorComponenteRubricaFiltradas);
                    }
                }
            }
        }

        if ($field === 'ponderacion_global' &&
            isset($itemActual['tipo_item']) && $itemActual['tipo_item'] === 'RUBRICA_TABULAR' &&
            isset($itemActual['componentes_rubrica_seleccionada']) && is_iterable($itemActual['componentes_rubrica_seleccionada'])) {

            $recalculadosCompSeleccionados = [];
            foreach ($itemActual['componentes_rubrica_seleccionada'] as $compData) {
                // Asegurar que $compData sea un objeto antes de acceder a sus propiedades
                $compObj = is_array($compData) ? (object) $compData : $compData;

                if (is_object($compObj) && property_exists($compObj, 'ponderacion_interna')) {
                    $recalculadosCompSeleccionados[] = (object) [
                        'id' => $compObj->id ?? null,
                        'nombre' => $compObj->nombre ?? null,
                        'ponderacion_interna' => (float) ($compObj->ponderacion_interna ?? 0),
                        'ponderacion_calculada_global' => round(((float) ($itemActual['ponderacion_global'] ?? 0) * (float) ($compObj->ponderacion_interna ?? 0)) / 100, 2)
                    ];
                } else {
                    // Si no es un objeto o no tiene la propiedad, añadirlo tal cual si es necesario
                    // o manejar el error. Por ahora, lo añadimos si es un objeto.
                    if(is_object($compObj)) $recalculadosCompSeleccionados[] = $compObj;
                }
            }
            $itemActual['componentes_rubrica_seleccionada'] = $recalculadosCompSeleccionados;
        }

        // Asegurar el tipo de 'plantilla_componentes' después de CUALQUIER actualización de 'items.*'
        // Esto es una capa extra de seguridad.
        if ($itemActual['tipo_item'] === 'RUBRICA_TABULAR') {
            if (isset($itemActual['plantilla_componentes']) && !($itemActual['plantilla_componentes'] instanceof Collection)) {
                if (!empty($itemActual['rubrica_plantilla_id'])) {
                    $reloadedPlantilla = Rubrica::with('componentesRubrica')->find($itemActual['rubrica_plantilla_id']);
                    $itemActual['plantilla_componentes'] = $reloadedPlantilla ? ($reloadedPlantilla->componentesRubrica ?? collect()) : collect();
                } else {
                    $itemActual['plantilla_componentes'] = collect($itemActual['plantilla_componentes']); // Convertir si es array
                }
            } elseif (!isset($itemActual['plantilla_componentes'])) {
                 $itemActual['plantilla_componentes'] = collect();
            }
        } else {
            $itemActual['plantilla_componentes'] = collect(); // Si no es rúbrica, debe ser colección vacía
        }
    }

    public function savePlan() // Sin cambios respecto a tu última versión
    {
        $validatedData = $this->validate();

        $totalPonderacionGlobal = 0;
        foreach ($this->items as $item) {
            $totalPonderacionGlobal += (float) ($item['ponderacion_global'] ?? 0);
        }

        if (round($totalPonderacionGlobal, 2) != 100.00) {
            $this->addError('ponderacion_total_global', 'La suma de las ponderaciones globales de todos los ítems debe ser 100%. Actual: ' . $totalPonderacionGlobal . '%');
            return;
        }

        DB::transaction(function () {
            if ($this->planEvaluacion) {
                $this->planEvaluacion->update([
                    'nombre' => $this->nombrePlan,
                    'descripcion' => $this->descripcionPlan,
                ]);
            } else {
                $this->planEvaluacion = PlanEvaluacion::create([
                    'carrera_periodo_id' => $this->carreraPeriodoId,
                    'nombre' => $this->nombrePlan,
                    'descripcion' => $this->descripcionPlan,
                ]);
            }

            $idsItemsActualesEnFormulario = [];
            foreach ($this->items as $index => $itemDataForm) {
                $calificadoPorNotaDirectaParaGuardar = null;
                if ($itemDataForm['tipo_item'] === 'NOTA_DIRECTA') {
                    $calificadoPorNotaDirectaParaGuardar = self::CALIFICADO_POR_NOTA_DIRECTA_DEFAULT;
                }

                $itemPlanEvaluacion = ItemPlanEvaluacion::updateOrCreate(
                    [
                        'id' => $itemDataForm['db_id'] ?? null,
                        'plan_evaluacion_id' => $this->planEvaluacion->id
                    ],
                    [
                        'nombre_item' => $itemDataForm['nombre_item'],
                        'tipo_item' => $itemDataForm['tipo_item'],
                        'ponderacion_global' => $itemDataForm['ponderacion_global'],
                        'rubrica_plantilla_id' => ($itemDataForm['tipo_item'] === 'RUBRICA_TABULAR') ? $itemDataForm['rubrica_plantilla_id'] : null,
                        'calificado_por_nota_directa' => $calificadoPorNotaDirectaParaGuardar,
                        'orden' => $index,
                        'plan_evaluacion_id' => $this->planEvaluacion->id,
                    ]
                );
                $idsItemsActualesEnFormulario[] = $itemPlanEvaluacion->id;

                if ($itemPlanEvaluacion->tipo_item === 'RUBRICA_TABULAR') {
                    $idsComponentesAsignadosEnFormulario = [];
                    if (!empty($itemDataForm['asignaciones_componentes'])) {
                        foreach ($itemDataForm['asignaciones_componentes'] as $componenteRubricaId => $calificadoPor) {
                            if(empty($calificadoPor)) continue;

                            AsignacionCalificadorComponentePlan::updateOrCreate(
                                [
                                    'item_plan_id' => $itemPlanEvaluacion->id,
                                    'componente_rubrica_id' => $componenteRubricaId,
                                ],
                                ['calificado_por' => $calificadoPor]
                            );
                            $idsComponentesAsignadosEnFormulario[] = $componenteRubricaId;
                        }
                    }
                    AsignacionCalificadorComponentePlan::where('item_plan_id', $itemPlanEvaluacion->id)
                        ->whereNotIn('componente_rubrica_id', $idsComponentesAsignadosEnFormulario)
                        ->delete();
                } else {
                    AsignacionCalificadorComponentePlan::where('item_plan_id', $itemPlanEvaluacion->id)->delete();
                }
            }
            ItemPlanEvaluacion::where('plan_evaluacion_id', $this->planEvaluacion->id)
                ->whereNotIn('id', $idsItemsActualesEnFormulario)
                ->delete();
        });

        session()->flash('success', 'Plan de Evaluación guardado exitosamente.');
        $this->reloadDataAfterSave();
    }

    protected function reloadDataAfterSave() // Sin cambios
    {
        $this->planEvaluacion->load([
            'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica',
            'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
        ]);
        $this->items = [];
        if ($this->planEvaluacion) {
            foreach ($this->planEvaluacion->itemsPlanEvaluacion->sortBy('orden') as $itemDB) {
                $this->items[] = $this->mapItemDBToArray($itemDB);
            }
        }
    }

    public function render() // Sin cambios respecto a tu última versión con la coerción de tipos
    {
        foreach ($this->items as $index => &$itemRef) {
            if (isset($itemRef['tipo_item']) && $itemRef['tipo_item'] === 'RUBRICA_TABULAR') {
                if (isset($itemRef['plantilla_componentes'])) {
                    if (!($itemRef['plantilla_componentes'] instanceof Collection)) {
                        if (!empty($itemRef['rubrica_plantilla_id']) && is_array($itemRef['plantilla_componentes'])) {
                            $plantilla = Rubrica::with('componentesRubrica')->find($itemRef['rubrica_plantilla_id']);
                            $itemRef['plantilla_componentes'] = $plantilla ? ($plantilla->componentesRubrica ?? collect()) : collect();
                        } elseif (is_array($itemRef['plantilla_componentes'])) {
                            $itemRef['plantilla_componentes'] = collect($itemRef['plantilla_componentes'])->map(function ($subItem) {
                                return is_array($subItem) ? (object) $subItem : $subItem;
                            });
                        } else {
                            $itemRef['plantilla_componentes'] = collect();
                        }
                    }
                } else {
                    $itemRef['plantilla_componentes'] = collect();
                }
            } elseif(isset($itemRef['plantilla_componentes']) && !$itemRef['plantilla_componentes'] instanceof Collection) {
                $itemRef['plantilla_componentes'] = collect($itemRef['plantilla_componentes']);
            } elseif(!isset($itemRef['plantilla_componentes'])) {
                 $itemRef['plantilla_componentes'] = collect();
            }

            if (isset($itemRef['componentes_rubrica_seleccionada'])) {
                if (is_array($itemRef['componentes_rubrica_seleccionada']) && !($itemRef['componentes_rubrica_seleccionada'] instanceof Collection)) {
                    $itemRef['componentes_rubrica_seleccionada'] = array_map(function($value) { // Usar array_map si es un array simple de arrays
                        return is_array($value) ? (object) $value : $value;
                    }, $itemRef['componentes_rubrica_seleccionada']);
                } else if ($itemRef['componentes_rubrica_seleccionada'] instanceof Collection) {
                     // Si ya es una colección, asegurarse que sus ítems son objetos
                    $itemRef['componentes_rubrica_seleccionada'] = $itemRef['componentes_rubrica_seleccionada']->map(function($value){
                        return is_array($value) ? (object) $value : $value;
                    });
                }
            } else {
                $itemRef['componentes_rubrica_seleccionada'] = [];
            }
        }
        unset($itemRef);

        return view('livewire.plan_evaluacion.plan-evaluacion-manager');
    }
}
