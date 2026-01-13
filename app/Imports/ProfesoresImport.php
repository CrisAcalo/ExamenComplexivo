<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection; // Usaremos ToCollection para más control
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class ProfesoresImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $departamento_id;

    /**
     * Constructor para recibir el departamento_id
     */
    public function __construct($departamento_id = null)
    {
        $this->departamento_id = $departamento_id;
    }

    public function collection(Collection $rows)
    {
        //dd($rows);
        foreach ($rows as $row) {

            // Verificar si la fila tiene datos esenciales antes de procesar
            if (
                // empty($row['id_espe']) ||
                empty($row['apellidos']) || empty($row['nombres']) || empty($row['cedula'])
            ) {
                continue; // Saltar fila si le faltan datos clave
            }

            // Usar updateOrCreate para evitar duplicados y actualizar si es necesario
            $user = User::updateOrCreate(
                [
                    // Columna única para encontrar al usuario
                    'email' => trim($row['correo']),
                ],
                [
                    // Datos a crear o actualizar
                    'name'      => trim($row['nombres']), // Solo nombres
                    'lastname'  => trim($row['apellidos']), // Solo apellidos
                    'username'  => $this->generarUsername($row['correo']),
                    'email'     => trim($row['correo']),
                    'cedula'    => trim($row['cedula']),
                    'password'  => Hash::make(trim($row['cedula'])), // La cédula como contraseña
                    'email_verified_at' => now(), // Marcar como verificado por defecto
                    'departamento_id' => $this->departamento_id, // Asignar departamento si fue proporcionado
                ]
            );

            // Asignar el rol 'Docente' si el usuario es nuevo o no tiene roles.
            if (!$user->hasAnyRole()) {
                $user->assignRole('Docente');
            }
        }
    }

    /**
     * Define la fila donde comienzan los encabezados.
     */
    public function headingRow(): int
    {
        return 6;
    }

    /**
     * Reglas de validación para cada fila del Excel.
     */
    public function rules(): array
    {
        return [
            // El '*' se aplica a cada fila
            //'id_espe' => ['required', 'string', 'unique:users,ID_espe'], // De momento comentado porque no hay información
            'cedula' => ['numeric', 'numeric', 'unique:users,cedula'], // Aseguramos que la cédula sea única
            'apellidos' => ['required', 'string'],
            'nombres' => ['required', 'string'],
            'correo' => ['required', 'email', 'unique:users,email'],
        ];
    }

    /**
     * Helper para generar un username a partir del correo.
     */
    private function generarUsername(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }
        return strstr($email, '@', true);
    }
}
