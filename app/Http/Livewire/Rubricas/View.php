<?php

namespace App\Http\Livewire\Rubricas;

use App\Models\Rubrica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $keyWord = '';
    public $rubricaIdToCopy;

    // Para el modal de eliminación
    public $rubricaAEliminar; // Similar a tu $founded, almacenará el objeto Rubrica a eliminar
    public $confirmingRubricaDeletion = false; // Controlará la visibilidad del modal si no usas eventos JS directamente

    protected $listeners = ['initializePopovers' => 'initializePopoversJs'];

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        $rubricas = Rubrica::latest()
            ->where('nombre', 'LIKE', $keyWord)
            ->paginate(10);

        // $this->dispatchBrowserEvent('initializePopovers'); // Moveremos esto para evitar llamadas excesivas
        return view('livewire.rubricas.view', [
            'rubricas' => $rubricas,
        ])->layout('layouts.panel');
    }

    // Se puede llamar desde el @push('scripts') en la vista para que se ejecute una vez cargada y después de cada actualización
    public function dehydrate()
    {
        $this->dispatchBrowserEvent('initializePopovers');
    }


    public function initializePopoversJs()
    {
        // Placeholder
    }

    public function generarHtmlPrevisualizacion($rubricaId)
    {
        // ... (código sin cambios)
        $rubrica = Rubrica::with(['componentesRubrica.criteriosComponente'])->find($rubricaId);
        if (!$rubrica) {
            return 'Rúbrica no encontrada.';
        }
        $html = '<div style="max-width: 450px; max-height: 350px; overflow-y: auto; font-size: 0.75rem; text-align: left;">';
        $html .= '<h6 class="text-primary">R: ' . htmlspecialchars($rubrica->nombre, ENT_QUOTES, 'UTF-8') . '</h6>';
        foreach ($rubrica->componentesRubrica as $componente) {
            $html .= '<div class="mb-2 border p-1">';
            $html .= '<strong>C: ' . htmlspecialchars($componente->nombre, ENT_QUOTES, 'UTF-8') . ' (' . $componente->ponderacion . '%)</strong>';
            if ($componente->criteriosComponente->count() > 0) {
                $html .= '<ul class="list-unstyled ps-2 mb-0">';
                foreach ($componente->criteriosComponente as $criterio) {
                    $nombreCriterioCorto = Str::limit(htmlspecialchars($criterio->nombre, ENT_QUOTES, 'UTF-8'), 50);
                    $html .= '<li><small><em>Cr:</em> ' . $nombreCriterioCorto . '</small></li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p class="ms-2 mb-0"><small><em>Sin criterios definidos.</em></small></p>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return addslashes($html);
    }

    // --- Acción de Copiar ---
    public function confirmCopy($id)
    {
        $this->rubricaIdToCopy = $id;
        $this->copyRubrica();
    }

    public function copyRubrica()
    {
        // ... (código sin cambios)
        if (!$this->rubricaIdToCopy) {
            session()->flash('danger', 'No se especificó una rúbrica para copiar.');
            return;
        }
        $originalRubrica = Rubrica::with([
            'componentesRubrica.criteriosComponente.calificacionesCriterio'
        ])->find($this->rubricaIdToCopy);
        if (!$originalRubrica) {
            session()->flash('danger', 'Rúbrica original no encontrada.');
            $this->rubricaIdToCopy = null;
            return;
        }
        try {
            DB::transaction(function () use ($originalRubrica) {
                $nuevaRubrica = $originalRubrica->replicate();
                $nuevaRubrica->nombre = $originalRubrica->nombre . ' - Copia';
                $count = Rubrica::where('nombre', 'LIKE', $originalRubrica->nombre . ' - Copia%')->where('id', '!=', $originalRubrica->id)->count();
                if ($count > 0) {
                    // Buscar el número más alto existente en las copias
                    $maxNum = 0;
                    $existingCopies = Rubrica::where('nombre', 'LIKE', $originalRubrica->nombre . ' - Copia%')->pluck('nombre');
                    foreach($existingCopies as $ec) {
                        if (preg_match('/ - Copia(?: \((\d+)\))?$/', $ec, $matches)) {
                            $num = isset($matches[1]) ? intval($matches[1]) : 1;
                            if ($num > $maxNum) $maxNum = $num;
                        }
                    }
                    $nuevaRubrica->nombre = $originalRubrica->nombre . ' - Copia (' . ($maxNum + 1) . ')';
                }
                $nuevaRubrica->created_at = now();
                $nuevaRubrica->updated_at = now();
                $nuevaRubrica->push();
                foreach ($originalRubrica->componentesRubrica as $originalComponente) {
                    $nuevoComponente = $originalComponente->replicate();
                    $nuevoComponente->rubrica_id = $nuevaRubrica->id;
                    $nuevoComponente->created_at = now();
                    $nuevoComponente->updated_at = now();
                    $nuevoComponente->push();
                    foreach ($originalComponente->criteriosComponente as $originalCriterio) {
                        $nuevoCriterio = $originalCriterio->replicate();
                        $nuevoCriterio->componente_id = $nuevoComponente->id;
                        $nuevoCriterio->created_at = now();
                        $nuevoCriterio->updated_at = now();
                        $nuevoCriterio->push();
                        foreach ($originalCriterio->calificacionesCriterio as $originalCalificacion) {
                            $nuevaCalificacion = $originalCalificacion->replicate();
                            $nuevaCalificacion->criterio_id = $nuevoCriterio->id;
                            $nuevaCalificacion->created_at = now();
                            $nuevaCalificacion->updated_at = now();
                            $nuevaCalificacion->save();
                        }
                    }
                }
            });
            session()->flash('success', 'Rúbrica copiada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('danger', 'Ocurrió un error al copiar la rúbrica. Detalles: ' . $e->getMessage());
        }
        $this->rubricaIdToCopy = null;
    }

    // --- Acción de Eliminar ---
    public function confirmDelete($id)
    {
        $rubrica = Rubrica::find($id);

        if (!$rubrica) {
            session()->flash('danger', 'Rúbrica no encontrada.');
            $this->dispatchBrowserEvent('showFlashMessage'); // Asume que tienes un listener para mostrar alertas
            return;
        }

        $enUsoEnPlan = DB::table('carreras_periodos_has_rubrica')
                           ->where('rubrica_id', $rubrica->id)
                           ->exists();

        if ($enUsoEnPlan) {
            session()->flash('warning', 'Esta rúbrica no se puede eliminar porque está asignada a uno o más Planes de Evaluación.');
            $this->dispatchBrowserEvent('showFlashMessage'); // Asume que tienes un listener para mostrar alertas
            return;
        }

        $tieneCalificaciones = DB::table('miembro_calificacion as mc')
            ->join('calificaciones_criterio as cc', 'mc.calificacion_criterio_id', '=', 'cc.id') // Asumiendo que esta es la FK en miembro_calificacion
            ->join('criterios_componente as critc', 'cc.criterio_id', '=', 'critc.id')
            ->join('componentes_rubrica as compr', 'critc.componente_id', '=', 'compr.id')
            ->where('compr.rubrica_id', $rubrica->id)
            ->exists();


        if ($tieneCalificaciones) {
            session()->flash('warning', 'Esta rúbrica no se puede eliminar porque ya existen calificaciones registradas utilizándola.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Si pasa todas las validaciones:
        $this->rubricaAEliminar = $rubrica;
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteDataModal']);
    }

    public function destroy()
    {
        if (!$this->rubricaAEliminar) {
            // Esto no debería ocurrir si confirmDelete funciona bien, pero es una salvaguarda
            session()->flash('danger', 'Error: No se ha especificado la rúbrica a eliminar.');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            $this->rubricaAEliminar->delete();
            session()->flash('success', 'Rúbrica eliminada exitosamente.');
        } catch (\Exception $e) {
            // Log::error("Error al eliminar rúbrica ID {$this->rubricaAEliminar->id}: " . $e->getMessage());
            session()->flash('danger', 'Ocurrió un error al intentar eliminar la rúbrica.');
        }

        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('showFlashMessage'); // Para mostrar el mensaje de éxito/error
    }

    public function resetDeleteConfirmation()
    {
        $this->rubricaAEliminar = null;
        $this->confirmingRubricaDeletion = false;
    }


    // Redirección para el botón "Nueva Rúbrica"
    public function create()
    {
        return redirect()->route('rubricas.create');
    }
}
