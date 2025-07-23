<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ContextualAuth;

class CheckCarreraPeriodoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // Obtener carrera_periodo_id de la ruta o parámetros
        $carreraPeriodoId = $request->route('carreraPeriodoId') ??
                           $request->route('carrera_periodo_id') ??
                           $request->get('carrera_periodo_id');

        if (!$carreraPeriodoId) {
            abort(400, 'Carrera-período no especificada');
        }

        $user = auth()->user();

        // Verificar acceso básico a la carrera-período
        if (!ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodoId)) {
            abort(403, 'No tienes acceso a esta carrera-período');
        }

        // Verificar permiso específico si se proporciona
        if ($permission && !$this->hasContextualPermission($user, $carreraPeriodoId, $permission)) {
            abort(403, 'No tienes permisos para realizar esta acción en esta carrera-período');
        }

        return $next($request);
    }

    /**
     * Verifica permisos contextuales específicos
     */
    private function hasContextualPermission($user, $carreraPeriodoId, $permission): bool
    {
        switch ($permission) {
            case 'manage-estudiantes':
                return ContextualAuth::canManageEstudiantesInCarreraPeriodo($user, $carreraPeriodoId);

            case 'manage-rubricas':
                return ContextualAuth::canManageRubricasInCarreraPeriodo($user, $carreraPeriodoId);

            case 'manage-tribunales':
                return ContextualAuth::canManageTribunalesInCarreraPeriodo($user, $carreraPeriodoId);

            case 'view-only':
                // Solo necesita acceso básico a la carrera-período
                return true;

            default:
                return false;
        }
    }
}
