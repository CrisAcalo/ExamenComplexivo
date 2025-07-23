<?php

namespace App\Http\Controllers\Periodos;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Periodo;
use App\Helpers\ContextualAuth;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function show($id)
    {
        $user = auth()->user();

        // Verificar acceso al período específico
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            // Acceso completo para Super Admin y Administrador
            $periodoId = $id;
            return view('livewire.periodos.profile.index', compact('periodoId'));
        }

        // Director o Docente de Apoyo: verificar acceso contextual
        $canAccessAsDirector = ContextualAuth::getCarrerasAsDirector($user)
            ->where('periodo_id', $id)
            ->isNotEmpty();

        $canAccessAsApoyo = ContextualAuth::getCarrerasAsApoyo($user)
            ->where('periodo_id', $id)
            ->isNotEmpty();

        if ($canAccessAsDirector || $canAccessAsApoyo) {
            $periodoId = $id;
            return view('livewire.periodos.profile.index', compact('periodoId'));
        }

        // Sin acceso
        return redirect()->route('periodos.')->with('error', 'No tienes acceso a este período.');
    }
}
