<?php

namespace Database\Factories;

use App\Models\ComponentesEvaluacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComponentesEvaluacionFactory extends Factory
{
    protected $model = ComponentesEvaluacion::class;

    public function definition()
    {
        return [
			'carrera_periodo_id' => $this->faker->name,
			'nombre' => $this->faker->name,
			'ponderacion' => $this->faker->name,
        ];
    }
}
