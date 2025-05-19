<?php

namespace Database\Factories;

use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    public function definition()
    {
        return [
			'codigo_carrera' => $this->faker->name,
			'nombre' => $this->faker->name,
			'departamento' => $this->faker->name,
			'sede' => $this->faker->name,
        ];
    }
}
