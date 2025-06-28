<?php

namespace App\Http\Controllers\Tribunales;

use App\Http\Controllers\Controller;
use App\Models\Tribunale;
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

    public function profile($tribunalId)
    {
        $tribunal = Tribunale::find($tribunalId);
        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }
        // Esta vista contendrá el componente Livewire 'tribunal-profile'
        return view('livewire.tribunales.profile.index', ['tribunalId' => $tribunalId]);
    }

    public function principal(){
        return view('livewire.tribunales.principal.index');
    }
    public function calificar($tribunalId){
        $tribunal = Tribunale::find($tribunalId);
        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }

        return view('livewire.tribunales.principal.calificar-index', compact('tribunalId'));
    }
}
