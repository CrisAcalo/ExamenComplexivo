<?php

namespace App\Http\Controllers;

use App\Models\CarrerasPeriodo;
use Illuminate\Http\Request;

class PlanEvaluacionController extends Controller
{
    public function manage($carreraPeriodoId)
    {
        $carreraPeriodo = CarrerasPeriodo::find($carreraPeriodoId);
        if (!$carreraPeriodo) {
            abort(404, 'Carrera-Periodo no encontrado.');
        }

        return view('livewire.plan_evaluacion.index', ['carreraPeriodoId' => $carreraPeriodoId]);
    }
}
