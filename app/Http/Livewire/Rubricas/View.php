<?php

namespace App\Http\Livewire\Rubricas;

use Illuminate\Support\Facades\Gate;
use App\Helpers\ContextualAuth;
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

    public function mount()
    {
        // No se requiere verificación específica aquí ya que se manejará por ruta/middleware
        // La verificación de permisos se hace en cada método específico
    }

    /**
     * Verificar acceso a las rúbricas usando ContextualAuth
     */
    private function verificarAccesoRubricas()
    {
        $user = auth()->user();

        // Verificar si tiene permisos globales
        if (Gate::allows('ver rubricas') || Gate::allows('gestionar rubricas') || Gate::allows('gestionar plantillas rubricas')) {
            return true;
        }

        // Verificar si tiene asignaciones contextuales (Director o Docente de Apoyo)
        $userContext = ContextualAuth::getUserContextInfo($user);
        if ($userContext['carreras_director']->isNotEmpty() || $userContext['carreras_apoyo']->isNotEmpty()) {
            return true;
        }

        abort(403, 'No tienes permisos para acceder a las rúbricas.');
    }

    /**
     * Verificar si el usuario puede gestionar rúbricas
     */
    private function puedeGestionarRubricas()
    {
        $user = auth()->user();

        // Super Admin y Administrador pueden gestionar
        if (ContextualAuth::isSuperAdminOrAdmin($user)) {
            return Gate::allows('gestionar rubricas');
        }

        // Director y Docente de Apoyo también pueden gestionar si tienen el permiso
        $userContext = ContextualAuth::getUserContextInfo($user);
        if (($userContext['carreras_director']->isNotEmpty() || $userContext['carreras_apoyo']->isNotEmpty()) &&
            Gate::allows('gestionar rubricas')) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede gestionar plantillas de rúbricas
     */
    private function puedeGestionarPlantillasRubricas()
    {
        $user = auth()->user();

        // Principalmente para Super Admin y Administrador
        if (ContextualAuth::isSuperAdminOrAdmin($user)) {
            return Gate::allows('gestionar plantillas rubricas');
        }

        return false;
    }

    /**
     * Verificar si el usuario puede crear rúbricas
     */
    private function puedeCrearRubricas()
    {
        return $this->puedeGestionarRubricas() || $this->puedeGestionarPlantillasRubricas();
    }

    public function render()
    {
        // Verificar acceso al renderizar
        $this->verificarAccesoRubricas();

        $keyWord = '%' . $this->keyWord . '%';
        $rubricas = Rubrica::latest()
            ->where('nombre', 'LIKE', $keyWord)
            ->paginate(10);

        return view('livewire.rubricas.view', [
            'rubricas' => $rubricas,
        ]);
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
        // Verificar permisos
        if (!$this->puedeCrearRubricas()) {
            session()->flash('error', 'No tienes permisos para copiar rúbricas.');
            return;
        }

        $this->rubricaIdToCopy = $id;
        $this->copyRubrica();
    }

    public function copyRubrica()
    {
        // Verificar permisos
        if (!$this->puedeCrearRubricas()) {
            session()->flash('error', 'No tienes permisos para copiar rúbricas.');
            return;
        }

        if (!$this->rubricaIdToCopy) {
            session()->flash('error', 'No se especificó una rúbrica para copiar.');
            return;
        }
        $originalRubrica = Rubrica::with([
            'componentesRubrica.criteriosComponente.calificacionesCriterio'
        ])->find($this->rubricaIdToCopy);
        if (!$originalRubrica) {
            session()->flash('error', 'Rúbrica original no encontrada.');
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
            session()->flash('error', 'Ocurrió un error al copiar la rúbrica: ' . $e->getMessage());
        }
        $this->rubricaIdToCopy = null;
    }

    // --- Acción de Eliminar ---
    public function confirmDelete($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarRubricas() && !$this->puedeGestionarPlantillasRubricas()) {
            session()->flash('error', 'No tienes permisos para eliminar rúbricas.');
            return;
        }

        $rubrica = Rubrica::find($id);

        if (!$rubrica) {
            session()->flash('error', 'Rúbrica no encontrada.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $enUsoEnPlan = DB::table('carreras_periodos_has_rubrica')
                           ->where('rubrica_id', $rubrica->id)
                           ->exists();

        if ($enUsoEnPlan) {
            session()->flash('warning', 'Esta rúbrica no se puede eliminar porque está asignada a uno o más Planes de Evaluación.');
            $this->dispatchBrowserEvent('showFlashMessage');
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
        // Verificar permisos
        if (!$this->puedeGestionarRubricas() && !$this->puedeGestionarPlantillasRubricas()) {
            session()->flash('error', 'No tienes permisos para eliminar rúbricas.');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            $this->resetDeleteConfirmation();
            return;
        }

        if (!$this->rubricaAEliminar) {
            session()->flash('error', 'Error: No se ha especificado la rúbrica a eliminar.');
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            $this->resetDeleteConfirmation();
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            $this->rubricaAEliminar->delete();
            session()->flash('success', 'Rúbrica eliminada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al intentar eliminar la rúbrica.');
        }

        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
        $this->resetDeleteConfirmation();
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function resetDeleteConfirmation()
    {
        $this->rubricaAEliminar = null;
        $this->confirmingRubricaDeletion = false;
    }


    // Redirección para el botón "Nueva Rúbrica"
    public function create()
    {
        // Verificar permisos
        if (!$this->puedeCrearRubricas()) {
            session()->flash('error', 'No tienes permisos para crear rúbricas.');
            return;
        }

        return redirect()->route('rubricas.create');
    }
}
