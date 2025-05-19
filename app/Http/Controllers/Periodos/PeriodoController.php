<?php

namespace App\Http\Controllers\Periodos;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Periodo;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function show($id)
    {
        $periodoId = $id;
        return view('livewire.periodos.profile.index', compact('periodoId'));
    }
}
