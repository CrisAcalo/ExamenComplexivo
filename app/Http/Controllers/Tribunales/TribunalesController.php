<?php

namespace App\Http\Controllers\Tribunales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TribunalesController extends Controller
{
    public function index($id)
    {
        $carreraPeriodoId = $id;
        return view('livewire.tribunales.index', compact('carreraPeriodoId'));
    }

    public function componenteShow($id)
    {
        $componenteId = $id;
        return view('livewire.componentes.componente.index', compact('componenteId'));
    }
}
