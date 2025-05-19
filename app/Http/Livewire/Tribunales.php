<?php

namespace App\Http\Livewire;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\ComponenteRubrica;
use App\Models\ComponentesEvaluacion;
use App\Models\Estudiante;
use App\Models\Periodo;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tribunale;
use App\Models\User;

class Tribunales extends Component
{
    use WithPagination;
    public $carreraPeriodoId;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $carrera_periodo_id, $estudiante_id, $fecha, $hora_inicio, $hora_fin;
    public $profesores;
    public $estudiantes;
    public $carreraPeriodo, $carrera, $periodo;
    public $presidente_id, $integrante1_id, $integrante2_id;
    public $nombre_componente, $ponderacion_componente;
    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.tribunales.view', [
            'tribunales' => Tribunale::latest()
                ->orWhere('carrera_periodo_id', 'LIKE', $keyWord)
                ->orWhere('estudiante_id', 'LIKE', $keyWord)
                ->orWhere('fecha', 'LIKE', $keyWord)
                ->orWhere('hora_inicio', 'LIKE', $keyWord)
                ->orWhere('hora_fin', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }
    public function mount($carreraPeriodoId)
    {
        $this->profesores = User::all();
        $this->estudiantes = Estudiante::all();
        $this->carreraPeriodo = CarrerasPeriodo::find($carreraPeriodoId);
        $this->carrera = Carrera::find($this->carreraPeriodo->carrera_id);
        $this->periodo = Periodo::find($this->carreraPeriodo->periodo_id);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->carrera_periodo_id = null;
        $this->estudiante_id = null;
        $this->fecha = null;
        $this->hora_inicio = null;
        $this->hora_fin = null;
    }

    public function store()
    {
        $this->validate([
            'estudiante_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ]);

        $newTribunale = new Tribunale();
        $newTribunale->carrera_periodo_id = $this->carreraPeriodoId;
        $newTribunale->estudiante_id = $this->estudiante_id;
        $newTribunale->fecha = $this->fecha;
        $newTribunale->hora_inicio = $this->hora_inicio;
        $newTribunale->hora_fin = $this->hora_fin;
        $newTribunale->save();

        //Crear Miembros del tribunal
        $newTribunale->miembrosTribunales()->create([
            'tribunal_id' => $newTribunale->id,
            'user_id' => $this->presidente_id,
            'status' => 'PRESIDENTE'
        ]);
        $newTribunale->miembrosTribunales()->create([
            'tribunal_id' => $newTribunale->id,
            'user_id' => $this->integrante1_id,
            'status' => 'INTEGRANTE1'
        ]);
        $newTribunale->miembrosTribunales()->create([
            'tribunal_id' => $newTribunale->id,
            'user_id' => $this->integrante2_id,
            'status' => 'INTEGRANTE2'
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Tribunal Creado Exitosamente.');
    }


    public function storeComponente()
    {
        $this->validate([
            'nombre_componente' => 'required',
            'ponderacion_componente' => 'required',
        ]);

        ComponenteRubrica::create([
            'carrera_periodo_id' => $this->carreraPeriodoId,
            'nombre' => $this->nombre_componente,
            'ponderacion' => $this->ponderacion_componente,
        ]);

        $this->resetInputComponente();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera Creada Exitosamente.');
    }

    public function edit($id)
    {
        $record = Tribunale::findOrFail($id);
        $this->selected_id = $id;
        $this->carrera_periodo_id = $record->carrera_periodo_id;
        $this->estudiante_id = $record->estudiante_id;
        $this->fecha = $record->fecha;
        $this->hora_inicio = $record->hora_inicio;
        $this->hora_fin = $record->hora_fin;
    }

    public function update()
    {
        $this->validate([
            'carrera_periodo_id' => 'required',
            'estudiante_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ]);

        if ($this->selected_id) {
            $record = Tribunale::find($this->selected_id);
            $record->update([
                'carrera_periodo_id' => $this->carrera_periodo_id,
                'estudiante_id' => $this->estudiante_id,
                'fecha' => $this->fecha,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Tribunal Actualizado Exitosamente.');
        }
    }

    public function destroy($id)
    {
        if ($id) {
            Tribunale::where('id', $id)->delete();
        }
    }

    public function resetInputComponente()
    {
        $this->nombre_componente = null;
        $this->ponderacion_componente = null;
    }
}
