<?php

namespace App\Http\Controllers\Rubricas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Helpers\ContextualAuth;

class RubricaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->verificarAccesoRubricas();
            return $next($request);
        });
    }

    public function create(){
        return view('livewire.rubricas.create.index');
    }

    public function edit($id){
        return view('livewire.rubricas.create.index', compact('id'));
    }

    /**
     * Verificar acceso a rúbricas usando ContextualAuth
     */
    private function verificarAccesoRubricas()
    {
        $user = auth()->user();

        // Verificar si tiene permisos globales
        if (Gate::allows('ver rubricas') || Gate::allows('gestionar rubricas') || Gate::allows('gestionar plantillas rubricas')) {
            return true;
        }

        // Verificar si tiene asignaciones contextuales (Director o Docente de Apoyo)
        $userContext = ContextualAuth::getUserContextInfo($user);
        if ($userContext['carreras_director']->isNotEmpty() || $userContext['carreras_apoyo']->isNotEmpty()) {
            return true;
        }

        abort(403, 'No tienes permisos para acceder a las rúbricas.');
    }
}
