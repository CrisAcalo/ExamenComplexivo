<?php

namespace App\Http\Livewire;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\Estudiante;
use App\Models\MiembroCalificacion;
use App\Models\MiembrosTribunal;
use App\Models\Periodo;
use App\Models\PlanEvaluacion;
use App\Models\Tribunale;
use App\Models\User;
use App\Models\CalificadorGeneralCarreraPeriodo; // Nuevo modelo
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Para verificar permisos si es necesario
use Illuminate\Support\Facades\Gate; // Para verificar permisos
use Livewire\Component;
use Livewire\WithPagination;

class Tribunales extends Component
{
    use WithPagination;
    public $carreraPeriodoId;

    protected $paginationTheme = 'bootstrap';

    // Propiedades para el listado de tribunales
    public $keyWord = '';

    // Propiedades para el modal de creación/edición de tribunal
    public $selected_id; // Para edición (aunque tu modal actual es solo para creación)
    public $estudiante_id;
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $presidente_id;
    public $integrante1_id;
    public $integrante2_id;

    // Datos cargados en mount
    public ?CarrerasPeriodo $carreraPeriodo = null;
    public ?Carrera $carrera = null;
    public ?Periodo $periodo = null;
    public $profesores; // Lista de todos los profesores para selects
    public $estudiantesDisponibles;

    // Para mostrar el Plan de Evaluación
    public ?PlanEvaluacion $planEvaluacionActivo = null;
    // Para mostrar "Calificado Por" en la vista del plan (copiado de PlanEvaluacionManager para consistencia)
    public $opcionesCalificadoPorNotaDirecta = [
        'DIRECTOR_CARRERA' => 'Director de Carrera',
        'DOCENTE_APOYO'    => 'Docente de Apoyo',
    ];


    // NUEVO: Para Calificadores Generales
    public $calificadoresGeneralesSeleccionados = []; // Array de user_ids [0 => id1, 1 => id2, 2 => id3]
    public $profesoresDisponiblesParaCalificadorGeneral; // Lista de profesores para los selects de calificadores

    // Para el modal de eliminación de tribunal
    public ?Tribunale $tribunalAEliminar = null;

    public $profesoresParaTribunal;

    protected function rules()
    {
        // Reglas para el modal de CREACIÓN de tribunal
        $rules = [
            'estudiante_id' => 'required|exists:estudiantes,id|unique:tribunales,estudiante_id,NULL,id,carrera_periodo_id,' . $this->carreraPeriodoId,
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'presidente_id' => 'required|exists:users,id|different:integrante1_id|different:integrante2_id',
            'integrante1_id' => 'required|exists:users,id|different:presidente_id|different:integrante2_id',
            'integrante2_id' => 'required|exists:users,id|different:presidente_id|different:integrante1_id',
        ];

        // Validar que los miembros del tribunal seleccionados estén en la lista filtrada y no sean excluidos
        $validProfessorIdsParaTribunal = $this->profesoresParaTribunal->pluck('id')->implode(',');

        $rules['presidente_id'] = "required|exists:users,id|different:integrante1_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
        $rules['integrante1_id'] = "required|exists:users,id|different:presidente_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
        $rules['integrante2_id'] = "required|exists:users,id|different:presidente_id|different:integrante1_id|in:{$validProfessorIdsParaTribunal}";

        $rules['calificadoresGeneralesSeleccionados'] = 'array|max:3';
        $rules['calificadoresGeneralesSeleccionados.*'] = ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
            if ($value !== null) {
                // Validar que no sea Director, Apoyo, Admin o miembro de tribunal
                if ($value == $this->carreraPeriodo->director_id || $value == $this->carreraPeriodo->docente_apoyo_id) {
                    $fail('El Director o Docente de Apoyo no pueden ser Calificadores Generales.');
                    return;
                }
                $user = User::find($value);
                if ($user && $user->hasRole('Administrador')) {
                    $fail('Un Administrador no puede ser Calificador General.');
                    return;
                }
                $esMiembroTribunal = MiembrosTribunal::join('tribunales', 'miembros_tribunales.tribunal_id', '=', 'tribunales.id')
                    ->where('tribunales.carrera_periodo_id', $this->carreraPeriodoId)
                    ->where('miembros_tribunales.user_id', $value)
                    ->exists();
                if ($esMiembroTribunal) {
                    $fail('Este docente ya es miembro de un tribunal en este período/carrera y no puede ser Calificador General.');
                    return;
                }

                // Validar duplicados en la selección actual de calificadores
                $count = collect($this->calificadoresGeneralesSeleccionados)->filter()->filter(function ($id) use ($value) {
                    return $id == $value;
                })->count();
                if ($count > 1) {
                    $fail('El profesor ' . ($user->name ?? '') . ' ya ha sido seleccionado como calificador general.');
                }
            }
        }];
        return $rules;
    }

    public function messages()
    {
        return [
            'estudiante_id.required' => 'Debe seleccionar un estudiante.',
            'estudiante_id.unique' => 'Este estudiante ya tiene un tribunal asignado en este período y carrera.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'presidente_id.required' => 'Debe seleccionar un presidente.',
            'presidente_id.different' => 'El presidente no puede ser igual a otro miembro.',
            'integrante1_id.required' => 'Debe seleccionar el integrante 1.',
            'integrante1_id.different' => 'El integrante 1 no puede ser igual a otro miembro.',
            'integrante2_id.required' => 'Debe seleccionar el integrante 2.',
            'integrante2_id.different' => 'El integrante 2 no puede ser igual a otro miembro.',
            'calificadoresGeneralesSeleccionados.*.exists' => 'El profesor seleccionado como calificador general no es válido.',
        ];
    }


    public function mount($carreraPeriodoId)
    {
        $this->carreraPeriodoId = $carreraPeriodoId;
        $this->carreraPeriodo = CarrerasPeriodo::with(['carrera', 'periodo', 'director', 'docenteApoyo'])->find($carreraPeriodoId);

        if (!$this->carreraPeriodo) {
            abort(404, 'Contexto Carrera-Periodo no encontrado.');
        }

        $this->carrera = $this->carreraPeriodo->carrera;
        $this->periodo = $this->carreraPeriodo->periodo;

        // Lista base de todos los profesores potenciales (excluyendo Super Admin si es necesario)
        $rolesExcluidos = ['Super Admin', 'Administrador']; // Roles a excluir de ser seleccionables
        $this->profesores = User::whereDoesntHave('roles', function ($query) use ($rolesExcluidos) {
            $query->whereIn('name', $rolesExcluidos);
        })
            ->orderBy('name')->get();

        $this->loadEstudiantesDisponibles();
        $this->loadPlanEvaluacionActivo();
        $this->loadCalificadoresGeneralesExistentes(); // Esto puebla $this->calificadoresGeneralesSeleccionados

        // Ahora filtramos las listas de profesores disponibles
        $this->actualizarProfesoresDisponibles();
    }

    protected function loadEstudiantesDisponibles()
    {
        $estudiantesConTribunalIds = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->pluck('estudiante_id')->toArray();
        $this->estudiantesDisponibles = Estudiante::whereNotIn('id', $estudiantesConTribunalIds)
            ->orderBy('apellidos')->orderBy('nombres')->get();
    }

    protected function loadPlanEvaluacionActivo()
    {
        $this->planEvaluacionActivo = PlanEvaluacion::with('itemsPlanEvaluacion.rubricaPlantilla')
            ->where('carrera_periodo_id', $this->carreraPeriodoId)
            ->first();
    }

    protected function loadCalificadoresGeneralesExistentes()
    {
        $this->calificadoresGeneralesSeleccionados = [];
        $calificadores = CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->pluck('user_id')->toArray();

        // Llenar el array hasta 3 elementos, usando null si hay menos de 3 asignados
        for ($i = 0; $i < 3; $i++) {
            $this->calificadoresGeneralesSeleccionados[$i] = $calificadores[$i] ?? null;
        }
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        $tribunales = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->with(['estudiante', 'miembrosTribunales.user'])
            ->where(function ($query) use ($keyWord) {
                $query->whereHas('estudiante', function ($q) use ($keyWord) {
                    $q->where('nombres', 'LIKE', $keyWord)
                        ->orWhere('apellidos', 'LIKE', $keyWord);
                })
                    ->orWhere('fecha', 'LIKE', $keyWord); // Búsqueda por fecha si es un string YYYY-MM-DD
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'asc')
            ->paginate(10);

        return view('livewire.tribunales.view', [
            'tribunales' => $tribunales,
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
        $this->resetValidation();
    }

    private function resetInput()
    {
        $this->estudiante_id = null;
        $this->fecha = now()->format('Y-m-d'); // Default a hoy
        $this->hora_inicio = null;
        $this->hora_fin = null;
        $this->presidente_id = null;
        $this->integrante1_id = null;
        $this->integrante2_id = null;
    }

    public function store() // Crear Tribunal
    {
        // Definir explícitamente las reglas SOLO para la creación del tribunal
        $tribunalRules = [
            'estudiante_id' => 'required|exists:estudiantes,id|unique:tribunales,estudiante_id,NULL,id,carrera_periodo_id,' . $this->carreraPeriodoId,
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ];

        $validProfessorIdsParaTribunal = $this->profesoresParaTribunal->pluck('id')->implode(',');
        if (empty($validProfessorIdsParaTribunal)) { // Si no hay profesores válidos, la regla 'in' fallará o será vacía
            // Podrías añadir un error general o manejarlo de otra forma
            session()->flash('danger', 'No hay profesores válidos disponibles para formar el tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $tribunalRules['presidente_id'] = "required|exists:users,id|different:integrante1_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
        $tribunalRules['integrante1_id'] = "required|exists:users,id|different:presidente_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
        $tribunalRules['integrante2_id'] = "required|exists:users,id|different:presidente_id|different:integrante1_id|in:{$validProfessorIdsParaTribunal}";

        // Validar solo estas reglas
        $validatedData = $this->validate($tribunalRules); // Usar $this->validate() con las reglas específicas

        // La validación unique ya maneja esto, pero un doble check no hace daño si la regla unique se quita o cambia.
        // $existingTribunalForStudent = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
        //     ->where('estudiante_id', $this->estudiante_id)
        //     ->exists();
        // if ($existingTribunalForStudent) {
        //     $this->addError('estudiante_id', 'Este estudiante ya tiene un tribunal asignado en este periodo y carrera.');
        //     return;
        // }

        DB::transaction(function () use ($validatedData) { // Usar $validatedData
            $newTribunale = Tribunale::create([
                'carrera_periodo_id' => $this->carreraPeriodoId,
                'estudiante_id' => $validatedData['estudiante_id'], // Usar datos validados
                'fecha' => $validatedData['fecha'],
                'hora_inicio' => $validatedData['hora_inicio'],
                'hora_fin' => $validatedData['hora_fin'],
            ]);

            $newTribunale->miembrosTribunales()->createMany([
                ['user_id' => $validatedData['presidente_id'], 'status' => 'PRESIDENTE'],
                ['user_id' => $validatedData['integrante1_id'], 'status' => 'INTEGRANTE1'],
                ['user_id' => $validatedData['integrante2_id'], 'status' => 'INTEGRANTE2'],
            ]);
        });

        session()->flash('success', 'Tribunal Creado Exitosamente.');
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        $this->resetInput();
        $this->loadEstudiantesDisponibles();
        $this->actualizarProfesoresDisponibles();
    }

    // --- MÉTODOS PARA CALIFICADORES GENERALES ---
    public function guardarCalificadoresGenerales()
    {
        if (!Gate::allows('gestionar-calificadores-generales', $this->carreraPeriodo)) {
            session()->flash('danger', 'No tiene permisos para gestionar los calificadores generales.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Validar solo los campos de calificadores
        $this->validate([
            'calificadoresGeneralesSeleccionados' => 'array|max:3',
            'calificadoresGeneralesSeleccionados.*' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                if ($value !== null) {
                    if ($value == $this->carreraPeriodo->director_id || $value == $this->carreraPeriodo->docente_apoyo_id) {
                        $fail('El Director o Docente de Apoyo no pueden ser Calificadores Generales.');
                        return;
                    }
                    $user = User::find($value);
                    if ($user && $user->hasRole('Administrador')) {
                        $fail('Un Administrador no puede ser Calificador General.');
                        return;
                    }
                    $esMiembroTribunal = MiembrosTribunal::join('tribunales', 'miembros_tribunales.tribunal_id', '=', 'tribunales.id')
                        ->where('tribunales.carrera_periodo_id', $this->carreraPeriodoId)
                        ->where('miembros_tribunales.user_id', $value)
                        ->exists();
                    if ($esMiembroTribunal) {
                        $fail('Este docente ya es miembro de un tribunal y no puede ser Calificador General.');
                        return;
                    }
                    $count = collect($this->calificadoresGeneralesSeleccionados)->filter()->filter(fn($id) => $id == $value)->count();
                    if ($count > 1) {
                        $fail('El profesor ' . ($user->name ?? '') . ' ha sido seleccionado más de una vez.');
                        return;
                    }
                }
            }],
        ]);

        DB::transaction(function () {
            CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $this->carreraPeriodoId)->delete();
            foreach ($this->calificadoresGeneralesSeleccionados as $userId) {
                if (!empty($userId)) {
                    CalificadorGeneralCarreraPeriodo::create([
                        'carrera_periodo_id' => $this->carreraPeriodoId,
                        'user_id' => $userId,
                    ]);
                }
            }
        });

        session()->flash('success', 'Calificadores Generales guardados exitosamente.');
        $this->dispatchBrowserEvent('showFlashMessage');
        $this->loadCalificadoresGeneralesExistentes(); // Recargar para la vista
        $this->actualizarProfesoresDisponibles(); // Actualizar listas después de cambiar calificadores
    }


    // --- MÉTODOS PARA ELIMINAR TRIBUNAL ---
    public function confirmDelete($tribunalId)
    {
        $tribunal = Tribunale::with('miembrosTribunales.calificacionesRegistradas')->find($tribunalId);

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar si el tribunal tiene calificaciones (más robusto)
        $tieneCalificaciones = false;
        foreach ($tribunal->miembrosTribunales as $miembro) {
            if ($miembro->calificacionesRegistradas->isNotEmpty()) {
                $tieneCalificaciones = true;
                break;
            }
        }

        if ($tieneCalificaciones) {
            session()->flash('warning', 'Este tribunal no se puede eliminar porque ya tiene calificaciones registradas.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $this->tribunalAEliminar = $tribunal;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteTribunalModal']);
    }

    public function destroy()
    {
        if (!$this->tribunalAEliminar) {
            session()->flash('danger', 'Error: No se ha especificado el tribunal a eliminar.');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteTribunalModal']);
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            DB::transaction(function () {
                // Los logs asociados al tribunal también se eliminarán por la cascada de la FK
                $this->tribunalAEliminar->delete();
            });
            session()->flash('success', 'Tribunal eliminado exitosamente.');
            $this->loadEstudiantesDisponibles();
            $this->actualizarProfesoresDisponibles();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                session()->flash('danger', 'No se puede eliminar el tribunal porque tiene datos relacionados que impiden su borrado.');
            } else {
                session()->flash('danger', 'Error de base de datos al intentar eliminar el tribunal.');
            }
        } catch (\Exception $e) {
            session()->flash('danger', 'Ocurrió un error inesperado al eliminar el tribunal.');
        }

        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteTribunalModal']);
        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    // En App\Http\Livewire\Tribunales.php

    protected function actualizarProfesoresDisponibles()
    {
        $idsCalificadoresGeneralesActuales = collect($this->calificadoresGeneralesSeleccionados)->filter()->values()->toArray();

        $idsMiembrosDeTribunalesActuales = MiembrosTribunal::join('tribunales', 'miembros_tribunales.tribunal_id', '=', 'tribunales.id')
            ->where('tribunales.carrera_periodo_id', $this->carreraPeriodoId)
            ->pluck('miembros_tribunales.user_id')
            ->unique()
            ->toArray();

        // Profesores base (ya filtrados en mount para no incluir Super Admin/Admin)
        $baseProfesores = $this->profesores;

        // Filtrar para Calificadores Generales
        $this->profesoresDisponiblesParaCalificadorGeneral = $baseProfesores->filter(function ($profesor) use ($idsMiembrosDeTribunalesActuales) {
            if ($profesor->id == $this->carreraPeriodo->director_id) return false;
            if ($profesor->id == $this->carreraPeriodo->docente_apoyo_id) return false;
            if (in_array($profesor->id, $idsMiembrosDeTribunalesActuales)) return false;
            return true;
        })->values();

        // Filtrar para Miembros de Tribunal
        $this->profesoresParaTribunal = $baseProfesores->filter(function ($profesor) use ($idsCalificadoresGeneralesActuales) {
            if ($profesor->id == $this->carreraPeriodo->director_id) return false;
            if ($profesor->id == $this->carreraPeriodo->docente_apoyo_id) return false;
            if (in_array($profesor->id, $idsCalificadoresGeneralesActuales)) return false;
            return true;
        })->values();
    }

    public function resetDeleteConfirmation()
    {
        $this->tribunalAEliminar = null;
    }
}
