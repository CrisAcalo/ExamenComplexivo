<?php

namespace App\Http\Livewire;

use App\Models\Tribunale;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\PlanEvaluacion;
use App\Models\ItemPlanEvaluacion;
use App\Models\MiembroCalificacion;
use App\Models\CalificacionCriterio;
use App\Models\MiembrosTribunal;
use App\Models\MiembrosTribunale; // Para cargar todas las calificaciones
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate; // Importar Gate

class TribunalProfile extends Component
{
    public $tribunalId;
    public $tribunal;

    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $estudiante_id;
    public $presidente_id;
    public $integrante1_id;
    public $integrante2_id;

    public $profesoresDisponibles;
    public $planEvaluacionActivo;
    public $calificaciones = []; // Para las calificaciones del usuario actual (si es miembro)

    // NUEVO: Para mostrar todas las calificaciones (Admin, Director, Apoyo)
    public $todasLasCalificacionesDelTribunal = [];
    /*
    Estructura de $todasLasCalificacionesDelTribunal:
    [
        'miembro_user_id_1' => [
            'nombre_miembro' => 'Nombre Docente 1',
            'rol_miembro' => 'PRESIDENTE',
            'calificaciones_ingresadas' => [ // Similar a la estructura de $calificaciones
                'item_plan_id_1' => ['tipo' => 'NOTA_DIRECTA', 'nota_directa' => 18, 'observacion_general' => '...'],
                // ...
            ]
        ],
        // ...
    ]
    */

    public $modoEdicionTribunal = false;
    public $usuarioEsMiembroDelTribunal = false; // Renombrado para claridad
    public $usuarioPuedeCalificar = false;      // Renombrado
    public $usuarioPuedeEditarDatosTribunal = false;
    public $usuarioPuedeVerTodasLasCalificaciones = false;


    protected function rules()
    {
        // ... (Reglas para actualizar datos del tribunal sin cambios) ...
        $rules = [
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ];
        if ($this->modoEdicionTribunal && $this->usuarioPuedeEditarDatosTribunal) {
            $rules['presidente_id'] = 'required|exists:users,id|different:integrante1_id|different:integrante2_id';
            $rules['integrante1_id'] = 'required|exists:users,id|different:presidente_id|different:integrante2_id';
            $rules['integrante2_id'] = 'required|exists:users,id|different:presidente_id|different:integrante1_id';
        }

        // ... (Reglas para calificaciones sin cambios) ...
        if ($this->planEvaluacionActivo && $this->usuarioPuedeCalificar) {
            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                // ... (lógica de reglas de calificación existente)
                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    $rules["calificaciones.{$itemPlan->id}.nota_directa"] = 'required|numeric|min:0|max:20';
                    $rules["calificaciones.{$itemPlan->id}.observacion_general"] = 'nullable|string|max:1000';
                } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    $rules["calificaciones.{$itemPlan->id}.observacion_general"] = 'nullable|string|max:1000';
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        foreach ($componenteR->criteriosComponente as $criterioR) {
                            $rules["calificaciones.{$itemPlan->id}.componentes_evaluados.{$componenteR->id}.criterios_evaluados.{$criterioR->id}.calificacion_criterio_id"] = 'required|exists:calificaciones_criterio,id';
                            $rules["calificaciones.{$itemPlan->id}.componentes_evaluados.{$componenteR->id}.criterios_evaluados.{$criterioR->id}.observacion"] = 'nullable|string|max:500';
                        }
                    }
                }
            }
        }
        return $rules;
    }

    public function validationAttributes()
    {
        // ... (sin cambios) ...
        $attributes = [];
        if ($this->planEvaluacionActivo) {
            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    $attributes["calificaciones.{$itemPlan->id}.nota_directa"] = "nota para '{$itemPlan->nombre_item}'";
                } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        foreach ($componenteR->criteriosComponente as $criterioR) {
                            $attributes["calificaciones.{$itemPlan->id}.componentes_evaluados.{$componenteR->id}.criterios_evaluados.{$criterioR->id}.calificacion_criterio_id"] = "calificación para criterio '{$criterioR->nombre}' en '{$componenteR->nombre}'";
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
        if (!$this->loadTribunalData()) { // loadTribunalData ahora retorna false si no encuentra el tribunal
            return; // Detener ejecución si el tribunal no se carga
        }
        $this->profesoresDisponibles = User::role(['Docente', 'Director de Carrera', 'Docente de Apoyo', 'Administrador']) // Roles que pueden ser miembros
                                        ->orderBy('name')->get();
        $this->checkUserPermissions();

        if ($this->usuarioPuedeCalificar) {
            $this->loadCalificacionesExistentesParaUsuarioActual();
        }
        if ($this->usuarioPuedeVerTodasLasCalificaciones) {
            $this->loadTodasLasCalificacionesDelTribunal();
        }
    }

    public function loadTribunalData()
    {
        $this->tribunal = Tribunale::with([
            'carrerasPeriodo.carrera',
            'carrerasPeriodo.periodo',
            'estudiante',
            'miembrosTribunales.user'
        ])->find($this->tribunalId);

        if (!$this->tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            // No podemos redirigir desde mount directamente antes de que Livewire inicialice.
            // Marcar para redirigir en render o manejar en la vista.
            // Por ahora, solo retornamos false para que mount pueda detenerse.
            return false;
        }

        $this->fecha = $this->tribunal->fecha;
        $this->hora_inicio = $this->tribunal->hora_inicio;
        $this->hora_fin = $this->tribunal->hora_fin;
        $this->estudiante_id = $this->tribunal->estudiante_id;

        foreach ($this->tribunal->miembrosTribunales as $miembro) {
            if ($miembro->status == 'PRESIDENTE') $this->presidente_id = $miembro->user_id;
            if ($miembro->status == 'INTEGRANTE1') $this->integrante1_id = $miembro->user_id;
            if ($miembro->status == 'INTEGRANTE2') $this->integrante2_id = $miembro->user_id;
        }

        $this->planEvaluacionActivo = PlanEvaluacion::with([
            'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica.criteriosComponente.calificacionesCriterio'
        ])
        ->where('carrera_periodo_id', $this->tribunal->carrera_periodo_id)
        ->first();
        return true; // Indicar que se cargó correctamente
    }

    public function checkUserPermissions()
    {
        $user = Auth::user();
        if (!$user || !$this->tribunal) return;

        $this->usuarioEsMiembroDelTribunal = $this->tribunal->miembrosTribunales->contains('user_id', $user->id);

        // Usar Gates definidos en AuthServiceProvider
        $this->usuarioPuedeCalificar = Gate::allows('calificar-este-tribunal', $this->tribunal);
        $this->usuarioPuedeEditarDatosTribunal = Gate::allows('editar-datos-basicos-este-tribunal-como-presidente', $this->tribunal) || $user->hasRole('Administrador'); // Admin también puede
        $this->usuarioPuedeVerTodasLasCalificaciones = Gate::allows('ver-todas-calificaciones-de-este-tribunal', $this->tribunal);
    }

    public function loadCalificacionesExistentesParaUsuarioActual() // Renombrado
    {
        if (!$this->planEvaluacionActivo || !$this->usuarioEsMiembroDelTribunal) {
            $this->calificaciones = []; // Limpiar si no es miembro o no hay plan
            return;
        }
        // ... (lógica de loadCalificacionesExistentes sin cambios, pero ahora se llama condicionalmente) ...
        $this->calificaciones = [];
        $miembroTribunalActual = $this->tribunal->miembrosTribunales()
            ->where('user_id', Auth::id())
            ->first();
        if (!$miembroTribunalActual) return;
        $calificacionesGuardadas = MiembroCalificacion::where('miembro_tribunal_id', $miembroTribunalActual->id)
            ->get(); // No usar keyBy aquí, lo procesaremos diferente

        foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $this->calificaciones[$itemPlan->id] = [
                'tipo' => $itemPlan->tipo_item,
                'observacion_general' => $calificacionesGuardadas->firstWhere('item_plan_evaluacion_id', $itemPlan->id)?->observacion ?? '',
            ];

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                $this->calificaciones[$itemPlan->id]['nota_directa'] = $calificacionesGuardadas->firstWhere('item_plan_evaluacion_id', $itemPlan->id)?->nota_obtenida_directa ?? null;
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                $this->calificaciones[$itemPlan->id]['rubrica_plantilla_id'] = $itemPlan->rubrica_plantilla_id;
                $this->calificaciones[$itemPlan->id]['componentes_evaluados'] = [];
                foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                    $this->calificaciones[$itemPlan->id]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'] = [];
                    foreach ($componenteR->criteriosComponente as $criterioR) {
                        $califCritGuardada = $calificacionesGuardadas->firstWhere('criterio_id', $criterioR->id);
                        $this->calificaciones[$itemPlan->id]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'][$criterioR->id] = [
                            'calificacion_criterio_id' => $califCritGuardada?->calificacion_criterio_id ?? null,
                            'observacion' => $califCritGuardada?->observacion ?? '',
                            'opciones_calificacion' => $criterioR->calificacionesCriterio->sortByDesc('valor')
                        ];
                    }
                }
            }
        }
    }

    // NUEVO MÉTODO
    public function loadTodasLasCalificacionesDelTribunal()
    {
        if (!$this->planEvaluacionActivo || !$this->tribunal) return;
        $this->todasLasCalificacionesDelTribunal = [];

        $miembrosDelTribunal = MiembrosTribunal::with('user')->where('tribunal_id', $this->tribunal->id)->get();

        foreach($miembrosDelTribunal as $miembro) {
            $calificacionesEsteMiembro = [];
            $susCalificacionesGuardadas = MiembroCalificacion::where('miembro_tribunal_id', $miembro->id)
                                          ->get(); // No usar keyBy, procesar

            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                $datosItem = [
                    'tipo' => $itemPlan->tipo_item,
                    'nombre_item_plan' => $itemPlan->nombre_item, // Para la vista
                    'observacion_general' => $susCalificacionesGuardadas->firstWhere('item_plan_evaluacion_id', $itemPlan->id)?->observacion ?? '',
                ];

                if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                    $datosItem['nota_directa'] = $susCalificacionesGuardadas->firstWhere('item_plan_evaluacion_id', $itemPlan->id)?->nota_obtenida_directa ?? 'N/A';
                } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    $datosItem['rubrica_plantilla_nombre'] = $itemPlan->rubricaPlantilla->nombre;
                    $datosItem['componentes_evaluados'] = [];
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        $datosItem['componentes_evaluados'][$componenteR->id]['nombre_componente_rubrica'] = $componenteR->nombre;
                        $datosItem['componentes_evaluados'][$componenteR->id]['criterios_evaluados'] = [];
                        foreach ($componenteR->criteriosComponente as $criterioR) {
                            $califCritGuardada = $susCalificacionesGuardadas->firstWhere('criterio_id', $criterioR->id);
                            $opcionCalificacionElegida = $califCritGuardada ? CalificacionCriterio::find($califCritGuardada->calificacion_criterio_id) : null;

                            $datosItem['componentes_evaluados'][$componenteR->id]['criterios_evaluados'][$criterioR->id] = [
                                'nombre_criterio_rubrica' => $criterioR->nombre,
                                'calificacion_elegida_nombre' => $opcionCalificacionElegida?->nombre ?? 'N/A',
                                'calificacion_elegida_valor' => $opcionCalificacionElegida?->valor ?? 'N/A',
                                'observacion' => $califCritGuardada?->observacion ?? '',
                            ];
                        }
                    }
                }
                $calificacionesEsteMiembro[$itemPlan->id] = $datosItem;
            }
            $this->todasLasCalificacionesDelTribunal[$miembro->user_id] = [
                'nombre_miembro' => $miembro->user->name,
                'rol_miembro' => $miembro->status,
                'calificaciones_ingresadas' => $calificacionesEsteMiembro
            ];
        }
    }


    public function toggleModoEdicionTribunal()
    {
        if ($this->usuarioPuedeEditarDatosTribunal) {
            $this->modoEdicionTribunal = !$this->modoEdicionTribunal;
            if (!$this->modoEdicionTribunal) {
                $this->loadTribunalData(); // Recargar datos originales si se cancela
                $this->resetValidation();
            }
        } else {
            session()->flash('danger', 'No tiene permisos para editar los datos de este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }

    public function actualizarDatosTribunal()
    {
        if (!$this->usuarioPuedeEditarDatosTribunal) {
             session()->flash('danger', 'No tiene permisos para actualizar este tribunal.');
             $this->dispatchBrowserEvent('showFlashMessage');
             return;
        }
        // Validar solo los campos relevantes para la edición del tribunal
        $this->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'presidente_id' => 'required|exists:users,id|different:integrante1_id|different:integrante2_id',
            'integrante1_id' => 'required|exists:users,id|different:presidente_id|different:integrante2_id',
            'integrante2_id' => 'required|exists:users,id|different:presidente_id|different:integrante1_id',
        ]);
        // ... (lógica de actualizarDatosTribunal sin cambios en la transacción) ...
        DB::transaction(function () {
            $this->tribunal->update([
                'fecha' => $this->fecha,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
            ]);
            $this->tribunal->miembrosTribunales()->delete();
            $this->tribunal->miembrosTribunales()->createMany([
                ['user_id' => $this->presidente_id, 'status' => 'PRESIDENTE'],
                ['user_id' => $this->integrante1_id, 'status' => 'INTEGRANTE1'],
                ['user_id' => $this->integrante2_id, 'status' => 'INTEGRANTE2'],
            ]);
        });
        session()->flash('success', 'Datos del tribunal actualizados.');
        $this->modoEdicionTribunal = false;
        $this->loadTribunalData();
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function guardarCalificaciones()
    {
        if (!$this->usuarioPuedeCalificar) {
            session()->flash('danger', 'No tiene permisos para registrar calificaciones.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }
        // ... (lógica de guardarCalificaciones sin cambios en su núcleo, pero se valida usando $this->rules()) ...
        $this->validate(); // Validará las reglas de calificaciones dinámicas
        $miembroTribunalActual = $this->tribunal->miembrosTribunales()
            ->where('user_id', Auth::id())
            ->first();
        if (!$miembroTribunalActual) return;

        DB::transaction(function() use ($miembroTribunalActual) {
            MiembroCalificacion::where('miembro_tribunal_id', $miembroTribunalActual->id)->delete();
            foreach ($this->calificaciones as $itemPlanId => $datosItem) {
                if ($datosItem['tipo'] === 'NOTA_DIRECTA') {
                    MiembroCalificacion::create([
                        'miembro_tribunal_id' => $miembroTribunalActual->id,
                        'item_plan_evaluacion_id' => $itemPlanId,
                        'nota_obtenida_directa' => $datosItem['nota_directa'],
                        'observacion' => $datosItem['observacion_general'] ?? null,
                    ]);
                } elseif ($datosItem['tipo'] === 'RUBRICA_TABULAR') {
                    if (!empty($datosItem['observacion_general'])) {
                        MiembroCalificacion::create([
                            'miembro_tribunal_id' => $miembroTribunalActual->id,
                            'item_plan_evaluacion_id' => $itemPlanId,
                            'observacion' => $datosItem['observacion_general'],
                        ]);
                    }
                    if(isset($datosItem['componentes_evaluados'])) { // Asegurarse que exista
                        foreach ($datosItem['componentes_evaluados'] as $componenteId => $datosComponente) {
                            if(isset($datosComponente['criterios_evaluados'])) { // Asegurarse que exista
                                foreach ($datosComponente['criterios_evaluados'] as $criterioId => $datosCriterio) {
                                    if(isset($datosCriterio['calificacion_criterio_id']) && $datosCriterio['calificacion_criterio_id'] !== null){ // Guardar solo si se seleccionó una calificación
                                        MiembroCalificacion::create([
                                            'miembro_tribunal_id' => $miembroTribunalActual->id,
                                            'criterio_id' => $criterioId,
                                            'calificacion_criterio_id' => $datosCriterio['calificacion_criterio_id'],
                                            'observacion' => $datosCriterio['observacion'] ?? null,
                                            // Podrías añadir item_plan_evaluacion_id aquí también si es útil para agrupar luego
                                            // 'item_plan_evaluacion_id' => $itemPlanId
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
        session()->flash('success', 'Calificaciones guardadas exitosamente.');
        $this->loadCalificacionesExistentesParaUsuarioActual();
        if ($this->usuarioPuedeVerTodasLasCalificaciones) { // Recargar todas si el usuario puede verlas
            $this->loadTodasLasCalificacionesDelTribunal();
        }
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function render()
    {
        if (!$this->tribunal && $this->tribunalId) { // Si el tribunal no se cargó en mount (ej. no encontrado)
             // Redirigir o mostrar un mensaje de error en la vista.
             // La redirección desde render es más segura en Livewire.
            session()->flash('danger', 'Tribunal no encontrado o no tiene acceso.');
            return view('livewire.empty-view')->layout('layouts.panel') // Una vista vacía o de error
                   ->with('message', 'Tribunal no disponible.');
        }
        return view('livewire.tribunales.profile.tribunal-profile');
    }
}
