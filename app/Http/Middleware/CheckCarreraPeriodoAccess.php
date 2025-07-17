<?php

namespace App\Http\Middleware;

use App\Models\CarrerasPeriodo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckCarreraPeriodoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $gatePrefix El prefijo del gate a verificar (ej: 'gestionar-estudiantes', 'gestionar-rubricas')
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $gatePrefix = null)
    {
        $user = auth()->user();

        // Super Admin siempre pasa
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Administrador siempre pasa para operaciones globales
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        // Para otros roles, necesitamos verificar acceso contextual
        $carreraPeriodoId = $this->extractCarreraPeriodoId($request);

        if ($carreraPeriodoId) {
            $carreraPeriodo = CarrerasPeriodo::find($carreraPeriodoId);

            if (!$carreraPeriodo) {
                abort(404, 'Carrera-Período no encontrado');
            }

            // Verificar acceso contextual según el gate especificado
            if ($gatePrefix) {
                $gateName = $gatePrefix . '-en-carrera-periodo';
                if (Gate::allows($gateName, $carreraPeriodo)) {
                    return $next($request);
                }
            } else {
                // Verificación genérica de acceso a la carrera-período
                if ($this->hasAccessToCarreraPeriodo($user, $carreraPeriodo)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'No tienes permisos para acceder a este recurso');
    }

    /**
     * Extraer el ID de carrera-período de la request
     */
    private function extractCarreraPeriodoId(Request $request)
    {
        // Buscar en parámetros de ruta
        if ($request->route('carreraPeriodoId')) {
            return $request->route('carreraPeriodoId');
        }

        if ($request->route('id')) {
            // Si es una ruta de período, el ID corresponde al período
            if ($request->route()->getName() === 'periodos.profile') {
                return $request->route('id');
            }
        }

        // Buscar en query parameters
        if ($request->query('carrera_periodo_id')) {
            return $request->query('carrera_periodo_id');
        }

        return null;
    }

    /**
     * Verificar si el usuario tiene acceso a esta carrera-período
     */
    private function hasAccessToCarreraPeriodo($user, CarrerasPeriodo $carreraPeriodo)
    {
        // Director de la carrera
        if ($user->hasRole('Director de Carrera') && $carreraPeriodo->director_id === $user->id) {
            return true;
        }

        // Docente de apoyo
        if ($user->hasRole('Docente de Apoyo') && $carreraPeriodo->docente_apoyo_id === $user->id) {
            return true;
        }

        return false;
    }
}
