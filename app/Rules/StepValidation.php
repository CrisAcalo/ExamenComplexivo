<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StepValidation implements Rule
{
    protected $step;

    public function __construct($step)
    {
        $this->step = $step;
    }

    public function passes($attribute, $value)
    {
        // Verificar si el valor es un múltiplo del paso especificado
        return fmod($value, $this->step) == 0;
    }

    public function message()
    {
        return 'El valor debe ser un múltiplo de ' . $this->step;
    }
}
