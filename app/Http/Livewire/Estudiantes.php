<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Estudiante;

class Estudiantes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $nombres, $apellidos, $ID_estudiante, $founded;

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.estudiantes.view', [
            'estudiantes' => Estudiante::latest()
                ->orWhere('nombres', 'LIKE', $keyWord)
                ->orWhere('apellidos', 'LIKE', $keyWord)
                ->orWhere('ID_estudiante', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->nombres = null;
        $this->apellidos = null;
        $this->ID_estudiante = null;
    }

    public function store()
    {
        $this->validate([
            'nombres' => 'required',
            'apellidos' => 'required',
            'ID_estudiante' => 'required',
        ]);

        Estudiante::create([
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'ID_estudiante' => $this->ID_estudiante
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Periodo Creado Exitosamente.');
    }

    public function edit($id)
    {
        $record = Estudiante::findOrFail($id);
        $this->selected_id = $id;
        $this->nombres = $record->nombres;
        $this->apellidos = $record->apellidos;
        $this->ID_estudiante = $record->ID_estudiante;
    }

    public function update()
    {
        $this->validate([
            'nombres' => 'required',
            'apellidos' => 'required',
            'ID_estudiante' => 'required',
        ]);

        if ($this->selected_id) {
            $record = Estudiante::find($this->selected_id);
            $record->update([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'ID_estudiante' => $this->ID_estudiante
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Periodo Actualizado Exitosamente.');
        }
    }
    public function eliminar($id)
    {
        $this->founded = Estudiante::find($id);
        if ($this->founded->tribunales->count() > 0) {
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('danger', 'No se puede eliminar el estudiante porque tiene tribunales asociados.');
            return;
        }
    }
    public function destroy($id)
    {
        if ($id) {
            Estudiante::where('id', $id)->delete();
        }
    }
}
