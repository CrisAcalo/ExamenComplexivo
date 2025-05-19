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
        return [
			'nombres' => $this->faker->name,
			'apellidos' => $this->faker->name,
			'ID_estudiante' => $this->faker->name,
        ];
    }
}
