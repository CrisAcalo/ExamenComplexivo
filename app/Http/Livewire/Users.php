<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class Users extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $name, $email, $password, $password_confirmation;
    public $usuarioFounded;

    protected function rules()
    {
        return [
            'name' => 'required|min:6',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', 'min:6', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }

    protected $messages = [
        'name.required' => 'El campo de Nombre no puede estar vacío.',
        'email.required' => 'El campo de Email no puede estar vacío.',
        'email.email' => 'Ingrese un email válido.',
        'email.unique' => 'El email ya está en uso.',
        'password.required' => 'El campo de Contraseña no puede estar vacío.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.letters' => 'La contraseña debe contener al menos una letra.',
        'password.mixedCase' => 'La contraseña debe contener al menos una letra mayúscula y una minúscula.',
        'password.numbers' => 'La contraseña debe contener al menos un número.',
        'password.symbols' => 'La contraseña debe contener al menos un símbolo.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        $users = User::where(function ($query) use ($keyWord) {
                $query
                    ->orWhere('name', 'LIKE', $keyWord)
                    ->orWhere('email', 'LIKE', $keyWord);
            })
            ->paginate(13);
        return view('livewire.users.view', compact('users'));
    }
    public function mount()
    {
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->name = null;
        $this->email = null;
        $this->password = null;
        $this->password_confirmation = null;
    }

    public function store()
    {
        $this->validate();

        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->password = Hash::make($this->password);
        $user->save();

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'User Successfully created.');
    }

    public function edit($id)
    {
        return redirect()->route('users.profile', ['id' => encrypt($id)]);
        // $record = User::findOrFail($id);
        // $this->selected_id = $id;
        // $this->name = $record->name;
        // $this->email = $record->email;
        // $this->updated_at = $record->updated_at;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required',
        ]);

        if ($this->selected_id) {
            $record = User::find($this->selected_id);
            $record->assignRole('writer');

            $record->update([
                'name' => $this->name,
                'email' => $this->email
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModal');
            session()->flash('success', 'Usuario actualizado exitosamente.');
        }
    }
    public function eliminar($id)
    {
        $this->usuarioFounded = User::find($id);
    }
    public function destroy($id)
    {
        if ($id) {
            try {
                User::where('id', $id)->delete();
                session()->flash('success', 'Usuario eliminado exitosamente.');
                $this->resetInput();
                $this->usuarioFounded = null;
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            } catch (\Throwable $th) {
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
                $this->resetInput();
                $this->usuarioFounded = null;
                session()->flash('danger', 'No se puede eliminar el usurio ya que tiene cheques asignados.');
            }
        }
    }

    public function impersonate($id)
    {
        $user = User::find($id);
        Auth::user()->impersonate($user);
        return redirect()->to('/home'); // Cambia '/dashboard' a la ruta a la que quieras redirigir después de impersonar
    }
}
