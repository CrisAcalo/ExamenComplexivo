<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;

class Carreras extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_carrera, $nombre, $departamento, $sede, $founded, $periodos;

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.carreras.view', [
            'carreras' => Carrera::latest()
                ->orWhere('codigo_carrera', 'LIKE', $keyWord)
                ->orWhere('nombre', 'LIKE', $keyWord)
                ->orWhere('departamento', 'LIKE', $keyWord)
                ->orWhere('sede', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->codigo_carrera = null;
        $this->nombre = null;
        $this->departamento = null;
        $this->sede = null;
    }

    public function store()
    {
        $this->validate([
            'codigo_carrera' => 'required',
            'nombre' => 'required',
            'departamento' => 'required',
            'sede' => 'required',
        ]);

        Carrera::create([
            'codigo_carrera' => $this->codigo_carrera,
            'nombre' => $this->nombre,
            'departamento' => $this->departamento,
            'sede' => $this->sede
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera Creado Exitosamente.');
    }

    public function edit($id)
    {
        $record = Carrera::findOrFail($id);
        $this->selected_id = $id;
        $this->codigo_carrera = $record->codigo_carrera;
        $this->nombre = $record->nombre;
        $this->departamento = $record->departamento;
        $this->sede = $record->sede;
    }

    public function update()
    {
        $this->validate([
            'codigo_carrera' => 'required',
            'nombre' => 'required',
            'departamento' => 'required',
            'sede' => 'required',
        ]);

        if ($this->selected_id) {
            $record = Carrera::find($this->selected_id);
            $record->update([
                'codigo_carrera' => $this->codigo_carrera,
                'nombre' => $this->nombre,
                'departamento' => $this->departamento,
                'sede' => $this->sede
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModal');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Carrera Actualizada Exitosamente.');
        }
    }
    public function eliminar($id)
    {
        $this->founded = Carrera::find($id);
        if ($this->founded->carrerasPeriodos->count() > 0) {
            session()->flash('danger', 'No se puede eliminar la carrera porque tiene periodos asociados.');
            return;
        }
    }

    public function destroy($id)
    {
        if ($id) {
            Carrera::where('id', $id)->delete();
        }
    }
}
