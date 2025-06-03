<?php

namespace App\Http\Livewire\Periodos;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\ComponentesEvaluacion;
use App\Models\Periodo;
use App\Models\User;
use Livewire\Component;

class Profile extends Component
{
    public $keyWord;
    public $periodoId;
    public $periodo;
    public $periodos_carreras;
    public $carreras;
    public $users;
    public $carrera_id, $director_id, $docente_apoyo_id;
    public $nombre_componente, $nombre_ponderacion;
    public $componentes_evaluacion;

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.periodos.profile.profile', [
            'periodos_carreras' => CarrerasPeriodo::where('periodo_id', $this->periodoId)
                ->orWhereHas('carrera', function ($query) use ($keyWord) {
                    $query->where('nombre', 'LIKE', $keyWord);
                })
                ->orWhereHas('director', function ($query) use ($keyWord) {
                    $query->where('name', 'LIKE', $keyWord);
                })
                ->orWhereHas('docenteApoyo', function ($query) use ($keyWord) {
                    $query->where('name', 'LIKE', $keyWord);
                })
                ->paginate(10),
            'periodo' => $this->periodo,
            'periodos_carreras' => $this->periodos_carreras,
            'carreras' => $this->carreras,
            'users' => $this->users,
        ]);
    }

    public function mount()
    {
        $this->periodo = Periodo::find($this->periodoId);
        $this->periodos_carreras = $this->periodo->carrerasPeriodos()->get();
        $this->carreras = Carrera::all();
        $this->users = User::all();
    }

    public function store()
    {
        $this->validate([
            'carrera_id' => 'required',
            'director_id' => 'required',
            'docente_apoyo_id' => 'required',
        ]);
        //validar existencia
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

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera Creada Exitosamente.');
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
    }
}
