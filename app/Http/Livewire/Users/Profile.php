<?php

namespace App\Http\Livewire\Users;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class Profile extends Component
{
    public $user, $name, $email, $password, $password_confirmation, $userId, $roles = [], $selectedRoles = array();

    protected function rules()
    {
        return [
            'name' => 'nullable|min:6',
            'email' => 'nullable|email',
            'password' => ['nullable', 'confirmed', 'min:6'],
        ];
    }

    protected $messages = [
        'email.email' => 'Ingrese un email válido.',
        'email.unique' => 'El email ya está en uso.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.users.profile');
    }
    public function mount()
    {
    }

    public function update()
    {
        $user = User::find($this->userId);
        if ($user->email === $this->email) {
            $this->validate([
                'name' => 'required|min:6',
                'email' => 'required|email',
                'password' => 'nullable|confirmed|min:6',
            ]);
        } else {
            $this->validate([
                'name' => 'required|min:6',
                'email' => 'required|email|unique:users,email,',
                'password' => 'nullable|confirmed|min:6',
            ]);
        }

        $user->name = $this->name;
        $user->email = $this->email;
        if ($this->password) {
            $user->password = bcrypt($this->password);
        }
        $user->save();
        session()->flash('success', 'Perfil actualizado correctamente.');
    }
}
