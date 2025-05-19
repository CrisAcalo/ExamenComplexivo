<?php

namespace Database\Factories;

use App\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PeriodoFactory extends Factory
{
    protected $model = Periodo::class;

    public function definition()
    {
        return [
			'codigo_periodo' => $this->faker->name,
			'fecha_inicio' => $this->faker->name,
			'fecha_fin' => $this->faker->name,
        ];
    }
}
