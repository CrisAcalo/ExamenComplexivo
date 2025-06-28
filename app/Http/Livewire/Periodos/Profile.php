<?php

namespace App\Http\Livewire\Periodos;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
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
    public $selected_id;
    public $founded;

    public function mount($periodoId)
    {
        $this->periodoId = $periodoId;
        $this->periodo = Periodo::find($this->periodoId);
        $this->refreshCarrerasPeriodos();
        $this->carreras = Carrera::all();
        $this->users = User::all();
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.periodos.profile.profile', [
            'periodos_carreras' => CarrerasPeriodo::with(['carrera', 'director', 'docenteApoyo'])
                ->where('periodo_id', $this->periodoId)
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
                ->paginate(10),
            'periodo' => $this->periodo,
            'carreras' => $this->carreras,
            'users' => $this->users,
        ]);
    }

    public function store()
    {
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

        $this->resetInput();
        $this->refreshCarrerasPeriodos();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Carrera asignada al periodo exitosamente.');
    }

    public function edit($id)
    {
        $record = CarrerasPeriodo::findOrFail($id);
        $this->selected_id = $id;
        $this->carrera_id = $record->carrera_id;
        $this->director_id = $record->director_id;
        $this->docente_apoyo_id = $record->docente_apoyo_id;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'updateDataModal']);
    }

    public function update()
    {
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
            $this->resetInput();
            $this->refreshCarrerasPeriodos();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Asignación actualizada exitosamente.');
        }
    }

    public function eliminar($id)
    {
        $this->founded = CarrerasPeriodo::find($id);
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteDataModal']);
    }

    public function destroy($id)
    {
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
    }

    private function refreshCarrerasPeriodos()
    {
        $this->periodos_carreras = CarrerasPeriodo::with(['carrera', 'director', 'docenteApoyo'])
            ->where('periodo_id', $this->periodoId)
            ->get();
    }
}
