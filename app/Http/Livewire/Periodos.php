<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Periodo;

class Periodos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $codigo_periodo, $descripcion, $fecha_inicio, $fecha_fin, $founded, $periodos_carreras;
    public $periodoAEliminarId;
    public $confirmingPeriodoDeletion = false;
    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.periodos.view', [
            'periodos' => Periodo::latest()
                ->where('codigo_periodo', 'LIKE', $keyWord)
                ->orWhere('descripcion', 'LIKE', $keyWord)
                ->orWhere('fecha_inicio', 'LIKE', $keyWord)
                ->orWhere('fecha_fin', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }

    public function open($periodoID)
    {
        return redirect()->route('periodos.profile', $periodoID);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->codigo_periodo = null;
        $this->descripcion = null;
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
    }

    public function store()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        [$this->codigo_periodo, $this->descripcion] = $this->determinarCodigoYDescripcion();

        $periodoExistente = Periodo::where('codigo_periodo', $this->codigo_periodo)
            ->orWhere('descripcion', $this->descripcion)
            ->first();
        if ($periodoExistente) {
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
            session()->flash('danger', 'El periodo ya existe.');
            return;
        }

        Periodo::create([
            'codigo_periodo' => $this->codigo_periodo,
            'descripcion' => $this->descripcion,
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
        $this->descripcion = $record->descripcion;
        $this->fecha_inicio = $record->fecha_inicio;
        $this->fecha_fin = $record->fecha_fin;
    }

    public function update()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($this->selected_id) {
            [$this->codigo_periodo, $this->descripcion] = $this->determinarCodigoYDescripcion();
            $periodoExistente = Periodo::where(function ($q) {
                $q->where('codigo_periodo', $this->codigo_periodo)
                    ->orWhere('descripcion', $this->descripcion);
            })
                ->where('id', '!=', $this->selected_id)
                ->first();
            if ($periodoExistente) {
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
                session()->flash('danger', 'El periodo ya existe.');
                return;
            }

            $record = Periodo::find($this->selected_id);
            $record->update([
                'codigo_periodo' => $this->codigo_periodo,
                'descripcion' => $this->descripcion,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Periodo Actualizado Exitosamente.');
        }
    }

    public function eliminar($id) // Renombrar a confirmDelete para más claridad
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            session()->flash('danger', 'Periodo no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage'); // Usar un listener JS si tienes alertas flotantes
            return;
        }

        if ($periodo->carrerasPeriodos()->exists()) { // Usar exists() es más eficiente que count()
            session()->flash('warning', 'No se puede eliminar el periodo porque tiene carreras asociadas.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Si pasa las validaciones, prepara el modal
        $this->periodoAEliminarId = $id;
        $this->confirmingPeriodoDeletion = true;
        // El modal se abre por data-bs-toggle en el botón, no necesitamos un evento JS aquí.
    }

    public function destroy()
    {
        if (!$this->periodoAEliminarId) {
            // No debería ocurrir si el flujo es correcto
            return;
        }

        // Volver a verificar por si acaso algo cambió entre la confirmación y la acción
        $periodo = Periodo::find($this->periodoAEliminarId);
        if ($periodo && $periodo->carrerasPeriodos()->exists()) {
            session()->flash('danger', 'Acción cancelada: El periodo ahora tiene carreras asociadas.');
            $this->dispatchBrowserEvent('showFlashMessage');
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            return;
        }

        try {
            Periodo::destroy($this->periodoAEliminarId); // Usar destroy(id) es más directo
            session()->flash('success', 'Periodo eliminado con éxito.');
        } catch (\Exception $e) {
            session()->flash('danger', 'Ocurrió un error al eliminar el periodo.');
        }

        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);

        // Si estás en una página del paginador que ya no existe, ve a la primera página.
        if (Periodo::paginate(10)->currentPage() > Periodo::paginate(10)->lastPage()) {
            $this->resetPage();
        }
    }

    public function resetDeleteConfirmation()
    {
        $this->periodoAEliminarId = null;
        $this->confirmingPeriodoDeletion = false;
    }

    public function determinarCodigoYDescripcion()
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
        $anio_fin_full = $fecha_fin->format('Y');
        $mes_inicio_nombre = $meses[$mes_inicio];
        $mes_fin_nombre = $meses[$mes_fin];

        // Nuevo código de periodo según el mes de fin
        if (in_array($mes_fin, ['01', '02', '03', '04'])) {
            $codigo = $anio_fin_full . '01';
        } elseif (in_array($mes_fin, ['05', '06', '07', '08'])) {
            $codigo = $anio_fin_full . '02';
        } else {
            $codigo = $anio_fin_full . '03';
        }

        // descripcion: MAY-SEP25 o OCT21-MAR22
        if ($anio_inicio == $anio_fin) {
            $descripcion = $mes_inicio_nombre . '-' . $mes_fin_nombre . $anio_inicio;
        } else {
            $descripcion = $mes_inicio_nombre . $anio_inicio . '-' . $mes_fin_nombre . $anio_fin;
        }
        return [$codigo, $descripcion];
    }
}
