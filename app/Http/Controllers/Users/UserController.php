<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }
    public function profile($id)
    {
        $user = User::find(decrypt($id));
        $roles = Role::all();
        $selectedRoles = [];
        return view('livewire.users.profileInfo', compact('user', 'roles', 'selectedRoles'));
    }

    public function exitImpersonate()
    {
        Auth::user()->leaveImpersonation();
        return view('/home');
    }
    public function updateRoles(Request $data, $id)
    {

        $user = User::find(decrypt($id));

        if ($data && $user) {
            $rolesToassign = $data->except(['_token', '_method']);
            $selectedIdRoles = [];
            foreach ($rolesToassign as $checkbox => $value) {
                $selectedIdRoles[] = $value;
            }
            $user->syncRoles($selectedIdRoles);
            session()->flash('success', 'Roles asignados correctamente.');
            return redirect()->route('users.profile', encrypt($user->id));
        } else {
            return back()->with('warning', 'No se han encontrado datos a o para actualizar');
        }
    }
}
