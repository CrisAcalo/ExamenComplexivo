<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Imports\ProfesoresImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class Users extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $name, $lastname, $email, $password, $password_confirmation;
    public $departamento_id; // Departamento al que pertenece el docente
    public $usuarioFounded;
    public $archivoExcelProfesores;
    public $importing = false;
    public $importFinished = false;
    public $importErrors = [];
    public $perPage = 13; // NUEVO
    public $departamentosDisponibles = []; // Para poblar el selector
    public $departamento_filter = ''; // Filtro por departamento en la vista

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'lastname' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', 'min:6', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'departamento_id' => 'required|exists:departamentos,id',
        ];
    }

    protected $messages = [
        'name.required' => 'El campo de Nombres no puede estar vacío.',
        'name.min' => 'Los nombres deben tener al menos 3 caracteres.',
        'lastname.required' => 'El campo de Apellidos no puede estar vacío.',
        'lastname.min' => 'Los apellidos deben tener al menos 3 caracteres.',
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
        'departamento_id.required' => 'El departamento es obligatorio.',
        'departamento_id.exists' => 'El departamento seleccionado no es válido.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->verificarAccesoUsuarios();
        $this->cargarDepartamentosDisponibles();
    }

    /**
     * Cargar departamentos disponibles para el selector
     */
    private function cargarDepartamentosDisponibles()
    {
        $this->departamentosDisponibles = \App\Models\Departamento::orderBy('nombre')->get();
    }

    /**
     * Verificar acceso a la gestión de usuarios
     */
    private function verificarAccesoUsuarios()
    {
        $user = auth()->user();

        // Super Admin o Admin con permiso
        if (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && Gate::allows('gestionar usuarios')) {
            return;
        }

        // Director de Carrera o Docente de Apoyo tienen acceso contextual
        if (\App\Helpers\ContextualAuth::hasActiveAssignments($user)) {
            return;
        }

        abort(403, 'No tienes permisos para acceder a la gestión de usuarios.');
    }

    /**
     * Verificar si el usuario puede gestionar usuarios
     */
    private function puedeGestionarUsuarios()
    {
        $user = auth()->user();

        // Super Admin o Admin con permiso
        if (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && Gate::allows('gestionar usuarios')) {
            return true;
        }

        // Director de Carrera o Docente de Apoyo pueden gestionar usuarios
        if (\App\Helpers\ContextualAuth::hasActiveAssignments($user)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede importar profesores
     */
    private function puedeImportarProfesores()
    {
        $user = auth()->user();

        // Super Admin o Admin con permiso
        if (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && Gate::allows('importar profesores')) {
            return true;
        }

        // Director de Carrera o Docente de Apoyo pueden importar profesores
        if (\App\Helpers\ContextualAuth::hasActiveAssignments($user)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede gestionar roles
     */
    private function puedeGestionarRoles()
    {
        return Gate::allows('gestionar roles y permisos');
    }

    public function render()
    {
        // Verificar acceso al renderizar
        $this->verificarAccesoUsuarios();

        $keyWord = '%' . $this->keyWord . '%';
        $users = User::where(function ($query) use ($keyWord) {
            $query
                ->orWhere('name', 'LIKE', $keyWord)
                ->orWhere('email', 'LIKE', $keyWord);
        })
            ->when($this->departamento_filter, function ($query) {
                $query->where('departamento_id', $this->departamento_filter);
            })
            ->with([
                'carrerasComoDirector.carrera',
                'carrerasComoDirector.periodo',
                'carrerasComoApoyo.carrera',
                'carrerasComoApoyo.periodo',
                'asignacionesCalificadorGeneral.carreraPeriodo.carrera',
                'asignacionesCalificadorGeneral.carreraPeriodo.periodo',
                'miembrosTribunales.tribunal.carrerasPeriodo.carrera',
                'miembrosTribunales.tribunal.carrerasPeriodo.periodo'
            ])
            ->paginate($this->perPage);

        return view('livewire.users.view', compact('users'));
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->name = null;
        $this->lastname = null;
        $this->email = null;
        $this->password = null;
        $this->password_confirmation = null;
        $this->departamento_id = null;
    }

    public function store()
    {
        // Verificar permisos
        if (!$this->puedeGestionarUsuarios()) {
            session()->flash('error', 'No tienes permisos para crear usuarios.');
            return;
        }

        $this->validate();

        try {
            $user = new User();
            $user->name = $this->name;
            $user->lastname = $this->lastname;
            $user->email = $this->email;
            $user->password = Hash::make($this->password);
            $user->departamento_id = $this->departamento_id;
            $user->save();

            // Asignar el rol 'Docente' si el usuario es nuevo o no tiene roles
            if (!$user->hasAnyRole()) {
                $user->assignRole('Docente');
            }

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
            session()->flash('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarUsuarios()) {
            session()->flash('error', 'No tienes permisos para editar usuarios.');
            return;
        }

        return redirect()->route('users.profile', ['id' => encrypt($id)]);
    }

    public function update()
    {
        // Verificar permisos
        if (!$this->puedeGestionarUsuarios()) {
            session()->flash('error', 'No tienes permisos para actualizar usuarios.');
            return;
        }

        $this->validate([
            'name' => 'required',
            'email' => 'required',
        ]);

        if ($this->selected_id) {
            try {
                $record = User::find($this->selected_id);
                $record->assignRole('writer');

                $record->update([
                    'name' => $this->name,
                    'email' => $this->email
                ]);

                $this->resetInput();
                $this->dispatchBrowserEvent('closeModal');
                session()->flash('success', 'Usuario actualizado exitosamente.');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al actualizar el usuario: ' . $e->getMessage());
            }
        }
    }
    public function eliminar($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarUsuarios()) {
            session()->flash('error', 'No tienes permisos para eliminar usuarios.');
            return;
        }

        $this->usuarioFounded = User::find($id);
    }
    public function destroy($id)
    {
        // Verificar permisos
        if (!$this->puedeGestionarUsuarios()) {
            session()->flash('error', 'No tienes permisos para eliminar usuarios.');
            return;
        }

        if ($id) {
            try {
                // Verificar que no se elimine a sí mismo
                if ($id == auth()->id()) {
                    $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
                    session()->flash('error', 'No puedes eliminar tu propio usuario.');
                    return;
                }

                User::where('id', $id)->delete();
                session()->flash('success', 'Usuario eliminado exitosamente.');
                $this->resetInput();
                $this->usuarioFounded = null;
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            } catch (\Throwable $th) {
                $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
                $this->resetInput();
                $this->usuarioFounded = null;
                session()->flash('error', 'No se puede eliminar el usuario ya que tiene registros asociados.');
            }
        }
    }

    public function impersonate($id)
    {
        // $user = User::find($id);
        // Auth::user()->impersonate($user);
        // return redirect()->to('/home');
    }
    public function resetImport()
    {
        $this->archivoExcelProfesores = null;
        $this->importing = false;
        $this->importFinished = false;
        $this->importErrors = [];
    }

    public function importarProfesores()
    {
        // Verificar permisos
        if (!$this->puedeImportarProfesores()) {
            session()->flash('error', 'No tienes permisos para importar profesores.');
            return;
        }

        $this->validate([
            'archivoExcelProfesores' => 'required|file|mimes:xlsx,xls',
            'departamento_id' => 'required|exists:departamentos,id',
        ], [
            'archivoExcelProfesores.required' => 'Debe seleccionar un archivo.',
            'archivoExcelProfesores.mimes' => 'El archivo debe ser de tipo Excel (xlsx, xls).',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento seleccionado no es válido.',
        ]);

        $this->importing = true;
        $this->importFinished = false;
        $this->importErrors = [];

        try {
            // Pasar departamento_id al importador
            $import = new ProfesoresImport($this->departamento_id);
            Excel::import($import, $this->archivoExcelProfesores->getRealPath());

            if ($import->failures()->isNotEmpty()) {
                foreach ($import->failures() as $failure) {
                    $this->importErrors[] = "Error de validación en la fila {$failure->row()}: {$failure->errors()[0]} para '{$failure->attribute()}' con valor '{$failure->values()[$failure->attribute()]}'";
                }
                session()->flash('warning', 'La importación finalizó, pero algunas filas tenían errores y no se importaron.');
            } else {
                $departamentoMsg = $this->departamento_id ? ' al departamento seleccionado' : '';
                session()->flash('success', 'Importación de profesores completada exitosamente' . $departamentoMsg . '.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error durante la importación: ' . $e->getMessage());
        }

        $this->importing = false;
        $this->importFinished = true;
    }
}
