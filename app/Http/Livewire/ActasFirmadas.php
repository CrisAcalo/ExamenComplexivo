<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
use App\Models\Tribunale;
use App\Models\TribunalLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ActasFirmadas extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $keyWord = '';
    public $perPage = 10;
    public $actaFirmada;
    public $tribunalSeleccionado;

    protected $rules = [
        'actaFirmada' => 'required|file|mimes:pdf|max:10240',
    ];

    protected $messages = [
        'actaFirmada.required' => 'Debe seleccionar un archivo PDF',
        'actaFirmada.mimes' => 'El archivo debe ser PDF',
        'actaFirmada.max' => 'El archivo no debe pesar más de 10MB',
    ];

    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        $user = Auth::user();

        // Obtener tribunales donde el usuario es presidente y están cerrados
        $tribunales = Tribunale::whereHas('miembrosTribunales', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'PRESIDENTE');
            })
            ->where('estado', 'CERRADO')
            ->where('es_plantilla', false)
            ->with(['estudiante', 'carrerasPeriodo.carrera', 'carrerasPeriodo.periodo', 'usuarioSubioActa'])
            ->where(function ($query) use ($keyWord) {
                $query->whereHas('estudiante', function ($q) use ($keyWord) {
                    $q->where('nombres', 'LIKE', $keyWord)
                      ->orWhere('apellidos', 'LIKE', $keyWord);
                })
                ->orWhere('fecha', 'LIKE', $keyWord);
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate($this->perPage);

        return view('livewire.actas-firmadas.view', [
            'tribunales' => $tribunales,
        ]);
    }

    public function abrirModalSubir($tribunalId)
    {
        $tribunal = Tribunale::find($tribunalId);

        if (!$tribunal) {
            session()->flash('danger', 'Tribunal no encontrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar permisos usando Gate
        if (!Gate::allows('subir-acta-firmada-este-tribunal-como-presidente', $tribunal)) {
            session()->flash('danger', 'No tienes permisos para subir acta firmada en este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $this->tribunalSeleccionado = $tribunal;
        $this->actaFirmada = null;
        $this->resetValidation();
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'subirActaModal']);
    }

    public function subirActa()
    {
        if (!$this->tribunalSeleccionado) {
            session()->flash('danger', 'No se ha seleccionado un tribunal.');
            return;
        }

        // Verificar permisos nuevamente
        if (!Gate::allows('subir-acta-firmada-este-tribunal-como-presidente', $this->tribunalSeleccionado)) {
            session()->flash('danger', 'No tienes permisos para subir acta firmada en este tribunal.');
            return;
        }

        $this->validate();

        try {
            // Si ya existe un acta firmada, eliminarla del storage
            if ($this->tribunalSeleccionado->acta_firmada_path) {
                Storage::disk('private')->delete($this->tribunalSeleccionado->acta_firmada_path);
            }

            // Guardar el nuevo archivo
            $path = $this->actaFirmada->store('actas-firmadas', 'private');

            // Actualizar el tribunal
            $this->tribunalSeleccionado->update([
                'acta_firmada_path' => $path,
                'acta_firmada_subida_por' => Auth::id(),
                'acta_firmada_fecha' => now(),
            ]);

            // Registrar log
            TribunalLog::create([
                'tribunal_id' => $this->tribunalSeleccionado->id,
                'user_id' => Auth::id(),
                'accion' => 'ACTA_FIRMADA_SUBIDA',
                'descripcion' => 'Acta firmada subida por el presidente del tribunal.',
            ]);

            session()->flash('success', 'Acta firmada subida exitosamente.');
            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'subirActaModal']);
            $this->dispatchBrowserEvent('showFlashMessage');

        } catch (\Exception $e) {
            session()->flash('danger', 'Error al subir el acta: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }

    public function descargarActaFirmada($tribunalId)
    {
        $tribunal = Tribunale::find($tribunalId);

        if (!$tribunal || !$tribunal->acta_firmada_path) {
            session()->flash('danger', 'Acta firmada no encontrada.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar permisos
        if (!Gate::allows('subir-acta-firmada-este-tribunal-como-presidente', $tribunal)) {
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

    public function cancelar()
    {
        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'subirActaModal']);
    }

    private function resetInput()
    {
        $this->actaFirmada = null;
        $this->tribunalSeleccionado = null;
        $this->resetValidation();
    }
}
