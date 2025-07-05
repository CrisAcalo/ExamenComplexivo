<?php

namespace App\Http\Livewire;

use App\Imports\EstudiantesImport;
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
    public $perPage = 10; // NUEVO

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.estudiantes.view', [
            'estudiantes' => Estudiante::latest()
                ->where('nombres', 'LIKE', $keyWord)
                ->orWhere('apellidos', 'LIKE', $keyWord)
                ->orWhere('cedula', 'LIKE', $keyWord)
                ->orWhere('correo', 'LIKE', $keyWord)
                ->orWhere('telefono', 'LIKE', $keyWord)
                ->orWhere('username', 'LIKE', $keyWord)
                ->orWhere('ID_estudiante', 'LIKE', $keyWord)
                ->paginate($this->perPage),
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
        session()->flash('success', 'Estudiante Creado Exitosamente.');
    }

    public function edit($id)
    {
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
            session()->flash('success', 'Estudiante Actualizado Exitosamente.');
        }
    }

    public function eliminar($id)
    {
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
        if ($id) {
            Estudiante::where('id', $id)->delete();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
            session()->flash('success', 'Estudiante Eliminado Exitosamente.');
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
}
