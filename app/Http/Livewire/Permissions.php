<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class Permissions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $name, $guard_name;
    public $rolEncontrado;

    public function mount()
    {
        // Verificar autorización al montar el componente
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            abort(403, 'No tienes permisos para gestionar permisos.');
        }

        $this->guard_name = 'web';
    }

    public function render()
    {
        // Verificar autorización en cada render
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            abort(403, 'No tienes permisos para gestionar permisos.');
        }

        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.permissions.view', [
            'permissions' => Permission::latest()
                ->orWhere('name', 'LIKE', $keyWord)
                ->orWhere('guard_name', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->name = null;
        $this->guard_name = null;
    }

    public function store()
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para crear permisos.');
            return;
        }

        $this->validate([
            'name' => 'required',
        ]);

        Permission::create([
            'name' => $this->name,
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Permiso creado exitosamente.');
    }

    public function edit($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para editar permisos.');
            return;
        }

        $record = Permission::findById($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function update()
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para actualizar permisos.');
            return;
        }

        $this->validate([
            'name' => 'required'
        ]);

        if ($this->selected_id) {
            $record = Permission::findById($this->selected_id);
            $record->update([
                'name' => $this->name
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);

            session()->flash('success', 'Permiso actualizado exitosamente.');
        }
    }

    public function eliminar($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para eliminar permisos.');
            return;
        }

        $this->rolEncontrado = Permission::findById($id);
    }

    public function destroy($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para eliminar permisos.');
            return;
        }

        // Verificar que no sea un permiso del sistema crítico
        $permission = Permission::findById($id);
        $criticalPermissions = [
            'gestionar roles y permisos',
            'gestionar usuarios',
            'gestionar configuracion sistema'
        ];

        if (in_array($permission->name, $criticalPermissions)) {
            session()->flash('error', 'No se puede eliminar este permiso crítico del sistema.');
            return;
        }

        if ($id) {
            Permission::findById($id)->delete();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
        }
        $this->rolEncontrado = null;
        session()->flash('success', 'Permiso eliminado exitosamente.');
    }
}
