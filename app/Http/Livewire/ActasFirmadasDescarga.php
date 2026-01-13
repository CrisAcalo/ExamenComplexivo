<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
use App\Models\CarrerasPeriodo;
use App\Models\Periodo;
use App\Models\Tribunale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ActasFirmadasDescarga extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $keyWord = '';
    public $perPage = 10;
    public $periodoSeleccionado = '';
    public $carreraPeriodoSeleccionado = '';

    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedPeriodoSeleccionado()
    {
        $this->carreraPeriodoSeleccionado = '';
        $this->resetPage();
    }

    public function updatedCarreraPeriodoSeleccionado()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $keyWord = '%' . $this->keyWord . '%';

        // Obtener periodos donde el usuario es director o apoyo
        $carrerasPeriodos = CarrerasPeriodo::with(['carrera', 'periodo'])
            ->where(function ($query) use ($user) {
                $query->where('director_id', $user->id)
                      ->orWhere('docente_apoyo_id', $user->id);
            })
            ->get();

        // Obtener periodos únicos para el filtro
        $periodos = Periodo::whereIn('id', $carrerasPeriodos->pluck('periodo_id')->unique())
            ->orderBy('codigo_periodo', 'desc')
            ->get();

        // Query base de tribunales
        $query = Tribunale::whereHas('carrerasPeriodo', function ($q) use ($user) {
                $q->where('director_id', $user->id)
                  ->orWhere('docente_apoyo_id', $user->id);
            })
            ->where('estado', 'CERRADO')
            ->where('es_plantilla', false)
            ->whereNotNull('acta_firmada_path') // Solo tribunales con acta firmada
            ->with([
                'estudiante',
                'carrerasPeriodo.carrera',
                'carrerasPeriodo.periodo',
                'usuarioSubioActa',
                'miembrosTribunales.user'
            ]);

        // Filtrar por búsqueda
        if ($this->keyWord) {
            $query->where(function ($q) use ($keyWord) {
                $q->whereHas('estudiante', function ($sq) use ($keyWord) {
                    $sq->where('nombres', 'LIKE', $keyWord)
                       ->orWhere('apellidos', 'LIKE', $keyWord);
                })
                ->orWhere('fecha', 'LIKE', $keyWord);
            });
        }

        // Filtrar por periodo
        if ($this->periodoSeleccionado) {
            $query->whereHas('carrerasPeriodo', function ($q) {
                $q->where('periodo_id', $this->periodoSeleccionado);
            });
        }

        // Filtrar por carrera-periodo específico
        if ($this->carreraPeriodoSeleccionado) {
            $query->where('carrera_periodo_id', $this->carreraPeriodoSeleccionado);
        }

        $tribunales = $query->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate($this->perPage);

        // Filtrar carreras-periodos por periodo seleccionado
        $carrerasPeriodosFiltradas = $carrerasPeriodos;
        if ($this->periodoSeleccionado) {
            $carrerasPeriodosFiltradas = $carrerasPeriodos->where('periodo_id', $this->periodoSeleccionado);
        }

        return view('livewire.actas-firmadas-descarga.view', [
            'tribunales' => $tribunales,
            'periodos' => $periodos,
            'carrerasPeriodos' => $carrerasPeriodosFiltradas,
        ]);
    }

    public function descargarActaFirmada($tribunalId)
    {
        $tribunal = Tribunale::find($tribunalId);

        if (!$tribunal || !$tribunal->acta_firmada_path) {
            session()->flash('danger', 'Acta firmada no encontrada.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar permisos usando Gate
        if (!Gate::allows('descargar-acta-firmada-de-este-tribunal', $tribunal)) {
            session()->flash('danger', 'No tienes permisos para descargar esta acta.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            if (!Storage::disk('private')->exists($tribunal->acta_firmada_path)) {
                session()->flash('danger', 'El archivo no existe en el servidor.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            $nombreEstudiante = $tribunal->estudiante
                ? str_replace(' ', '_', $tribunal->estudiante->apellidos . '_' . $tribunal->estudiante->nombres)
                : 'tribunal_' . $tribunal->id;

            $nombreArchivo = 'Acta_Firmada_' . $nombreEstudiante . '_' . date('Y-m-d') . '.pdf';

            return Storage::disk('private')->download($tribunal->acta_firmada_path, $nombreArchivo);

        } catch (\Exception $e) {
            session()->flash('danger', 'Error al descargar el acta: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }
}
