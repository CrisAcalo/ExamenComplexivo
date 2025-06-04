<?php

namespace App\Http\Livewire;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
// Quitar ComponenteRubrica si no se usa directamente aquí
// use App\Models\ComponenteRubrica;
// Quitar ComponentesEvaluacion si no se usa directamente aquí
// use App\Models\ComponentesEvaluacion;
use App\Models\Estudiante;
use App\Models\MiembroCalificacion;
use App\Models\Periodo;
use App\Models\PlanEvaluacion; // Añadir para cargar el plan
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tribunale;
use App\Models\User;
use Illuminate\Support\Facades\DB; // Para validaciones si es necesario

class Tribunales extends Component
{
    use WithPagination;
    public $carreraPeriodoId;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord; // Quitar carrera_periodo_id de aquí si siempre viene de mount
    public $estudiante_id, $fecha, $hora_inicio, $hora_fin; // Para el modal de creación

    public $profesores;
    public $estudiantesDisponibles; // Renombrar para claridad
    public $carreraPeriodo, $carrera, $periodo;
    public $presidente_id, $integrante1_id, $integrante2_id; // Para el modal de creación

    // Para mostrar el Plan de Evaluación
    public $planEvaluacionActivo;

    // Para el modal de eliminación de tribunal
    public $tribunalAEliminar;

    // Quitar nombre_componente, ponderacion_componente si createDataModal ya no los usa
    // public $nombre_componente, $ponderacion_componente;

    public function rules() // Definir reglas para el modal de creación
    {
        return [
            'estudiante_id' => 'required|exists:estudiantes,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required', // Podría ser 'date_format:H:i'
            'hora_fin' => 'required|after:hora_inicio', // Podría ser 'date_format:H:i|after:hora_inicio'
            'presidente_id' => 'required|exists:users,id|different:integrante1_id|different:integrante2_id',
            'integrante1_id' => 'required|exists:users,id|different:presidente_id|different:integrante2_id',
            'integrante2_id' => 'required|exists:users,id|different:presidente_id|different:integrante1_id',
        ];
    }

    public function mount($carreraPeriodoId)
    {
        $this->carreraPeriodoId = $carreraPeriodoId;
        $this->carreraPeriodo = CarrerasPeriodo::with(['carrera', 'periodo'])->find($carreraPeriodoId);

        if (!$this->carreraPeriodo) {
            abort(404, 'Contexto Carrera-Periodo no encontrado.');
        }

        $this->carrera = $this->carreraPeriodo->carrera;
        $this->periodo = $this->carreraPeriodo->periodo;

        $this->profesores = User::all();

        // Estudiantes que aún no tienen tribunal en este carrera_periodo_id
        $estudiantesConTribunalIds = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->pluck('estudiante_id')->toArray();
        $this->estudiantesDisponibles = Estudiante::whereNotIn('id', $estudiantesConTribunalIds)
            ->orderBy('apellidos')->orderBy('nombres')->get();

        // Cargar el Plan de Evaluación activo para este carrera_periodo_id
        $this->planEvaluacionActivo = PlanEvaluacion::with('itemsPlanEvaluacion.rubricaPlantilla')
            ->where('carrera_periodo_id', $this->carreraPeriodoId)
            ->first();
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        // Filtrar tribunales por el carreraPeriodoId actual
        $tribunales = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->with(['estudiante', 'miembrosTribunales.user']) // Carga ansiosa
            // Lógica de búsqueda (opcional, si la necesitas compleja)
            ->where(function ($query) use ($keyWord) {
                $query->whereHas('estudiante', function ($q) use ($keyWord) {
                    $q->where('nombres', 'LIKE', $keyWord)
                        ->orWhere('apellidos', 'LIKE', $keyWord)
                        ->orWhere('ID_estudiante', 'LIKE', $keyWord);
                })
                    ->orWhere('fecha', 'LIKE', $keyWord);
                // Puedes añadir más campos a la búsqueda si es necesario
            })
            ->orderBy('fecha', 'desc') // O como prefieras ordenar
            ->paginate(10);

        return view('livewire.tribunales.view', [
            'tribunales' => $tribunales,
        ]);
    }


    public function cancel()
    {
        $this->resetInput();
        $this->resetValidation(); // Limpiar errores de validación al cancelar
    }

    private function resetInput() // Para el modal de creación
    {
        $this->estudiante_id = null;
        $this->fecha = null;
        $this->hora_inicio = null;
        $this->hora_fin = null;
        $this->presidente_id = null;
        $this->integrante1_id = null;
        $this->integrante2_id = null;
    }

    public function store() // Crear Tribunal
    {
        $this->validate(); // Usa las rules() definidas arriba

        // Validar que el estudiante no tenga ya un tribunal en este carrera_periodo
        $existingTribunalForStudent = Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)
            ->where('estudiante_id', $this->estudiante_id)
            ->exists();
        if ($existingTribunalForStudent) {
            $this->addError('estudiante_id', 'Este estudiante ya tiene un tribunal asignado en este periodo y carrera.');
            return;
        }

        DB::transaction(function () {
            $newTribunale = Tribunale::create([
                'carrera_periodo_id' => $this->carreraPeriodoId,
                'estudiante_id' => $this->estudiante_id,
                'fecha' => $this->fecha,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
            ]);

            //Crear Miembros del tribunal
            $newTribunale->miembrosTribunales()->createMany([
                ['user_id' => $this->presidente_id, 'status' => 'PRESIDENTE'],
                ['user_id' => $this->integrante1_id, 'status' => 'INTEGRANTE1'],
                ['user_id' => $this->integrante2_id, 'status' => 'INTEGRANTE2'],
            ]);
        });

        session()->flash('success', 'Tribunal Creado Exitosamente.');
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        $this->resetInput();
        $this->estudiantesDisponibles = Estudiante::whereNotIn('id', Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)->pluck('estudiante_id')->toArray())
            ->orderBy('apellidos')->orderBy('nombres')->get(); // Refrescar lista
    }

    public function confirmDelete($tribunalId)
    {
        $tribunal = Tribunale::with('miembrosTribunales')->find($tribunalId); // Cargar miembros para la validación

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage'); // Asume que tienes este listener JS
            return;
        }

        $miembrosIds = $tribunal->miembrosTribunales->pluck('id')->toArray();

        if (!empty($miembrosIds)) {
            $tieneCalificaciones = MiembroCalificacion::whereIn('miembro_tribunal_id', $miembrosIds)->exists();

            if ($tieneCalificaciones) {
                session()->flash('warning', 'Este tribunal no se puede eliminar porque ya tiene calificaciones registradas por sus miembros.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return; // No abrir el modal
            }
        }

        $this->tribunalAEliminar = $tribunal;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteTribunalModal']);
    }

    public function destroy()
    {
        if (!$this->tribunalAEliminar) {
            session()->flash('danger', 'Error: No se ha especificado el tribunal a eliminar.');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteTribunalModal']);
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            DB::transaction(function () {
                $this->tribunalAEliminar->delete();
            });

            session()->flash('success', 'Tribunal eliminado exitosamente.');

            // Refrescar lista de estudiantes disponibles
            $this->estudiantesDisponibles = Estudiante::whereNotIn(
                'id',
                Tribunale::where('carrera_periodo_id', $this->carreraPeriodoId)->pluck('estudiante_id')->toArray()
            )->orderBy('apellidos')->orderBy('nombres')->get();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) { // Código de error común para FK constraint violation en MySQL
                session()->flash('danger', 'No se puede eliminar el tribunal porque tiene datos relacionados que impiden su borrado (ej. calificaciones no detectadas previamente o actas).');
            } else {
                // Log::error("Error de BD al eliminar tribunal ID {$this->tribunalAEliminar->id}: " . $e->getMessage());
                session()->flash('danger', 'Error de base de datos al intentar eliminar el tribunal.');
            }
        } catch (\Exception $e) {
            // Log::error("Error general al eliminar tribunal ID {$this->tribunalAEliminar->id}: " . $e->getMessage());
            session()->flash('danger', 'Ocurrió un error inesperado al eliminar el tribunal.');
        }

        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteTribunalModal']);
        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('showFlashMessage'); // Para mostrar el mensaje de éxito/error
    }

    public function resetDeleteConfirmation()
    {
        $this->tribunalAEliminar = null;
    }
}
