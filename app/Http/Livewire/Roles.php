<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Roles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selected_id, $keyWord, $name, $guard_name, $permisos, $permisosSeleccionados = [];

    public $rolEncontrado = null;
    public $test, $sections = [];

    public function mount()
    {
        // Verificar autorización al montar el componente
        if (!auth()->user()->can('gestionar roles y permisos')) {
            abort(403, 'No tienes permisos para gestionar roles y permisos.');
        }

        $this->permisos = Permission::all();
        $this->guard_name = 'web';
    }

    public function render()
    {
        // Verificar autorización en cada render
        if (!auth()->user()->can('gestionar roles y permisos')) {
            abort(403, 'No tienes permisos para gestionar roles y permisos.');
        }

        $permissions = Permission::all();
        $this->sections = [];
        foreach ($permissions as $permission) {
            $parts = explode(' - ', $permission->name);
            $section = $parts[0];
            if (!array_key_exists($section, $this->sections)) {
                $this->sections[$section] = [];
            }
            $this->sections[$section][] = $permission;
        }
        $keyWord = '%' . $this->keyWord . '%';

        return view('livewire.roles.view', [
            'roles' => Role::latest()
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
            session()->flash('error', 'No tienes permisos para crear roles.');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:200',
        ]);

        Role::create([
            'name' => $this->name
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Rol creado exitosamente.');
    }

    public function edit($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para editar roles.');
            return;
        }

        $record = Role::findById($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function permisosBusqueda($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para gestionar permisos de roles.');
            return;
        }

        $role = Role::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->permisos = Permission::all();
        $this->permisosSeleccionados = $role->permissions->pluck('id')->toArray();
    }

    public function update()
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para actualizar roles.');
            return;
        }

        $this->validate([
            'name' => 'required',
        ]);

        if ($this->selected_id) {
            $record = Role::findById($this->selected_id);
            $record->update([
                'name' => $this->name,
            ]);
            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Rol actualizado exitosamente.');
        }
    }

    public function destroy($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para eliminar roles.');
            return;
        }

        // Verificar que no sea un rol del sistema protegido
        $role = Role::findById($id);
        if (in_array($role->name, ['Super Admin', 'Administrador'])) {
            session()->flash('error', 'No se puede eliminar este rol del sistema.');
            return;
        }

        if ($id) {
            Role::findById($id)->delete();
            $this->rolEncontrado = null;
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('success', 'Rol eliminado exitosamente.');
        }
    }

    public function editPermisionsId($id) //Funcion para editar los permisos a un rol
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para editar permisos de roles.');
            return;
        }

        $record = Role::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function eliminar($id)
    {
        // Verificar autorización
        if (!auth()->user()->hasPermissionTo('gestionar roles y permisos')) {
            session()->flash('error', 'No tienes permisos para eliminar roles.');
            return;
        }

        $this->rolEncontrado = Role::findById($id);
    }
}
