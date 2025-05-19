<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Periodo;

class Periodos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_periodo, $fecha_inicio, $fecha_fin, $founded, $periodos_carreras;

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.periodos.view', [
            'periodos' => Periodo::latest()
                ->orWhere('codigo_periodo', 'LIKE', $keyWord)
                ->orWhere('fecha_inicio', 'LIKE', $keyWord)
                ->orWhere('fecha_fin', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }
    public function open($periodoID){
        //redirigir a la vista de detalle de periodo
        return redirect()->route('periodos.profile', $periodoID);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->codigo_periodo = null;
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
    }

    public function store()
    {
        $this->validate([
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
        ]);

        //autogenerar el codigo_periodo con este formato de ejemplo: MAYO-SEPT22 (meses en el mismo año), OCT21-MAR22 (meses en diferentes años), ETC
        $this->codigo_periodo = $this->determinarCodigo();

        // Verificar si el periodo ya existe
        $periodoExistente = Periodo::where('codigo_periodo', $this->codigo_periodo)->first();
        if ($periodoExistente) {
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
            session()->flash('danger', 'El periodo ya existe.');
            return;
        }

        Periodo::create([
            'codigo_periodo' => $this->codigo_periodo,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Periodo Creado Exitosamente.');
    }

    public function edit($id)
    {
        $record = Periodo::findOrFail($id);
        $this->selected_id = $id;
        $this->codigo_periodo = $record->codigo_periodo;
        $this->fecha_inicio = $record->fecha_inicio;
        $this->fecha_fin = $record->fecha_fin;
    }

    public function update()
    {
        $this->validate([
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
        ]);

        if ($this->selected_id) {

            //generar codigo
            $this->codigo_periodo = $this->determinarCodigo();
            // Verificar si el periodo ya existe
            $periodoExistente = Periodo::where('codigo_periodo', $this->codigo_periodo)
                ->where('id', '!=', $this->selected_id)
                ->first();
            if ($periodoExistente) {
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'editDataModal']);
                session()->flash('danger', 'El periodo ya existe.');
                return;
            }

            $record = Periodo::find($this->selected_id);
            $record->update([
                'codigo_periodo' => $this->codigo_periodo,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Periodo Actualizado Exitosamente.');
        }
    }

    public function eliminar($id)
    {
        $this->founded = Periodo::find($id);
        $this->periodos_carreras = $this->founded->carrerasPeriodos()->count();
        if ($this->periodos_carreras > 0) {
            session()->flash('danger', 'No se puede eliminar el periodo porque tiene carreras asociadas.');
            return;
        }
    }
    public function destroy($id)
    {
        if ($id) {
            Periodo::where('id', $id)->delete();
        }
    }

    public function determinarCodigo()
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
        if ($anio_inicio == $anio_fin) {
            return $this->codigo_periodo = $mes_inicio_nombre . '-' . $mes_fin_nombre . $anio_inicio;
        } else {
            return $this->codigo_periodo = $mes_inicio_nombre . $anio_inicio . '-' . $mes_fin_nombre . $anio_fin;
        }
    }
}
