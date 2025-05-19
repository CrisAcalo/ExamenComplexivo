<?php

namespace Database\Factories;

use App\Models\Tribunale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TribunaleFactory extends Factory
{
    protected $model = Tribunale::class;

    public function definition()
    {
        return [
			'carrera_periodo_id' => $this->faker->name,
			'estudiante_id' => $this->faker->name,
			'fecha' => $this->faker->name,
			'hora_inicio' => $this->faker->name,
			'hora_fin' => $this->faker->name,
        ];
    }
}
