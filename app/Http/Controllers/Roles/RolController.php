<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
// use App\Models\Role;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::all(); // Obtener todos los roles
        return view('roles.index', compact('roles')); // Devolver la vista con los roles
    }

    public function updatePermisos(Request $data, $id)
    {
        // return $data;
        $rol = Role::findById(decrypt($id));

        if ($data && $rol) {
            
            $rol->syncPermissions($data->permisos);
            
            session()->flash('success', 'Permisos asignados correctamente.');

            return redirect()->route('roles.');
        } else {
            return back()->with('warning', 'No se han encontrado datos a o para actualizar');
        }
    }
}
