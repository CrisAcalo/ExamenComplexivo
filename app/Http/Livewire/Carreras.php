<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;
use App\Models\Departamento;

class Carreras extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_carrera, $nombre, $departamento_id, $modalidad, $sede, $founded, $periodos;

    public function render()
    {
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
        $this->codigo_carrera = null;
        $this->nombre = null;
        $this->departamento_id = null;
        $this->modalidad = null;
        $this->sede = null;
    }

    public function store()
    {
        $this->validate([
            'codigo_carrera' => 'required|unique:carreras,codigo_carrera',
            'nombre' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'modalidad' => 'required|in:Presencial,Virtual',
            'sede' => 'required',
        ]);

        Carrera::create([
            'codigo_carrera' => $this->codigo_carrera,
            'nombre' => $this->nombre,
            'departamento_id' => $this->departamento_id,
            'modalidad' => $this->modalidad,
            'sede' => $this->sede
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera Creada Exitosamente.');
    }

    public function edit($id)
    {
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
        $this->validate([
            'codigo_carrera' => 'required|unique:carreras,codigo_carrera,' . $this->selected_id,
            'nombre' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'modalidad' => 'required|in:Presencial,Virtual',
            'sede' => 'required',
        ]);

        if ($this->selected_id) {
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
            session()->flash('success', 'Carrera Actualizada Exitosamente.');
        }
    }

    public function eliminar($id)
    {
        $this->founded = Carrera::find($id);
        if ($this->founded && method_exists($this->founded, 'carrerasPeriodos') && $this->founded->carrerasPeriodos->count() > 0) {
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
