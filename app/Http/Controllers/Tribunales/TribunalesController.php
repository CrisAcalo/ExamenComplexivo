<?php

namespace App\Http\Controllers\Tribunales;

use App\Helpers\ContextualAuth;
use App\Http\Controllers\Controller;
use App\Models\Tribunale;
use Illuminate\Http\Request;

class TribunalesController extends Controller
{
    public function index($id)
    {
        $carreraPeriodoId = $id;

        // Verificar acceso contextual al módulo de tribunales
        $user = auth()->user();

        // Super Admin y Administrador tienen acceso total
        if (!ContextualAuth::isSuperAdminOrAdmin($user)) {
            // Director y Docente de Apoyo solo de esta carrera-período específica
            if (!ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodoId)) {
                abort(403, 'No tienes permisos para acceder a este módulo de tribunales.');
            }
        }

        return view('livewire.tribunales.index', compact('carreraPeriodoId'));
    }

    public function componenteShow($id)
    {
        $componenteId = $id;
        return view('livewire.componentes.componente.index', compact('componenteId'));
    }

    public function profile($tribunalId)
    {
        $tribunal = Tribunale::with('carrerasPeriodo')->find($tribunalId);
        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }

        // Verificar acceso contextual al tribunal específico
        $user = auth()->user();

        // Super Admin y Administrador tienen acceso total
        if (!ContextualAuth::isSuperAdminOrAdmin($user)) {
            $carreraPeriodoId = $tribunal->carrera_periodo_id;

            // Verificar si es Director, Docente de Apoyo, miembro del tribunal o calificador general
            $puedeAcceder = ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodoId) ||
                           ContextualAuth::isMemberOfTribunal($user, $tribunalId) ||
                           ContextualAuth::isCalificadorGeneralOf($user, $carreraPeriodoId);

            if (!$puedeAcceder) {
                abort(403, 'No tienes permisos para acceder a este tribunal.');
            }
        }

        // Esta vista contendrá el componente Livewire 'tribunal-profile'
        return view('livewire.tribunales.profile.index', ['tribunalId' => $tribunalId]);
    }

    public function principal(){
        return view('livewire.tribunales.principal.index');
    }
    public function calificar($tribunalId){
        $tribunal = Tribunale::with('carrerasPeriodo')->find($tribunalId);
        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }

        // Verificar acceso contextual para calificar
        $user = auth()->user();

        // Usar el nuevo método integral de ContextualAuth
        if (!ContextualAuth::canCalifyInTribunal($user, $tribunal)) {
            abort(403, 'No tienes permisos para calificar en este tribunal.');
        }

        return view('livewire.tribunales.principal.calificar-index', compact('tribunalId'));
    }
}
