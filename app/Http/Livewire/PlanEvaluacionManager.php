<?php

namespace App\Http\Livewire;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\ItemPlanEvaluacion;
use App\Models\Periodo;
use App\Models\PlanEvaluacion;
use App\Models\Rubrica; // Para las plantillas de rúbricas
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PlanEvaluacionManager extends Component
{
    public $carreraPeriodoId;
    public $carreraPeriodo; // Objeto CarrerasPeriodo
    public $carrera;        // Objeto Carrera
    public $periodo;        // Objeto Periodo

    public $planEvaluacion; // El objeto PlanEvaluacion existente o uno nuevo
    public $nombrePlan;
    public $descripcionPlan;

    public $items = []; // Array para manejar los ítems del plan dinámicamente
    /*
    Estructura de un ítem en el array $items:
    [
        'id_temporal' => 'item_xyz', // Para wire:key
        'db_id' => null, // ID del item_plan_evaluacion si existe en BD
        'nombre_item' => '',
        'tipo_item' => 'NOTA_DIRECTA', // Valor por defecto
        'ponderacion_global' => 0,
        'rubrica_plantilla_id' => null,
        'orden' => 0 // Podríamos autoincrementar esto
    ]
    */

    public $plantillasRubricasDisponibles = [];
    public $tiposItemDisponibles = [
        'NOTA_DIRECTA' => 'Nota Directa (Ej: Cuestionario)',
        'RUBRICA_TABULAR' => 'Basado en Rúbrica Tabular (Ej: Parte Escrita)'
    ];

     protected function rules()
    {
        $rules = [
            'nombrePlan' => 'required|string|max:255',
            'descripcionPlan' => 'nullable|string',
            'items' => 'required|array|min:1',
            // Reglas base para cada ítem en el array
            'items.*.nombre_item' => 'required|string|max:255',
            'items.*.tipo_item' => 'required|in:' . implode(',', array_keys($this->tiposItemDisponibles)),
            'items.*.ponderacion_global' => 'required|numeric|min:0|max:100',
        ];

        // Añadir reglas condicionales para 'rubrica_plantilla_id'
        foreach ($this->items as $index => $item) {
            if (isset($item['tipo_item']) && $item['tipo_item'] === 'RUBRICA_TABULAR') {
                $rules["items.{$index}.rubrica_plantilla_id"] = 'required|exists:rubricas,id';
            } else {
                 $rules["items.{$index}.rubrica_plantilla_id"] = 'nullable'; // Es más seguro añadir esto
            }
        }
        return $rules;
    }

    protected $messages = [
        'items.*.nombre_item.required' => 'El nombre del ítem es obligatorio.',
        'items.*.tipo_item.required' => 'El tipo de ítem es obligatorio.',
        'items.*.ponderacion_global.required' => 'La ponderación global es obligatoria.',
        'items.*.ponderacion_global.numeric' => 'La ponderación debe ser un número.',
        'items.*.ponderacion_global.min' => 'La ponderación no puede ser negativa.',
        'items.*.ponderacion_global.max' => 'La ponderación no puede exceder 100.',
        'items.*.rubrica_plantilla_id.required' => 'Debe seleccionar una plantilla de rúbrica.',
        'items.*.rubrica_plantilla_id.exists' => 'La plantilla de rúbrica seleccionada no es válida.',
    ];

    public function mount($carreraPeriodoId)
    {
        $this->carreraPeriodoId = $carreraPeriodoId;
        $this->carreraPeriodo = CarrerasPeriodo::with('carrera', 'periodo')->find($carreraPeriodoId);

        if (!$this->carreraPeriodo) {
            // Manejar el caso de que no se encuentre el carrera_periodo_id
            session()->flash('danger', 'Contexto de Carrera-Periodo no válido.');
            return redirect()->route('periodos.'); // O a donde corresponda
        }
        $this->carrera = $this->carreraPeriodo->carrera;
        $this->periodo = $this->carreraPeriodo->periodo;

        $this->planEvaluacion = PlanEvaluacion::with('itemsPlanEvaluacion')
            ->where('carrera_periodo_id', $this->carreraPeriodoId)
            ->first();
        $this->nombrePlan = $this->planEvaluacion ? $this->planEvaluacion->nombre : '';
        $this->descripcionPlan = $this->planEvaluacion ? $this->planEvaluacion->descripcion : '';

        if ($this->planEvaluacion) {
            $this->items = []; // Limpiar para repoblar
            foreach ($this->planEvaluacion->itemsPlanEvaluacion as $itemDB) {
                $componentesRubricaSeleccionada = [];
                if ($itemDB->tipo_item === 'RUBRICA_TABULAR' && $itemDB->rubrica_plantilla_id) {
                    $plantilla = Rubrica::with('componentesRubrica')->find($itemDB->rubrica_plantilla_id);
                    if ($plantilla) {
                        foreach ($plantilla->componentesRubrica as $comp) {
                            $componentesRubricaSeleccionada[] = [
                                'nombre' => $comp->nombre,
                                'ponderacion_interna' => (float) $comp->ponderacion,
                                'ponderacion_calculada_global' => round(((float) $itemDB->ponderacion_global * (float) $comp->ponderacion) / 100, 2)
                            ];
                        }
                    }
                }

                $this->items[] = [
                    'id_temporal' => 'item_' . uniqid(),
                    'db_id' => $itemDB->id,
                    'nombre_item' => $itemDB->nombre_item,
                    'tipo_item' => $itemDB->tipo_item,
                    'ponderacion_global' => (float) $itemDB->ponderacion_global,
                    'rubrica_plantilla_id' => $itemDB->rubrica_plantilla_id,
                    'orden' => $itemDB->orden,
                    'componentes_rubrica_seleccionada' => $componentesRubricaSeleccionada, // Añadido
                ];
            }
        } else {
            $this->nombrePlan = "Plan de Evaluación para " . $this->carrera->nombre . " - " . $this->periodo->codigo_periodo;
        }

        $this->plantillasRubricasDisponibles = Rubrica::orderBy('nombre')->get();
    }

    public function addItem()
    {
        $this->items[] = [
            'id_temporal' => 'item_' . uniqid(),
            'db_id' => null,
            'nombre_item' => '',
            'tipo_item' => 'NOTA_DIRECTA', // Por defecto
            'ponderacion_global' => 0,
            'rubrica_plantilla_id' => null,
            'orden' => count($this->items),
            'componentes_rubrica_seleccionada' => [],
        ];
    }

    public function removeItem($index)
    {
        // Si el ítem tiene un 'db_id', podríamos querer marcarlo para eliminación
        // en lugar de solo quitarlo del array, para manejarlo en el guardado.
        // Por ahora, lo quitamos del array. Si se guarda, los no presentes se eliminarán.
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindexar
    }

    // Este método se activa cuando cambia el tipo_item de un ítem en el array
    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0]; // Índice del ítem en el array $items
        $field = $parts[1];     // Campo que cambió (ej. 'tipo_item', 'rubrica_plantilla_id', 'ponderacion_global')

        if (!isset($this->items[$index])) {
            return;
        }

        $itemActual = &$this->items[$index]; // Usar referencia para modificar directamente

        if ($field === 'tipo_item') {
            if ($itemActual['tipo_item'] !== 'RUBRICA_TABULAR') {
                $itemActual['rubrica_plantilla_id'] = null;
                $itemActual['componentes_rubrica_seleccionada'] = [];
            } else {
                // Si se cambia a RUBRICA_TABULAR, no hacer nada aún, esperar a que se seleccione una plantilla
                $itemActual['componentes_rubrica_seleccionada'] = []; // Limpiar por si acaso
            }
        }

        if ($field === 'rubrica_plantilla_id' && $itemActual['tipo_item'] === 'RUBRICA_TABULAR') {
            if (!empty($itemActual['rubrica_plantilla_id'])) {
                $plantilla = Rubrica::with('componentesRubrica')->find($itemActual['rubrica_plantilla_id']);
                $itemActual['componentes_rubrica_seleccionada'] = []; // Limpiar antes de repoblar
                if ($plantilla) {
                    foreach ($plantilla->componentesRubrica as $comp) {
                        $itemActual['componentes_rubrica_seleccionada'][] = [
                            'nombre' => $comp->nombre,
                            'ponderacion_interna' => (float) $comp->ponderacion,
                            // Calcular la ponderación global al cargar la rúbrica
                            'ponderacion_calculada_global' => round(((float) $itemActual['ponderacion_global'] * (float) $comp->ponderacion) / 100, 2)
                        ];
                    }
                }
            } else {
                $itemActual['componentes_rubrica_seleccionada'] = []; // No hay plantilla seleccionada
            }
        }

        if ($field === 'ponderacion_global' && $itemActual['tipo_item'] === 'RUBRICA_TABULAR' && !empty($itemActual['componentes_rubrica_seleccionada'])) {
            // Recalcular ponderaciones de componentes si la ponderación global del ítem cambia
            foreach ($itemActual['componentes_rubrica_seleccionada'] as &$compDetalle) { // Usar referencia aquí también
                $compDetalle['ponderacion_calculada_global'] = round(((float) $itemActual['ponderacion_global'] * (float) $compDetalle['ponderacion_interna']) / 100, 2);
            }
        }
    }


    public function savePlan()
    {
        $this->validate();

        // Validación adicional: suma de ponderaciones globales debe ser 100%
        $totalPonderacionGlobal = 0;
        foreach ($this->items as $item) {
            $totalPonderacionGlobal += (float) $item['ponderacion_global'];
        }

        if (round($totalPonderacionGlobal, 2) != 100.00) {
            $this->addError('ponderacion_total_global', 'La suma de las ponderaciones globales de todos los ítems debe ser 100%. Actual: ' . $totalPonderacionGlobal . '%');
            return;
        }

        DB::transaction(function () {
            if ($this->planEvaluacion) {
                // Actualizar plan existente
                $this->planEvaluacion->update([
                    'nombre' => $this->nombrePlan,
                    'descripcion' => $this->descripcionPlan,
                ]);
            } else {
                // Crear nuevo plan
                $this->planEvaluacion = PlanEvaluacion::create([
                    'carrera_periodo_id' => $this->carreraPeriodoId,
                    'nombre' => $this->nombrePlan,
                    'descripcion' => $this->descripcionPlan,
                ]);
            }

            // Sincronizar ítems (estrategia: eliminar los viejos y crear los nuevos)
            $this->planEvaluacion->itemsPlanEvaluacion()->delete(); // Elimina todos los ítems actuales del plan

            foreach ($this->items as $index => $itemData) {
                ItemPlanEvaluacion::create([
                    'plan_evaluacion_id' => $this->planEvaluacion->id,
                    'nombre_item' => $itemData['nombre_item'],
                    'tipo_item' => $itemData['tipo_item'],
                    'ponderacion_global' => $itemData['ponderacion_global'],
                    'rubrica_plantilla_id' => ($itemData['tipo_item'] === 'RUBRICA_TABULAR') ? $itemData['rubrica_plantilla_id'] : null,
                    'orden' => $index, // Usar el índice del array como orden
                ]);
            }
        });

        session()->flash('success', 'Plan de Evaluación guardado exitosamente.');
        // Podrías quedarte en la página o redirigir
        return redirect()->route('periodos.tribunales.index', $this->carreraPeriodoId);
    }

    public function render()
    {
        return view('livewire.plan_evaluacion.plan-evaluacion-manager');
    }
}
