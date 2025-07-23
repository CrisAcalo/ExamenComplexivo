<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Periodo;
use App\Helpers\ContextualAuth;
use Illuminate\Support\Facades\Gate;

class Periodos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_periodo, $descripcion, $fecha_inicio, $fecha_fin, $founded, $periodos_carreras;
    public $periodoAEliminarId;
    public $confirmingPeriodoDeletion = false;

    public function mount()
    {
        $this->verificarAccesoPeriodos();
    }

    private function verificarAccesoPeriodos()
    {
        $user = auth()->user();
        
        // Super Admin y Administrador tienen acceso global
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return;
        }

        // Director de Carrera: verificar con ContextualAuth si tiene asignaciones
        $hasDirectorAssignments = ContextualAuth::getCarrerasAsDirector($user)->isNotEmpty();
        
        // Docente de Apoyo: verificar con ContextualAuth si tiene asignaciones
        $hasApoyoAssignments = ContextualAuth::getCarrerasAsApoyo($user)->isNotEmpty();
        
        if (!$hasDirectorAssignments && !$hasApoyoAssignments) {
            session()->flash('error', 'No tienes asignaciones como director o docente de apoyo en ningún período.');
            abort(403);
        }
    }    public function puedeGestionarPeriodos()
    {
        $user = auth()->user();
        // Solo Super Admin y Administrador pueden gestionar (crear/editar/eliminar)
        return $user->hasRole(['Super Admin', 'Administrador']) &&
               $user->hasPermissionTo('gestionar periodos');
    }

    public function puedeVerPeriodos()
    {
        $user = auth()->user();

        // Super Admin y Administrador pueden ver todos
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return true;
        }

        // Director o Docente de Apoyo: verificar asignaciones con ContextualAuth
        $hasDirectorAssignments = ContextualAuth::getCarrerasAsDirector($user)->isNotEmpty();
        $hasApoyoAssignments = ContextualAuth::getCarrerasAsApoyo($user)->isNotEmpty();
        
        return $hasDirectorAssignments || $hasApoyoAssignments;
    }

    public function render()
    {
        if (!$this->puedeVerPeriodos()) {
            session()->flash('error', 'No tienes permisos para ver los períodos.');
            abort(403);
        }

        $user = auth()->user();
        $keyWord = '%' . $this->keyWord . '%';

        // Super Admin y Administrador ven todos los períodos
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            $periodos = Periodo::latest()
                ->where('codigo_periodo', 'LIKE', $keyWord)
                ->orWhere('descripcion', 'LIKE', $keyWord)
                ->orWhere('fecha_inicio', 'LIKE', $keyWord)
                ->orWhere('fecha_fin', 'LIKE', $keyWord)
                ->paginate(10);
        } 
        // Director o Docente de Apoyo: solo ven períodos donde tienen asignaciones
        else {
            // Obtener períodos como Director
            $periodosDirector = ContextualAuth::getCarrerasAsDirector($user)
                ->pluck('periodo_id')
                ->unique();
                
            // Obtener períodos como Docente de Apoyo
            $periodosApoyo = ContextualAuth::getCarrerasAsApoyo($user)
                ->pluck('periodo_id')
                ->unique();
            
            // Combinar ambos conjuntos
            $periodosIds = $periodosDirector->merge($periodosApoyo)->unique();

            $periodos = Periodo::whereIn('id', $periodosIds)
                ->latest()
                ->where(function($query) use ($keyWord) {
                    $query->where('codigo_periodo', 'LIKE', $keyWord)
                        ->orWhere('descripcion', 'LIKE', $keyWord)
                        ->orWhere('fecha_inicio', 'LIKE', $keyWord)
                        ->orWhere('fecha_fin', 'LIKE', $keyWord);
                })
                ->paginate(10);
        }        return view('livewire.periodos.view', [
            'periodos' => $periodos,
        ]);
    }

    public function open($periodoID)
    {
        $user = auth()->user();
        
        // Verificar si puede acceder al período específico
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return redirect()->route('periodos.profile', $periodoID);
        }
        
        // Director o Docente de Apoyo: verificar acceso contextual
        $canAccessAsDirector = ContextualAuth::getCarrerasAsDirector($user)
            ->where('periodo_id', $periodoID)
            ->isNotEmpty();
            
        $canAccessAsApoyo = ContextualAuth::getCarrerasAsApoyo($user)
            ->where('periodo_id', $periodoID)
            ->isNotEmpty();
        
        if ($canAccessAsDirector || $canAccessAsApoyo) {
            return redirect()->route('periodos.profile', $periodoID);
        }

        session()->flash('error', 'No tienes acceso a este período.');
    }    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->selected_id = null;
        $this->codigo_periodo = null;
        $this->descripcion = null;
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
    }

    public function store()
    {
        if (!$this->puedeGestionarPeriodos()) {
            session()->flash('error', 'No tienes permisos para crear períodos.');
            return;
        }

        $this->validate([
            'codigo_periodo' => 'required|string|max:20|unique:periodos,codigo_periodo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $this->descripcion = $this->generarDescripcion();

        Periodo::create([
            'codigo_periodo' => $this->codigo_periodo,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Periodo Creado Exitosamente.');
    }

    public function edit($id)
    {
        if (!$this->puedeGestionarPeriodos()) {
            session()->flash('error', 'No tienes permisos para editar períodos.');
            return;
        }

        $record = Periodo::findOrFail($id);
        $this->selected_id = $id;
        $this->codigo_periodo = $record->codigo_periodo;
        $this->descripcion = $record->descripcion;
        $this->fecha_inicio = $record->fecha_inicio;
        $this->fecha_fin = $record->fecha_fin;
    }

    public function update()
    {
        if (!$this->puedeGestionarPeriodos()) {
            session()->flash('error', 'No tienes permisos para actualizar períodos.');
            return;
        }

        $this->validate([
            'codigo_periodo' => 'required|string|max:20|unique:periodos,codigo_periodo,' . $this->selected_id,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($this->selected_id) {
            $this->descripcion = $this->generarDescripcion();

            $record = Periodo::find($this->selected_id);
            $record->update([
                'codigo_periodo' => $this->codigo_periodo,
                'descripcion' => $this->descripcion,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Periodo Actualizado Exitosamente.');
        }
    }

    public function eliminar($id) // Renombrar a confirmDelete para más claridad
    {
        if (!$this->puedeGestionarPeriodos()) {
            session()->flash('error', 'No tienes permisos para eliminar períodos.');
            return;
        }

        $periodo = Periodo::find($id);

        if (!$periodo) {
            session()->flash('danger', 'Periodo no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage'); // Usar un listener JS si tienes alertas flotantes
            return;
        }

        if ($periodo->carrerasPeriodos()->exists()) { // Usar exists() es más eficiente que count()
            session()->flash('warning', 'No se puede eliminar el periodo porque tiene carreras asociadas.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Si pasa las validaciones, prepara el modal
        $this->periodoAEliminarId = $id;
        $this->confirmingPeriodoDeletion = true;
        // El modal se abre por data-bs-toggle en el botón, no necesitamos un evento JS aquí.
    }

    public function destroy()
    {
        if (!$this->puedeGestionarPeriodos()) {
            session()->flash('error', 'No tienes permisos para eliminar períodos.');
            return;
        }

        if (!$this->periodoAEliminarId) {
            // No debería ocurrir si el flujo es correcto
            return;
        }

        // Volver a verificar por si acaso algo cambió entre la confirmación y la acción
        $periodo = Periodo::find($this->periodoAEliminarId);
        if ($periodo && $periodo->carrerasPeriodos()->exists()) {
            session()->flash('danger', 'Acción cancelada: El periodo ahora tiene carreras asociadas.');
            $this->dispatchBrowserEvent('showFlashMessage');
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            return;
        }

        try {
            Periodo::destroy($this->periodoAEliminarId); // Usar destroy(id) es más directo
            session()->flash('success', 'Periodo eliminado con éxito.');
        } catch (\Exception $e) {
            session()->flash('danger', 'Ocurrió un error al eliminar el periodo.');
        }

        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);

        // Si estás en una página del paginador que ya no existe, ve a la primera página.
        if (Periodo::paginate(10)->currentPage() > Periodo::paginate(10)->lastPage()) {
            $this->resetPage();
        }
    }

    public function resetDeleteConfirmation()
    {
        $this->periodoAEliminarId = null;
        $this->confirmingPeriodoDeletion = false;
    }

    public function generarDescripcion()
    {
        $meses = [
            '01' => 'ENE',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'ABR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGO',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DIC'
        ];

        $fecha_inicio = \Carbon\Carbon::parse($this->fecha_inicio);
        $fecha_fin = \Carbon\Carbon::parse($this->fecha_fin);
        $mes_inicio = $fecha_inicio->format('m');
        $mes_fin = $fecha_fin->format('m');
        $anio_inicio = $fecha_inicio->format('y');
        $anio_fin = $fecha_fin->format('y');
        $mes_inicio_nombre = $meses[$mes_inicio];
        $mes_fin_nombre = $meses[$mes_fin];

        // descripcion: MAY-SEP25 o OCT21-MAR22
        if ($anio_inicio == $anio_fin) {
            $descripcion = $mes_inicio_nombre . '-' . $mes_fin_nombre . $anio_inicio;
        } else {
            $descripcion = $mes_inicio_nombre . $anio_inicio . '-' . $mes_fin_nombre . $anio_fin;
        }

        return $descripcion;
    }

}
