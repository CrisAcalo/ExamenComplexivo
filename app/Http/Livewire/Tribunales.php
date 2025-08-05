<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\Estudiante;
use App\Models\MiembroCalificacion;
use App\Models\MiembrosTribunal;
use App\Models\Periodo;
use App\Models\PlanEvaluacion;
use App\Models\Tribunale;
use App\Models\TribunalLog;
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

    // Propiedades para el listado de tribunales
    public $keyWord = '';
    //estilos bootstrap para la paginacion
    protected $paginationTheme = 'bootstrap';

    // Propiedades para paginación y ordenamiento
    public $perPage = 10; // Número de elementos por página
    public $sortField = 'fecha'; // Campo por defecto para ordenar
    public $sortDirection = 'desc'; // Dirección por defecto: desc/asc

    // Propiedades para control de acceso contextual
    public $puedeGestionar = false; // Director/Apoyo pueden gestionar
    public $puedeVisualizar = false; // Administradores pueden solo ver

    // Propiedades para el modal de creación de tribunal
    public $selected_id;
    public $estudiante_id;
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $presidente_id;
    public $integrante1_id;
    public $integrante2_id;
    public $descripcion_plantilla;

    // Nuevas propiedades para el flujo de tribunales plantilla
    public $modoPlantilla = false; // true para crear plantilla, false para tribunal individual
    public ?Tribunale $tribunalPlantilla = null; // Tribunal plantilla seleccionado para asignar estudiantes
    public $estudiantesSeleccionados = []; // IDs de estudiantes seleccionados para generar tribunales
    public $showAsignarEstudiantesModal = false;
    public $buscarEstudiante = ''; // Buscador de estudiantes en modal de asignación

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

    /**
     * Actualizar la página cuando cambie el número de elementos por página
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Resetear la página cuando cambie la búsqueda
     */
    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        // Reglas para el modal de CREACIÓN de tribunal
        $rules = [];

        if ($this->modoPlantilla) {
            // Modo plantilla: no requiere estudiante, pero requiere descripción
            $rules = [
                'descripcion_plantilla' => 'required|string|max:255',
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        if (!$this->fecha || !$this->hora_fin) {
                            return;
                        }
                        $this->validarHorariosSolapados($fail);
                    }
                ],
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ];
        } else {
            // Modo normal: requiere estudiante
            $rules = [
                'estudiante_id' => [
                    'required',
                    'exists:estudiantes,id',
                    function ($attribute, $value, $fail) {
                        // Validar que el estudiante no tenga un tribunal individual en esta carrera-período
                        $existeTribunal = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
                            ->where('estudiante_id', $value)
                            ->where('es_plantilla', false)
                            ->when($this->selected_id, function ($query) {
                                // Si estamos editando, excluir el tribunal actual
                                return $query->where('id', '!=', $this->selected_id);
                            })
                            ->exists();

                        if ($existeTribunal) {
                            $fail('Este estudiante ya tiene un tribunal asignado en este período y carrera.');
                        }
                    }
                ],
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        if (!$this->fecha || !$this->hora_fin) {
                            return;
                        }
                        $this->validarHorariosSolapados($fail);
                    }
                ],
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ];
        }

        // Validar que los miembros del tribunal seleccionados estén en la lista filtrada y no sean excluidos
        if ($this->profesoresParaTribunal && $this->profesoresParaTribunal->count() > 0) {
            $validProfessorIdsParaTribunal = $this->profesoresParaTribunal->pluck('id')->implode(',');

            $rules['presidente_id'] = "required|exists:users,id|different:integrante1_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
            $rules['integrante1_id'] = "required|exists:users,id|different:presidente_id|different:integrante2_id|in:{$validProfessorIdsParaTribunal}";
            $rules['integrante2_id'] = "required|exists:users,id|different:presidente_id|different:integrante1_id|in:{$validProfessorIdsParaTribunal}";
        } else {
            // Si no hay profesores disponibles, simplemente requerir que estén seleccionados
            $rules['presidente_id'] = "required|exists:users,id|different:integrante1_id|different:integrante2_id";
            $rules['integrante1_id'] = "required|exists:users,id|different:presidente_id|different:integrante2_id";
            $rules['integrante2_id'] = "required|exists:users,id|different:presidente_id|different:integrante1_id";
        }

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
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
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

        // Verificar acceso contextual al módulo
        $this->verificarAccesoContextual();

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

    /**
     * Verifica el acceso contextual al módulo de tribunales
     */
    protected function verificarAccesoContextual()
    {
        $user = auth()->user();

        // Super Admin tiene acceso total
        if ($user->hasRole('Super Admin')) {
            $this->puedeGestionar = true;
            $this->puedeVisualizar = true;
        }

        // Administrador solo puede visualizar
        if ($user->hasRole('Administrador')) {
            $this->puedeGestionar = false;
            $this->puedeVisualizar = true;
        }

        // Director y Docente de Apoyo de esta carrera-período específica
        if (ContextualAuth::canAccessCarreraPeriodo($user, $this->carreraPeriodoId)) {
            $this->puedeGestionar = true;
            $this->puedeVisualizar = true;
        }

        if ($this->puedeGestionar || $this->puedeVisualizar) {
            return;
        } else {
            // Si no tiene acceso, abortar
            abort(403, 'No tienes permisos para acceder a este módulo de tribunales.');
        }
    }

    protected function loadEstudiantesDisponibles()
    {
        // Obtener estudiantes que ya tienen tribunales individuales en esta carrera-período
        $estudiantesConTribunalIds = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where('es_plantilla', false) // Solo excluir estudiantes con tribunales individuales, no plantillas
            ->whereNotNull('estudiante_id') // Asegurar que el estudiante_id no sea null
            ->pluck('estudiante_id')->toArray();

        // Obtener todos los estudiantes que NO están en la lista de excluidos
        $this->estudiantesDisponibles = Estudiante::whereNotIn('id', $estudiantesConTribunalIds)
            ->orderBy('apellidos')->orderBy('nombres')->get();
    }    protected function loadPlanEvaluacionActivo()
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

        // Obtener tribunales individuales (es_plantilla = false o null)
        $tribunalesIndividuales = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where(function ($query) {
                $query->where('es_plantilla', false)
                    ->orWhereNull('es_plantilla');
            })
            ->with(['estudiante', 'miembrosTribunales.user'])
            ->where(function ($query) use ($keyWord) {
                $query->whereHas('estudiante', function ($q) use ($keyWord) {
                    $q->where('nombres', 'LIKE', $keyWord)
                        ->orWhere('apellidos', 'LIKE', $keyWord);
                })
                    ->orWhere('fecha', 'LIKE', $keyWord);
            })
            ->when($this->sortField === 'estudiante', function($query) {
                // Ordenar por nombre del estudiante
                return $query->join('estudiantes', 'tribunales.estudiante_id', '=', 'estudiantes.id')
                             ->orderBy('estudiantes.nombres', $this->sortDirection)
                             ->orderBy('estudiantes.apellidos', $this->sortDirection)
                             ->orderBy('tribunales.fecha', 'desc') // Ordenación secundaria
                             ->select('tribunales.*');
            }, function($query) {
                // Ordenar por otros campos con ordenación secundaria
                $query->orderBy($this->sortField, $this->sortDirection);

                // Añadir ordenación secundaria según el campo principal
                if ($this->sortField === 'fecha') {
                    $query->orderBy('hora_inicio', 'asc');
                } elseif ($this->sortField === 'hora_inicio') {
                    $query->orderBy('fecha', 'desc');
                } else {
                    $query->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'asc');
                }

                return $query;
            })
            ->paginate($this->perPage);

        // Obtener plantillas de tribunal (es_plantilla = true)
        $plantillasTribunales = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where('es_plantilla', true)
            ->with(['miembrosTribunales.user'])
            ->where(function ($query) use ($keyWord) {
                $query->where('descripcion_plantilla', 'LIKE', $keyWord)
                    ->orWhere('fecha', 'LIKE', $keyWord);
            })
            ->when($this->sortField === 'descripcion_plantilla', function($query) {
                return $query->orderBy('descripcion_plantilla', $this->sortDirection);
            })
            ->when($this->sortField === 'fecha', function($query) {
                return $query->orderBy('fecha', $this->sortDirection)
                             ->orderBy('hora_inicio', 'asc'); // Ordenación secundaria
            })
            ->when($this->sortField === 'hora_inicio', function($query) {
                return $query->orderBy('hora_inicio', $this->sortDirection)
                             ->orderBy('fecha', 'desc'); // Ordenación secundaria
            })
            ->when(!in_array($this->sortField, ['descripcion_plantilla', 'fecha', 'hora_inicio']), function($query) {
                return $query->orderBy('created_at', 'desc'); // Ordenación por defecto
            })
            ->get();

        return view('livewire.tribunales.view', [
            'tribunales' => $tribunalesIndividuales,
            'plantillas' => $plantillasTribunales,
        ]);
    }

    /**
     * Cambiar el ordenamiento de la tabla
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // Si es el mismo campo, cambiar la dirección
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Si es un campo diferente, establecer asc por defecto
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Resetear la paginación al cambiar el ordenamiento
        $this->resetPage();
    }

    /**
     * Obtener el ícono de ordenamiento para una columna
     */
    public function getSortIcon($field)
    {
        if ($this->sortField === $field) {
            return $this->sortDirection === 'asc' ? 'bi-arrow-up text-primary' : 'bi-arrow-down text-primary';
        }
        return 'bi-arrow-up-down text-muted';
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
        $this->descripcion_plantilla = null;
        $this->modoPlantilla = false;
        $this->estudiantesSeleccionados = [];
        $this->tribunalPlantilla = null;
        $this->showAsignarEstudiantesModal = false;
        $this->buscarEstudiante = '';
    }

    // Nuevos métodos para el flujo de tribunales plantilla
    public function updatedModoPlantilla()
    {
        // Limpiar validaciones anteriores
        $this->resetValidation();

        // Limpiar campos específicos según el modo
        if (!$this->modoPlantilla) {
            // Modo individual: limpiar descripción de plantilla
            $this->descripcion_plantilla = null;
        } else {
            // Modo plantilla: limpiar estudiante seleccionado
            $this->estudiante_id = null;
        }
    }

    public function abrirAsignarEstudiantes($tribunalId)
    {
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para gestionar tribunales.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $this->tribunalPlantilla = Tribunale::where('id', $tribunalId)
            ->where('es_plantilla', true)
            ->where('carrera_periodo_id', $this->carreraPeriodoId)
            ->with('miembrosTribunales.user')
            ->first();

        if (!$this->tribunalPlantilla) {
            session()->flash('danger', 'Tribunal plantilla no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $this->estudiantesSeleccionados = [];
        $this->loadEstudiantesDisponibles();
        $this->showAsignarEstudiantesModal = true;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'asignarEstudiantesModal']);
    }

    public function cerrarAsignarEstudiantes()
    {
        $this->showAsignarEstudiantesModal = false;
        $this->estudiantesSeleccionados = [];
        $this->tribunalPlantilla = null;
        $this->buscarEstudiante = '';
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'asignarEstudiantesModal']);
    }

    /**
     * Propiedad computada para filtrar estudiantes según el buscador
     */
    public function getEstudiantesFiltradosProperty()
    {
        if (empty($this->buscarEstudiante)) {
            return $this->estudiantesDisponibles;
        }

        $busqueda = strtolower($this->buscarEstudiante);

        return $this->estudiantesDisponibles->filter(function ($estudiante) use ($busqueda) {
            return str_contains(strtolower($estudiante->nombres), $busqueda) ||
                   str_contains(strtolower($estudiante->apellidos), $busqueda) ||
                   str_contains(strtolower($estudiante->ID_estudiante), $busqueda);
        });
    }

    public function generarTribunalesIndividuales()
    {
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para gestionar tribunales.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if (!$this->tribunalPlantilla || empty($this->estudiantesSeleccionados)) {
            session()->flash('warning', 'Debe seleccionar al menos un estudiante.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Validar que los estudiantes no tengan tribunales asignados
        $estudiantesConTribunales = Tribunale::whereIn('estudiante_id', $this->estudiantesSeleccionados)
            ->where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where('es_plantilla', false)
            ->pluck('estudiante_id')
            ->toArray();

        if (!empty($estudiantesConTribunales)) {
            $estudiantes = Estudiante::whereIn('id', $estudiantesConTribunales)->get();
            $nombresEstudiantes = $estudiantes->map(fn($e) => $e->nombres . ' ' . $e->apellidos)->implode(', ');
            session()->flash('warning', "Los siguientes estudiantes ya tienen tribunales asignados: {$nombresEstudiantes}");
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            DB::transaction(function () {
                $cantidadEstudiantes = count($this->estudiantesSeleccionados);

                // Parsear las horas considerando que pueden venir con segundos (H:i:s) o sin segundos (H:i)
                $horaInicioStr = $this->tribunalPlantilla->hora_inicio;
                $horaFinStr = $this->tribunalPlantilla->hora_fin;

                // Intentar parsear con H:i:s primero, luego con H:i si falla
                try {
                    $horaInicio = \Carbon\Carbon::createFromFormat('H:i:s', $horaInicioStr);
                } catch (\Exception $e) {
                    $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $horaInicioStr);
                }

                try {
                    $horaFin = \Carbon\Carbon::createFromFormat('H:i:s', $horaFinStr);
                } catch (\Exception $e) {
                    $horaFin = \Carbon\Carbon::createFromFormat('H:i', $horaFinStr);
                }

                // Calcular duración total en minutos
                $duracionTotalMinutos = $horaFin->diffInMinutes($horaInicio);

                // Calcular duración por tribunal individual (división igualitaria)
                $duracionPorTribunal = floor($duracionTotalMinutos / $cantidadEstudiantes);

                // Obtener miembros del tribunal plantilla
                $miembrosPlantilla = $this->tribunalPlantilla->miembrosTribunales;

                foreach ($this->estudiantesSeleccionados as $index => $estudianteId) {
                    // Calcular hora de inicio para este tribunal (índice * duración)
                    $horaInicioTribunal = $horaInicio->copy()->addMinutes($index * $duracionPorTribunal);

                    // Calcular hora de fin para este tribunal
                    if ($index === $cantidadEstudiantes - 1) {
                        // Para el último tribunal, usar la hora fin original para aprovechar todo el tiempo
                        $horaFinTribunal = $horaFin->copy();
                    } else {
                        // Para los demás, usar la duración calculada
                        $horaFinTribunal = $horaInicioTribunal->copy()->addMinutes($duracionPorTribunal);
                    }

                    // Crear tribunal individual
                    $tribunalIndividual = Tribunale::create([
                        'carrera_periodo_id' => $this->tribunalPlantilla->carrera_periodo_id,
                        'estudiante_id' => $estudianteId,
                        'fecha' => $this->tribunalPlantilla->fecha,
                        'hora_inicio' => $horaInicioTribunal->format('H:i'),
                        'hora_fin' => $horaFinTribunal->format('H:i'),
                        'estado' => 'ABIERTO',
                        'es_plantilla' => false,
                        'descripcion_plantilla' => null
                    ]);

                    // Crear miembros del tribunal individual
                    foreach ($miembrosPlantilla as $miembro) {
                        MiembrosTribunal::create([
                            'tribunal_id' => $tribunalIndividual->id,
                            'user_id' => $miembro->user_id,
                            'status' => $miembro->status
                        ]);
                    }

                    // Crear log del tribunal
                    TribunalLog::create([
                        'tribunal_id' => $tribunalIndividual->id,
                        'user_id' => auth()->id(),
                        'accion' => 'TRIBUNAL_CREADO',
                        'descripcion' => "Tribunal creado desde plantilla: {$this->tribunalPlantilla->descripcion_plantilla}"
                    ]);
                }

                // Eliminar tribunal plantilla y sus miembros
                $this->tribunalPlantilla->miembrosTribunales()->delete();
                $this->tribunalPlantilla->delete();
            });

            session()->flash('success', "Se generaron exitosamente " . count($this->estudiantesSeleccionados) . " tribunales individuales.");
            $this->cerrarAsignarEstudiantes();
            $this->loadEstudiantesDisponibles();
            $this->dispatchBrowserEvent('showFlashMessage');
        } catch (\Exception $e) {
            session()->flash('danger', 'Error al generar tribunales: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }

    public function store() // Crear Tribunal
    {
        // Verificar permisos contextuales antes de proceder
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para crear tribunales en esta carrera-período.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Usar las reglas del método rules() que ya soporta ambos modos
        $validatedData = $this->validate();

        // Verificar que hay profesores válidos
        $validProfessorIdsParaTribunal = $this->profesoresParaTribunal->pluck('id')->implode(',');
        if (empty($validProfessorIdsParaTribunal)) {
            session()->flash('danger', 'No hay profesores válidos disponibles para formar el tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            DB::transaction(function () use ($validatedData) {
                if ($this->modoPlantilla) {
                    // Crear tribunal plantilla (sin estudiante)
                    $newTribunale = Tribunale::create([
                        'carrera_periodo_id' => $this->carreraPeriodoId,
                        'estudiante_id' => null,
                        'fecha' => $validatedData['fecha'],
                        'hora_inicio' => $validatedData['hora_inicio'],
                        'hora_fin' => $validatedData['hora_fin'],
                        'estado' => 'ABIERTO',
                        'es_plantilla' => true,
                        'descripcion_plantilla' => $validatedData['descripcion_plantilla']
                    ]);

                    $logDescripcion = "Tribunal plantilla creado: {$validatedData['descripcion_plantilla']}";
                } else {
                    // Crear tribunal individual normal
                    $newTribunale = Tribunale::create([
                        'carrera_periodo_id' => $this->carreraPeriodoId,
                        'estudiante_id' => $validatedData['estudiante_id'],
                        'fecha' => $validatedData['fecha'],
                        'hora_inicio' => $validatedData['hora_inicio'],
                        'hora_fin' => $validatedData['hora_fin'],
                        'estado' => 'ABIERTO',
                        'es_plantilla' => false,
                        'descripcion_plantilla' => null
                    ]);

                    $estudiante = Estudiante::find($validatedData['estudiante_id']);
                    $logDescripcion = "Tribunal creado para estudiante: {$estudiante->nombres} {$estudiante->apellidos}";
                }

                // Crear miembros del tribunal
                $newTribunale->miembrosTribunales()->createMany([
                    ['user_id' => $validatedData['presidente_id'], 'status' => 'PRESIDENTE'],
                    ['user_id' => $validatedData['integrante1_id'], 'status' => 'INTEGRANTE1'],
                    ['user_id' => $validatedData['integrante2_id'], 'status' => 'INTEGRANTE2'],
                ]);

                // Crear log del tribunal
                TribunalLog::create([
                    'tribunal_id' => $newTribunale->id,
                    'user_id' => auth()->id(),
                    'accion' => $this->modoPlantilla ? 'PLANTILLA_CREADA' : 'TRIBUNAL_CREADO',
                    'descripcion' => $logDescripcion
                ]);
            });

            $mensaje = $this->modoPlantilla ?
                'Tribunal plantilla creado exitosamente. Ahora puede asignar estudiantes.' :
                'Tribunal creado exitosamente.';

            session()->flash('success', $mensaje);
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
            $this->resetInput();
            $this->loadEstudiantesDisponibles();
            $this->actualizarProfesoresDisponibles();
        } catch (\Exception $e) {
            session()->flash('danger', 'Error al crear tribunal: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }

    // --- MÉTODOS PARA CALIFICADORES GENERALES ---
    public function guardarCalificadoresGenerales()
    {
        // Verificar permisos contextuales para gestionar calificadores
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para gestionar los calificadores generales.');
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
        // Verificar permisos contextuales antes de proceder
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para eliminar tribunales en esta carrera-período.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $tribunal = Tribunale::with('miembrosTribunales')->find($tribunalId);

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar si el tribunal tiene calificaciones (más robusto)
        $tieneCalificaciones = false;
        foreach ($tribunal->miembrosTribunales as $miembro) {
            if ($miembro->tieneCalificaciones()) {
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

    public function cerrarTribunal($tribunalId)
    {
        // Verificar permisos contextuales antes de proceder
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para cambiar el estado de tribunales.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $tribunal = Tribunale::find($tribunalId);

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($tribunal->estado === 'CERRADO') {
            session()->flash('info', 'El tribunal ya está cerrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        DB::transaction(function () use ($tribunal) {
            $tribunal->update(['estado' => 'CERRADO']);

            // Registrar log
            TribunalLog::create([
                'tribunal_id' => $tribunal->id,
                'user_id' => Auth::id(),
                'accion' => 'CIERRE_TRIBUNAL',
                'descripcion' => 'Tribunal cerrado desde lista de tribunales. No se permitirán más modificaciones ni evaluaciones.',
                'datos_antiguos' => ['estado' => 'ABIERTO'],
                'datos_nuevos' => ['estado' => 'CERRADO']
            ]);
        });

        session()->flash('success', 'Tribunal cerrado exitosamente.');
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function abrirTribunal($tribunalId)
    {
        // Verificar permisos contextuales antes de proceder
        if (!$this->puedeGestionar) {
            session()->flash('danger', 'No tienes permisos para cambiar el estado de tribunales.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $tribunal = Tribunale::find($tribunalId);

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($tribunal->estado === 'ABIERTO') {
            session()->flash('info', 'El tribunal ya está abierto.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        DB::transaction(function () use ($tribunal) {
            $tribunal->update(['estado' => 'ABIERTO']);

            // Registrar log
            TribunalLog::create([
                'tribunal_id' => $tribunal->id,
                'user_id' => Auth::id(),
                'accion' => 'APERTURA_TRIBUNAL',
                'descripcion' => 'Tribunal abierto desde lista de tribunales. Se permiten modificaciones y evaluaciones.',
                'datos_antiguos' => ['estado' => 'CERRADO'],
                'datos_nuevos' => ['estado' => 'ABIERTO']
            ]);
        });

        session()->flash('success', 'Tribunal abierto exitosamente.');
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

    /**
     * Valida que el nuevo horario no se solape con tribunales existentes en la misma fecha
     */
    private function validarHorariosSolapados($fail)
    {
        // Convertir las horas a objetos Carbon para comparar fácilmente
        $nuevaHoraInicio = \Carbon\Carbon::createFromFormat('H:i', $this->hora_inicio);
        $nuevaHoraFin = \Carbon\Carbon::createFromFormat('H:i', $this->hora_fin);

        // Buscar tribunales existentes en la misma fecha
        $tribunalesExistentes = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where('fecha', $this->fecha)
            ->when($this->selected_id, function ($query) {
                // Si estamos editando, excluir el tribunal actual
                return $query->where('id', '!=', $this->selected_id);
            })
            ->get(['hora_inicio', 'hora_fin']);

        foreach ($tribunalesExistentes as $tribunal) {
            // Parsear las horas de la base de datos manejando ambos formatos
            try {
                $horaInicioExistente = \Carbon\Carbon::createFromFormat('H:i:s', $tribunal->hora_inicio);
            } catch (\Exception $e) {
                $horaInicioExistente = \Carbon\Carbon::createFromFormat('H:i', $tribunal->hora_inicio);
            }

            try {
                $horaFinExistente = \Carbon\Carbon::createFromFormat('H:i:s', $tribunal->hora_fin);
            } catch (\Exception $e) {
                $horaFinExistente = \Carbon\Carbon::createFromFormat('H:i', $tribunal->hora_fin);
            }

            // Verificar si hay solapamiento
            // Caso 1: El nuevo tribunal inicia antes de que termine el existente Y termina después de que inicia el existente
            if ($nuevaHoraInicio->lt($horaFinExistente) && $nuevaHoraFin->gt($horaInicioExistente)) {
                $fail(sprintf(
                    'El horario seleccionado (%s - %s) se solapa con un tribunal existente (%s - %s) en la fecha %s.',
                    $this->hora_inicio,
                    $this->hora_fin,
                    $horaInicioExistente->format('H:i'), // Usar formato consistente
                    $horaFinExistente->format('H:i'),
                    \Carbon\Carbon::parse($this->fecha)->format('d/m/Y')
                ));
                return;
            }
        }
    }

    /**
     * Validación en tiempo real cuando cambia la hora de inicio
     */
    public function updatedHoraInicio()
    {
        if ($this->fecha && $this->hora_inicio && $this->hora_fin) {
            $this->validarHorarios();
        }
    }

    /**
     * Validación en tiempo real cuando cambia la hora de fin
     */
    public function updatedHoraFin()
    {
        if ($this->fecha && $this->hora_inicio && $this->hora_fin) {
            $this->validarHorarios();
        }
    }

    /**
     * Validación en tiempo real cuando cambia la fecha
     */
    public function updatedFecha()
    {
        if ($this->fecha && $this->hora_inicio && $this->hora_fin) {
            $this->validarHorarios();
        }
    }

    /**
     * Método auxiliar para validar horarios en tiempo real
     */
    private function validarHorarios()
    {
        // Limpiar errores anteriores de horarios
        $this->resetErrorBag(['hora_inicio', 'hora_fin', 'fecha']);

        // Validar formato de horas
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $this->hora_inicio)) {
            $this->addError('hora_inicio', 'La hora de inicio debe tener formato HH:MM.');
            return;
        }

        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $this->hora_fin)) {
            $this->addError('hora_fin', 'La hora de fin debe tener formato HH:MM.');
            return;
        }

        // Validar que hora fin sea mayor que hora inicio
        $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $this->hora_inicio);
        $horaFin = \Carbon\Carbon::createFromFormat('H:i', $this->hora_fin);

        if ($horaFin->lte($horaInicio)) {
            $this->addError('hora_fin', 'La hora de fin debe ser posterior a la hora de inicio.');
            return;
        }

        // Validar solapamiento con otros tribunales
        $this->validarHorariosSolapados(function ($message) {
            $this->addError('hora_inicio', $message);
        });
    }
}
