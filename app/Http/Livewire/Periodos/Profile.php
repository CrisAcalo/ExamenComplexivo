<?php

namespace App\Http\Livewire\Periodos;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\Periodo;
use App\Models\User;
use App\Helpers\ContextualAuth;
use Livewire\Component;

class Profile extends Component
{
    public $keyWord;
    public $periodoId;
    public $periodo;
    public $periodos_carreras;
    public $carreras;
    public $users;
    public $users_filtrados; // Usuarios filtrados por departamento de la carrera seleccionada
    public $carrera_id, $director_id, $docente_apoyo_id;
    public $selected_id;
    public $founded;
    public $mostrar_todos_docentes = false; // Controla si se muestran todos los docentes o solo del departamento

    public function mount($periodoId)
    {
        $rolesExcluidosEdicion = ['Super Admin'];
        $this->periodoId = $periodoId;
        $this->periodo = Periodo::find($this->periodoId);

        $this->verificarAccesoAlPeriodo();

        $this->refreshCarrerasPeriodos();
        $this->carreras = Carrera::orderBy('nombre')->get();
        // $this->users = User::orderBy('name')->get();
        $this->users = User::whereDoesntHave('roles', function ($query) use ($rolesExcluidosEdicion) {
            $query->whereIn('name', $rolesExcluidosEdicion);
        })
            ->orderBy('name')->get();

        // Inicializar usuarios filtrados vacío
        $this->users_filtrados = collect();
    }

    /**
     * Filtrar usuarios por departamento de la carrera
     */
    private function filtrarUsuariosPorCarrera($carreraId)
    {
        if ($carreraId) {
            $carrera = Carrera::find($carreraId);

            // Si se activó "mostrar todos los docentes", mostrar todos sin filtrar
            if ($this->mostrar_todos_docentes) {
                $this->users_filtrados = $this->users;
            } elseif ($carrera && $carrera->departamento_id) {
                // Filtrar usuarios que pertenecen al mismo departamento que la carrera
                $rolesExcluidosEdicion = ['Super Admin'];
                $this->users_filtrados = User::where('departamento_id', $carrera->departamento_id)
                    ->whereDoesntHave('roles', function ($query) use ($rolesExcluidosEdicion) {
                        $query->whereIn('name', $rolesExcluidosEdicion);
                    })
                    ->orderBy('name')
                    ->get();
            } else {
                // Si la carrera no tiene departamento, mostrar todos los docentes
                $this->users_filtrados = $this->users;
            }
        } else {
            // Si no hay carrera seleccionada, vaciar usuarios filtrados
            $this->users_filtrados = collect();
        }
    }

    /**
     * Ejecutar cuando cambia la carrera seleccionada
     * Filtrar usuarios por departamento de la carrera
     */
    public function updatedCarreraId($value)
    {
        $this->filtrarUsuariosPorCarrera($value);

        // Resetear director y apoyo al cambiar carrera
        $this->director_id = null;
        $this->docente_apoyo_id = null;
    }

    /**
     * Ejecutar cuando cambia el checkbox de "Mostrar todos los docentes"
     * Actualizar la lista de usuarios filtrados
     */
    public function updatedMostrarTodosDocentes()
    {
        // Refiltrar usuarios con la nueva configuración
        if ($this->carrera_id) {
            $this->filtrarUsuariosPorCarrera($this->carrera_id);
        }
    }

    private function verificarAccesoAlPeriodo()
    {
        $user = auth()->user();

        // Super Admin tiene acceso global
        if ($user->hasRole('Super Admin')) {
            return;
        }

        // Director o Docente de Apoyo: verificar acceso contextual
        $canAccessAsDirector = ContextualAuth::getCarrerasAsDirector($user)
            ->where('periodo_id', $this->periodoId)
            ->isNotEmpty();

        $canAccessAsApoyo = ContextualAuth::getCarrerasAsApoyo($user)
            ->where('periodo_id', $this->periodoId)
            ->isNotEmpty();

        if (!$canAccessAsDirector && !$canAccessAsApoyo) {
            session()->flash('error', 'No tienes acceso a este período.');
            abort(403);
        }
    }

    public function puedeGestionarCarrerasPeriodos()
    {
        $user = auth()->user();
        // Solo Super Admin puede gestionar carreras-períodos
        return $user->hasRole('Super Admin') &&
               $user->hasPermissionTo('asignar carrera a periodo');
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';

        // Usar el mismo filtro que refreshCarrerasPeriodos para consistencia
        $periodos_carreras = $this->getFilteredCarrerasPeriodos()
            ->when($this->keyWord, function ($query) use ($keyWord) {
                $query->whereHas('carrera', function ($q) use ($keyWord) {
                    $q->where('nombre', 'LIKE', $keyWord);
                })
                ->orWhereHas('director', function ($q) use ($keyWord) {
                    $q->where('name', 'LIKE', $keyWord);
                })
                ->orWhereHas('docenteApoyo', function ($q) use ($keyWord) {
                    $q->where('name', 'LIKE', $keyWord);
                });
            })
            ->paginate(10);

        return view('livewire.periodos.profile.profile', [
            'periodos_carreras' => $periodos_carreras,
            'periodo' => $this->periodo,
            'carreras' => $this->carreras,
            'users' => $this->users,
        ]);
    }

    /**
     * Obtiene las carreras-períodos filtradas según el rol del usuario
     */
    private function getFilteredCarrerasPeriodos()
    {
        $user = auth()->user();

        $query = CarrerasPeriodo::with(['carrera', 'director', 'docenteApoyo'])
            ->where('periodo_id', $this->periodoId);

        // Super Admin ve todas las carreras-períodos de este período
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        // Director o Docente de Apoyo: solo ven las carreras-períodos donde tienen asignación
        return $query->where(function($subQuery) use ($user) {
            $subQuery->where('director_id', $user->id)
                     ->orWhere('docente_apoyo_id', $user->id);
        });
    }

    public function store()
    {
        if (!$this->puedeGestionarCarrerasPeriodos()) {
            session()->flash('error', 'No tienes permisos para crear asignaciones carrera-período.');
            return;
        }

        $this->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'director_id' => 'required|exists:users,id|different:docente_apoyo_id',
            'docente_apoyo_id' => 'required|exists:users,id|different:director_id',
        ]);

        $exists = CarrerasPeriodo::where('periodo_id', $this->periodoId)
            ->where('carrera_id', $this->carrera_id)
            ->exists();
        if ($exists) {
            session()->flash('danger', 'La carrera ya está asignada a este periodo.');
            return;
        }

        CarrerasPeriodo::create([
            'periodo_id' => $this->periodoId,
            'carrera_id' => $this->carrera_id,
            'director_id' => $this->director_id,
            'docente_apoyo_id' => $this->docente_apoyo_id,
        ]);

        // Asignar roles globales automáticamente
        $director = User::find($this->director_id);
        if ($director && !$director->hasRole('Director de Carrera')) {
            $director->assignRole('Director de Carrera');
        }

        $docenteApoyo = User::find($this->docente_apoyo_id);
        if ($docenteApoyo && !$docenteApoyo->hasRole('Docente de Apoyo')) {
            $docenteApoyo->assignRole('Docente de Apoyo');
        }

        $this->resetInput();
        $this->refreshCarrerasPeriodos();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera asignada al periodo exitosamente.');
    }

    public function edit($id)
    {
        if (!$this->puedeGestionarCarrerasPeriodos()) {
            session()->flash('error', 'No tienes permisos para editar asignaciones carrera-período.');
            return;
        }

        $record = CarrerasPeriodo::findOrFail($id);
        $this->selected_id = $id;
        $this->carrera_id = $record->carrera_id;

        // Cargar los usuarios filtrados del departamento de la carrera
        $this->filtrarUsuariosPorCarrera($this->carrera_id);

        // Después de filtrar los usuarios, establecer director y docente de apoyo
        $this->director_id = $record->director_id;
        $this->docente_apoyo_id = $record->docente_apoyo_id;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'updateDataModal']);
    }

    public function update()
    {
        if (!$this->puedeGestionarCarrerasPeriodos()) {
            session()->flash('error', 'No tienes permisos para actualizar asignaciones carrera-período.');
            return;
        }

        $this->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'director_id' => 'required|exists:users,id|different:docente_apoyo_id',
            'docente_apoyo_id' => 'required|exists:users,id|different:director_id',
        ]);

        if ($this->selected_id) {
            $exists = CarrerasPeriodo::where('periodo_id', $this->periodoId)
                ->where('carrera_id', $this->carrera_id)
                ->where('id', '!=', $this->selected_id)
                ->exists();
            if ($exists) {
                session()->flash('danger', 'La carrera ya está asignada a este periodo.');
                return;
            }

            $record = CarrerasPeriodo::find($this->selected_id);
            $record->update([
                'carrera_id' => $this->carrera_id,
                'director_id' => $this->director_id,
                'docente_apoyo_id' => $this->docente_apoyo_id,
            ]);

            // Asignar roles globales automáticamente
            $director = User::find($this->director_id);
            if ($director && !$director->hasRole('Director de Carrera')) {
                $director->assignRole('Director de Carrera');
            }

            $docenteApoyo = User::find($this->docente_apoyo_id);
            if ($docenteApoyo && !$docenteApoyo->hasRole('Docente de Apoyo')) {
                $docenteApoyo->assignRole('Docente de Apoyo');
            }

            $this->resetInput();
            $this->refreshCarrerasPeriodos();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Asignación actualizada exitosamente.');
        }
    }

    public function eliminar($id)
    {
        if (!$this->puedeGestionarCarrerasPeriodos()) {
            session()->flash('error', 'No tienes permisos para eliminar asignaciones carrera-período.');
            return;
        }

        $this->founded = CarrerasPeriodo::find($id);
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteDataModal']);
    }

    public function destroy($id)
    {
        if (!$this->puedeGestionarCarrerasPeriodos()) {
            session()->flash('error', 'No tienes permisos para eliminar asignaciones carrera-período.');
            return;
        }

        if ($id) {
            CarrerasPeriodo::where('id', $id)->delete();
            $this->resetInput();
            $this->refreshCarrerasPeriodos();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('success', 'Asignación eliminada exitosamente.');
        }
    }

    public function cancel()
    {
        $this->resetInput();
    }

    public function resetInput()
    {
        $this->carrera_id = null;
        $this->director_id = null;
        $this->docente_apoyo_id = null;
        $this->selected_id = null;
        $this->founded = null;
        $this->mostrar_todos_docentes = false; // Resetear el filtro al cerrar el modal
    }

    private function refreshCarrerasPeriodos()
    {
        // Usar el método centralizado para obtener las carreras-períodos filtradas
        $this->periodos_carreras = $this->getFilteredCarrerasPeriodos()->get();
    }
}
