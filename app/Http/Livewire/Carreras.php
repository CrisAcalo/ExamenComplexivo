<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;
use App\Models\Departamento;
use Illuminate\Support\Facades\Gate;

class Carreras extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_carrera, $nombre, $departamento_id, $modalidad, $sede, $founded, $periodos;

    public function mount()
    {
        $this->verificarAccesoCarreras();
    }

    /**
     * Verificar acceso a la gestión de carreras
     */
    private function verificarAccesoCarreras()
    {
        if (!Gate::allows('gestionar carreras')) {
            abort(403, 'No tienes permisos para acceder a la gestión de carreras.');
        }
    }

    /**
     * Verificar si el usuario puede gestionar carreras
     */
    private function puedeGestionarCarreras()
    {
        return Gate::allows('gestionar carreras');
    }

    public function render()
    {
        // Verificar acceso al renderizar
        $this->verificarAccesoCarreras();

        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.carreras.view', [
            'carreras' => Carrera::with('departamento')
                ->where('codigo_carrera', 'LIKE', $keyWord)
                ->orWhere('nombre', 'LIKE', $keyWord)
                ->orWhereHas('departamento', function($q) use ($keyWord) {
                    $q->where('nombre', 'LIKE', $keyWord);
                })
                ->orWhere('sede', 'LIKE', $keyWord)
                ->latest()
                ->paginate(10),
            'departamentos' => Departamento::all(),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->selected_id = null;
        $this->codigo_carrera = null;
        $this->nombre = null;
        $this->departamento_id = null;
        $this->modalidad = null;
        $this->sede = null;
        $this->founded = null;
    }

    public function store()
    {
        // Verificar permisos
        if (!$this->puedeGestionarCarreras()) {
            session()->flash('error', 'No tienes permisos para crear carreras.');
            return;
        }

        $this->validate([
            'codigo_carrera' => 'required|unique:carreras,codigo_carrera',
            'nombre' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'modalidad' => 'required|in:Presencial,En línea',
            'sede' => 'required',
        ]);

        try {
            Carrera::create([
                'codigo_carrera' => $this->codigo_carrera,
                'nombre' => $this->nombre,
                'departamento_id' => $this->departamento_id,
                'modalidad' => $this->modalidad,
                'sede' => $this->sede
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
            session()->flash('success', 'Carrera creada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la carrera: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarCarreras()) {
            session()->flash('error', 'No tienes permisos para editar carreras.');
            return;
        }

        $record = Carrera::findOrFail($id);
        $this->selected_id = $id;
        $this->codigo_carrera = $record->codigo_carrera;
        $this->nombre = $record->nombre;
        $this->departamento_id = $record->departamento_id;
        $this->modalidad = $record->modalidad;
        $this->sede = $record->sede;
    }

    public function update()
    {
        // Verificar permisos
        if (!$this->puedeGestionarCarreras()) {
            session()->flash('error', 'No tienes permisos para actualizar carreras.');
            return;
        }

        $this->validate([
            'codigo_carrera' => 'required|unique:carreras,codigo_carrera,' . $this->selected_id,
            'nombre' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'modalidad' => 'required|in:Presencial,En línea',
            'sede' => 'required',
        ]);

        if ($this->selected_id) {
            try {
                $record = Carrera::find($this->selected_id);
                $record->update([
                    'codigo_carrera' => $this->codigo_carrera,
                    'nombre' => $this->nombre,
                    'departamento_id' => $this->departamento_id,
                    'modalidad' => $this->modalidad,
                    'sede' => $this->sede
                ]);

                $this->resetInput();
                $this->dispatchBrowserEvent('closeModal');
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
                session()->flash('success', 'Carrera actualizada exitosamente.');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al actualizar la carrera: ' . $e->getMessage());
            }
        }
    }

    public function eliminar($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarCarreras()) {
            session()->flash('error', 'No tienes permisos para eliminar carreras.');
            return;
        }

        $this->selected_id = $id;
        $this->founded = Carrera::find($id);
        if ($this->founded && method_exists($this->founded, 'carrerasPeriodos') && $this->founded->carrerasPeriodos->count() > 0) {
            session()->flash('error', 'No se puede eliminar la carrera porque tiene períodos asociados.');
            return;
        }
    }

    public function destroy()
    {
        // Verificar permisos
        if (!$this->puedeGestionarCarreras()) {
            session()->flash('error', 'No tienes permisos para eliminar carreras.');
            return;
        }

        if ($this->selected_id) {
            try {
                $carrera = Carrera::find($this->selected_id);

                // Verificar si tiene períodos asociados
                if ($carrera && method_exists($carrera, 'carrerasPeriodos') && $carrera->carrerasPeriodos->count() > 0) {
                    session()->flash('error', 'No se puede eliminar la carrera porque tiene períodos asociados.');
                    return;
                }

                Carrera::where('id', $this->selected_id)->delete();
                session()->flash('success', 'Carrera eliminada exitosamente.');
                $this->founded = null;
                $this->selected_id = null;
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            } catch (\Exception $e) {
                session()->flash('error', 'Error al eliminar la carrera: ' . $e->getMessage());
            }
        }
    }
}
