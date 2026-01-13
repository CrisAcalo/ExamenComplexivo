<?php

namespace App\Imports;

use App\Models\Estudiante;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para usar nombres de columna
use Maatwebsite\Excel\Concerns\WithValidation; // Para validar filas
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;    // Para saltar filas con errores
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class EstudiantesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use Importable, SkipsFailures;

    protected $carrera_periodo_id;

    /**
     * Constructor para recibir el carrera_periodo_id
     */
    public function __construct($carrera_periodo_id)
    {
        $this->carrera_periodo_id = $carrera_periodo_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // ----- PASO DE DEPURACIÓN -----
        // Descomenta esta línea para ver la primera fila que procesa.
        // Después de ver el resultado, coméntala de nuevo.
        // dd($row);
        // -----------------------------

        // Claves que esperamos (después de la conversión de la librería)
        // Ajusta estas claves según lo que veas en el dd($row)
        $keyIdEspe = 'id_espe';
        $keyCedula = 'cedula';
        $keyApellidos = 'apellidos';
        $keyNombres = 'nombres';
        $keyCorreo = 'correo';

        // Verifica que la fila tenga los datos esenciales. Si no, la saltamos.
        if (
            empty($row[$keyIdEspe]) &&
            empty($row[$keyApellidos]) &&
            empty($row[$keyNombres]) &&
            empty($row[$keyCedula]) &&
            empty($row[$keyCorreo])
        ) {
            return null; // Salta la fila si está completamente vacía
        }

        if (empty($row[$keyIdEspe])) {
            return null; // No procesar filas sin ID ESPE
        }

        return Estudiante::updateOrCreate(
            [
                'ID_estudiante' => trim($row[$keyIdEspe]), // Usar trim para limpiar espacios
                'carrera_periodo_id' => $this->carrera_periodo_id, // Incluir periodo en la búsqueda
            ],
            [
                'nombres'       => trim($row[$keyNombres]),
                'apellidos'     => trim($row[$keyApellidos]),
                'cedula'        => trim($row[$keyCedula]),
                'correo'        => trim($row[$keyCorreo]),
                'username'      => $this->generarUsername($row[$keyCorreo] ?? null),
                'telefono'      => null,
            ]
        );
    }

    /**
     * Define la fila donde comienzan los encabezados.
     * En tu caso, parece ser la fila 6.
     */
    public function headingRow(): int
    {
        return 6;
    }

    /**
     * Reglas de validación para cada fila del Excel.
     * Las reglas de unicidad ahora consideran el periodo académico.
     */
    public function rules(): array
    {
        return [
            'id_espe' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = Estudiante::where('ID_estudiante', $value)
                        ->where('carrera_periodo_id', $this->carrera_periodo_id)
                        ->exists();
                    if ($exists) {
                        $fail("El ID ESPE {$value} ya existe en este periodo académico.");
                    }
                }
            ],
            'cedula' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = Estudiante::where('cedula', $value)
                        ->where('carrera_periodo_id', $this->carrera_periodo_id)
                        ->exists();
                    if ($exists) {
                        $fail("La cédula {$value} ya existe en este periodo académico.");
                    }
                }
            ],
            'apellidos' => ['required', 'string'],
            'nombres' => ['required', 'string'],
            'correo' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $exists = Estudiante::where('correo', $value)
                        ->where('carrera_periodo_id', $this->carrera_periodo_id)
                        ->exists();
                    if ($exists) {
                        $fail("El correo {$value} ya existe en este periodo académico.");
                    }
                }
            ],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    public function customValidationMessages()
    {
        return [
            'id_espe.required' => 'La columna ID ESPE es obligatoria.',
            'id_espe.unique' => 'El ID ESPE :input ya existe en la base de datos.',
            'cedula.required' => 'La columna CÉDULA es obligatoria.',
            'cedula.unique' => 'La cédula :input ya existe en la base de datos.',
            'correo.required' => 'La columna CORREO es obligatoria.',
            'correo.email' => 'El valor en la columna CORREO no es un email válido.',
            'correo.unique' => 'El correo :input ya existe en la base de datos.',
        ];
    }

    /**
     * Helper para generar un username a partir del correo.
     * ej. 'jnnarvaez@espe.edu.ec' -> 'jnnarvaez'
     */
    private function generarUsername(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }
        return strstr($email, '@', true); // Obtiene la parte del string antes del '@'
    }
}
