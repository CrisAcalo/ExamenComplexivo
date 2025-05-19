<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class Roles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selected_id, $keyWord, $name, $guard_name, $permisos, $permisosSeleccionados = [];

    public $rolEncontrado = null;
    public $test, $sections = [];
    public function mount()
    {
        $this->permisos = Permission::all();
        $this->guard_name = 'web';
    }

    public function render()
    {
        // if (Auth::user()->hasRole('Super Admin')) {
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
        // } else {
        //     abort(403, 'No tiene permisos para acceder a esta página.');
        // }
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
        // if (Auth::user()->hasAnyRole(['Admin'])) {
        $this->validate([
            'name' => 'required'
        ]);

        Role::create([
            'name' => $this->name
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Rol creado exitosamente.');
        // app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        // }else{
        //     abort(403, 'Unauthorized.');
        // }
    }

    public function edit($id)
    {
        $record = Role::findById($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function permisosBusqueda($id)
    {
        $role = Role::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->permisos = Permission::all();
        $this->permisosSeleccionados = $role->permissions->pluck('id')->toArray();
    }

    public function update()
    {
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
        if ($id) {
            Role::findById($id)->delete();
            $this->rolEncontrado = null;
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('success', 'Rol eliminado exitosamente.');
        }
    }

    public function editPermisionsId($id) //Funcion para editar los permisos a un rol
    {
        $record = Role::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function eliminar($id)
    {
        $this->rolEncontrado = Role::findById($id);
    }
}
