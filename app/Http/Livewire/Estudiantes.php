<?php

namespace App\Http\Livewire;

use App\Imports\EstudiantesImport;
use App\Models\CarrerasPeriodo;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Estudiante;
use Maatwebsite\Excel\Facades\Excel;

class Estudiantes extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $nombres, $apellidos, $cedula, $correo, $telefono, $username, $ID_estudiante, $founded;
    public $archivoExcel;
    public $importErrors = [];
    public $importing = false;
    public $importFinished = false;
    public $perPage = 10;
    public $carrerasPeriodosAccesibles = [];

    public function mount()
    {
        // Verificar autorización básica
        if (!$this->verificarAccesoEstudiantes()) {
            abort(403, 'No tienes permisos para gestionar estudiantes.');
        }

        // Cargar carreras-períodos accesibles para el usuario
        $this->cargarCarrerasPeriodosAccesibles();
    }

    /**
     * Verificar si el usuario puede acceder al módulo de estudiantes
     */
    private function verificarAccesoEstudiantes()
    {
        $user = auth()->user();

        return $user->hasPermissionTo('gestionar estudiantes') ||
               $user->hasPermissionTo('ver listado estudiantes') ||
               $user->hasPermissionTo('importar estudiantes') ||
               $user->hasPermissionTo('exportar estudiantes');
    }

    /**
     * Cargar las carreras-períodos a las que el usuario tiene acceso
     */
    private function cargarCarrerasPeriodosAccesibles()
    {
        $user = auth()->user();

        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            // Super Admin y Administrador pueden ver todos
            $this->carrerasPeriodosAccesibles = CarrerasPeriodo::all()->pluck('id')->toArray();
        } else {
            // Director y Docente de Apoyo solo ven sus carreras-períodos asignados
            $this->carrerasPeriodosAccesibles = CarrerasPeriodo::where(function($query) use ($user) {
                $query->where('director_id', $user->id)
                      ->orWhere('docente_apoyo_id', $user->id);
            })->pluck('id')->toArray();
        }
    }

    public function render()
    {
        // Verificar autorización en cada render
        if (!$this->verificarAccesoEstudiantes()) {
            abort(403, 'No tienes permisos para gestionar estudiantes.');
        }

        $keyWord = '%' . $this->keyWord . '%';

        // Filtrar estudiantes según las carreras-períodos accesibles
        $query = Estudiante::latest()
            ->where(function($q) use ($keyWord) {
                $q->where('nombres', 'LIKE', $keyWord)
                  ->orWhere('apellidos', 'LIKE', $keyWord)
                  ->orWhere('cedula', 'LIKE', $keyWord)
                  ->orWhere('correo', 'LIKE', $keyWord)
                  ->orWhere('telefono', 'LIKE', $keyWord)
                  ->orWhere('username', 'LIKE', $keyWord)
                  ->orWhere('ID_estudiante', 'LIKE', $keyWord);
            });

        // Si no es Super Admin o Administrador, filtrar por carreras-períodos accesibles
        if (!auth()->user()->hasRole(['Super Admin', 'Administrador'])) {
            if (empty($this->carrerasPeriodosAccesibles)) {
                // Si no tiene carreras-períodos asignados, no ve ningún estudiante
                $query->whereRaw('1 = 0');
            } else {
                // Aquí deberíamos filtrar por la relación con carreras-períodos
                // Por ahora, asumimos que todos los estudiantes son visibles para Director/Apoyo
                // En el futuro, podrías agregar una relación estudiante-carrera-periodo
            }
        }

        return view('livewire.estudiantes.view', [
            'estudiantes' => $query->paginate($this->perPage),
        ]);
    }

    public function rules() // Renombrar el método a rulesForCRUD o similar para claridad
    {
        $rules = [
            'nombres' => 'required',
            'apellidos' => 'required',
            'cedula' => 'required|unique:estudiantes,cedula,' . $this->selected_id,
            'correo' => 'required|email|unique:estudiantes,correo,' . $this->selected_id,
            'telefono' => 'nullable',
            'username' => 'required|unique:estudiantes,username,' . $this->selected_id,
            'ID_estudiante' => 'required|unique:estudiantes,ID_estudiante,' . $this->selected_id,
        ];

        return $rules;
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->nombres = null;
        $this->apellidos = null;
        $this->cedula = null;
        $this->correo = null;
        $this->telefono = null;
        $this->username = null;
        $this->ID_estudiante = null;
    }

    public function store()
    {
        // Verificar autorización para crear estudiantes
        if (!auth()->user()->hasPermissionTo('gestionar estudiantes')) {
            session()->flash('error', 'No tienes permisos para crear estudiantes.');
            return;
        }

        $this->validate([
            'nombres' => 'required',
            'apellidos' => 'required',
            'cedula' => 'required|unique:estudiantes,cedula',
            'correo' => 'required|email|unique:estudiantes,correo',
            'telefono' => 'nullable',
            'username' => 'required|unique:estudiantes,username',
            'ID_estudiante' => 'required|unique:estudiantes,ID_estudiante',
        ]);

        Estudiante::create([
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'cedula' => $this->cedula,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'username' => $this->username,
            'ID_estudiante' => $this->ID_estudiante
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Estudiante creado exitosamente.');
    }

    public function edit($id)
    {
        // Verificar autorización para editar estudiantes
        if (!auth()->user()->hasPermissionTo('gestionar estudiantes')) {
            session()->flash('error', 'No tienes permisos para editar estudiantes.');
            return;
        }

        $record = Estudiante::findOrFail($id);
        $this->selected_id = $id;
        $this->nombres = $record->nombres;
        $this->apellidos = $record->apellidos;
        $this->cedula = $record->cedula;
        $this->correo = $record->correo;
        $this->telefono = $record->telefono;
        $this->username = $record->username;
        $this->ID_estudiante = $record->ID_estudiante;
    }

    public function update()
    {
        // Verificar autorización para actualizar estudiantes
        if (!auth()->user()->hasPermissionTo('gestionar estudiantes')) {
            session()->flash('error', 'No tienes permisos para actualizar estudiantes.');
            return;
        }

        $this->validate([
            'nombres' => 'required',
            'apellidos' => 'required',
            'cedula' => 'required|unique:estudiantes,cedula,' . $this->selected_id,
            'correo' => 'required|email|unique:estudiantes,correo,' . $this->selected_id,
            'telefono' => 'nullable',
            'username' => 'required|unique:estudiantes,username,' . $this->selected_id,
            'ID_estudiante' => 'required|unique:estudiantes,ID_estudiante,' . $this->selected_id,
        ]);

        if ($this->selected_id) {
            $record = Estudiante::find($this->selected_id);
            $record->update([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'cedula' => $this->cedula,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'username' => $this->username,
                'ID_estudiante' => $this->ID_estudiante
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);
            session()->flash('success', 'Estudiante actualizado exitosamente.');
        }
    }

    public function eliminar($id)
    {
        // Verificar autorización para eliminar estudiantes
        if (!auth()->user()->hasPermissionTo('gestionar estudiantes')) {
            session()->flash('error', 'No tienes permisos para eliminar estudiantes.');
            return;
        }

        $this->founded = Estudiante::find($id);
        if ($this->founded && method_exists($this->founded, 'tribunales') && $this->founded->tribunales->count() > 0) {
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('danger', 'No se puede eliminar el estudiante porque tiene tribunales asociados.');
            $this->founded = null;
            return;
        }
        $this->dispatchBrowserEvent('openModalByName', ['modalName' => 'deleteDataModal']);
    }

    public function destroy($id)
    {
        // Verificar autorización para eliminar estudiantes
        if (!auth()->user()->hasPermissionTo('gestionar estudiantes')) {
            session()->flash('error', 'No tienes permisos para eliminar estudiantes.');
            return;
        }

        if ($id) {
            Estudiante::where('id', $id)->delete();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('success', 'Estudiante eliminado exitosamente.');
            $this->founded = null;
        }
    }

    public function resetImport()
    {
        $this->archivoExcel = null;
        $this->importing = false;
        $this->importFinished = false;
        $this->importErrors = [];
    }

    public function importarEstudiantes()
    {
        // Verificar autorización para importar estudiantes
        if (!auth()->user()->hasPermissionTo('importar estudiantes')) {
            session()->flash('error', 'No tienes permisos para importar estudiantes.');
            return;
        }

        $this->validate([
            'archivoExcel' => 'required|file|mimes:xlsx,xls'
        ]);

        $this->importing = true;
        $this->importFinished = false;
        $this->importErrors = [];

        $import = new EstudiantesImport();

        try {
            Excel::import($import, $this->archivoExcel->getRealPath());

            // Si llegamos aquí sin excepción, la importación se completó.
            // Ahora verificamos los fallos de validación.
            if ($import->failures()->isNotEmpty()) {
                foreach ($import->failures() as $failure) {
                    $this->importErrors[] = "Error en la fila {$failure->row()}: {$failure->errors()[0]} para el atributo '{$failure->attribute()}' con valor '{$failure->values()[$failure->attribute()]}'";
                }
                // Mensaje de éxito parcial
                session()->flash('warning', 'Importación completada, pero algunas filas no se importaron debido a errores de validación.');
            } else {
                // Mensaje de éxito total
                session()->flash('success', '¡Todas las filas se importaron exitosamente!');
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $this->importErrors[] = "Error en la fila {$failure->row()}: {$failure->errors()[0]} para el atributo '{$failure->attribute()}' con valor '{$failure->values()[$failure->attribute()]}'";
            }
            session()->flash('danger', 'La importación falló debido a errores de validación.');
        } catch (\Exception $e) {
            session()->flash('danger', 'Ocurrió un error inesperado durante la importación. Por favor, verifique el formato del archivo y los datos. Error: ' . Str::limit($e->getMessage(), 150));
        }

        $this->importing = false;
        $this->importFinished = true;
    }

    /**
     * Verificar si el usuario puede realizar operaciones de gestión completa
     */
    public function puedeGestionarEstudiantes()
    {
        return auth()->user()->hasPermissionTo('gestionar estudiantes');
    }

    /**
     * Verificar si el usuario puede importar estudiantes
     */
    public function puedeImportarEstudiantes()
    {
        return auth()->user()->hasPermissionTo('importar estudiantes');
    }

    /**
     * Verificar si el usuario puede exportar estudiantes
     */
    public function puedeExportarEstudiantes()
    {
        return auth()->user()->hasPermissionTo('exportar estudiantes');
    }
}
