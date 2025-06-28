<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition()
    {
        // Generamos el ID de estudiante con un prefijo y aseguramos que sea único
        // Ejemplo: L00123456
        $idEstudiante = 'L' . fake()->unique()->numerify('00######');
        $nombre1 = fake()->firstName();
        $nombre2 = fake()->firstName();
        $apellido1 = fake()->lastName();
        $apellido2 = fake()->lastName();
        return [
            'nombres' => $nombre1.' '.$nombre2,
            'apellidos' => $apellido1.' '.$apellido2,
            // Cédula ecuatoriana (10 dígitos numéricos) única
            'cedula' => fake()->unique()->numerify('##########'),
            // Correo electrónico único
            'correo' => fake()->unique()->safeEmail(),
            // Teléfono (puede ser nulo)
            'telefono' => fake()->optional()->numerify('##########'),
            // El username se genera a partir del ID de estudiante para asegurar unicidad y un formato consistente
            'username' => function () use ($nombre1, $nombre2, $apellido1) {
                $baseUsername = strtolower(substr($nombre1, 0, 1) . substr($nombre2, 0, 1) . $apellido1);
                $username = $baseUsername;
                $counter = 1;
                while (Estudiante::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
                return $username;
            },
            // ID de estudiante con formato L00123456 único
            'ID_estudiante' => $idEstudiante,
        ];
    }
}
