<?php

namespace App\Http\Middleware;

use App\Models\Tribunale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckTribunalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $gateName El nombre del gate a verificar
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $gateName = null)
    {
        $user = auth()->user();

        // Extraer tribunal ID de la ruta
        $tribunalId = $request->route('tribunalId') ?? $request->route('id');

        if (!$tribunalId) {
            abort(404, 'Tribunal no especificado');
        }

        $tribunal = Tribunale::with('carrerasPeriodo')->find($tribunalId);

        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }

        // Verificar acceso usando el gate especificado
        if ($gateName && Gate::allows($gateName, $tribunal)) {
            return $next($request);
        }

        // Verificación de acceso genérica si no se especifica gate
        if (!$gateName) {
            // Super Admin siempre puede
            if ($user->hasRole('Super Admin')) {
                return $next($request);
            }

            // Administrador siempre puede
            if ($user->hasRole('Administrador')) {
                return $next($request);
            }

            // Verificar si es miembro del tribunal
            if ($tribunal->miembrosTribunales()->where('user_id', $user->id)->exists()) {
                return $next($request);
            }

            // Verificar si es Director/Apoyo de la carrera-período
            if ($tribunal->carrerasPeriodo) {
                if (($user->hasRole('Director de Carrera') && $tribunal->carrerasPeriodo->director_id === $user->id) ||
                    ($user->hasRole('Docente de Apoyo') && $tribunal->carrerasPeriodo->docente_apoyo_id === $user->id)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'No tienes permisos para acceder a este tribunal');
    }
}
