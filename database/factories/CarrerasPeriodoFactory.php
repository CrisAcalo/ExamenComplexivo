<?php

namespace Database\Factories;

use App\Models\CarrerasPeriodo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CarrerasPeriodoFactory extends Factory
{
    protected $model = CarrerasPeriodo::class;

    public function definition()
    {
        return [
			'carrera_id' => $this->faker->name,
			'periodo_id' => $this->faker->name,
			'docente_apoyo_id' => $this->faker->name,
			'director_id' => $this->faker->name,
        ];
    }
}
