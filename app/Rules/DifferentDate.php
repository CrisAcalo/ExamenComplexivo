<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DifferentDate implements Rule
{
    protected $comparisonDate;

    public function __construct($comparisonDate)
    {
        $this->comparisonDate = $comparisonDate;
    }

    public function passes($attribute, $value)
    {
        // Convertir las fechas a objetos Carbon para la comparación
        $valueDate = Carbon::parse($value);
        $comparisonDate = Carbon::parse($this->comparisonDate);

        // Verificar si las fechas son diferentes
        return $valueDate->notEqualTo($comparisonDate);
    }

    public function message()
    {
        $comparisonDate = Carbon::parse($this->comparisonDate);
        return 'La fecha de salida debe ser diferente a: ' . $comparisonDate->format('d/m/Y');
    }
}