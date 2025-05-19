<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CarrerasPeriodo;

class CarrerasPeriodos extends Component
{
    use WithPagination;

	protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $carrera_id, $periodo_id, $docente_apoyo_id, $director_id;

    public function render()
    {
		$keyWord = '%'.$this->keyWord .'%';
        return view('livewire.carrerasPeriodos.view', [
            'carrerasPeriodos' => CarrerasPeriodo::latest()
						->orWhere('carrera_id', 'LIKE', $keyWord)
						->orWhere('periodo_id', 'LIKE', $keyWord)
						->orWhere('docente_apoyo_id', 'LIKE', $keyWord)
						->orWhere('director_id', 'LIKE', $keyWord)
						->paginate(10),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
		$this->carrera_id = null;
		$this->periodo_id = null;
		$this->docente_apoyo_id = null;
		$this->director_id = null;
    }

    public function store()
    {
        $this->validate([
		'carrera_id' => 'required',
		'periodo_id' => 'required',
		'docente_apoyo_id' => 'required',
		'director_id' => 'required',
        ]);

        CarrerasPeriodo::create([
			'carrera_id' => $this-> carrera_id,
			'periodo_id' => $this-> periodo_id,
			'docente_apoyo_id' => $this-> docente_apoyo_id,
			'director_id' => $this-> director_id
        ]);

        $this->resetInput();
		$this->dispatchBrowserEvent('closeModal');
		session()->flash('message', 'CarrerasPeriodo Successfully created.');
    }

    public function edit($id)
    {
        $record = CarrerasPeriodo::findOrFail($id);
        $this->selected_id = $id;
		$this->carrera_id = $record-> carrera_id;
		$this->periodo_id = $record-> periodo_id;
		$this->docente_apoyo_id = $record-> docente_apoyo_id;
		$this->director_id = $record-> director_id;
    }

    public function update()
    {
        $this->validate([
		'carrera_id' => 'required',
		'periodo_id' => 'required',
		'docente_apoyo_id' => 'required',
		'director_id' => 'required',
        ]);

        if ($this->selected_id) {
			$record = CarrerasPeriodo::find($this->selected_id);
            $record->update([
			'carrera_id' => $this-> carrera_id,
			'periodo_id' => $this-> periodo_id,
			'docente_apoyo_id' => $this-> docente_apoyo_id,
			'director_id' => $this-> director_id
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModal');
			session()->flash('message', 'CarrerasPeriodo Successfully updated.');
        }
    }

    public function destroy($id)
    {
        if ($id) {
            CarrerasPeriodo::where('id', $id)->delete();
        }
    }
}
