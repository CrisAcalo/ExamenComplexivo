<?php

namespace App\Http\Controllers\Tribunales;

use App\Helpers\ContextualAuth;
use App\Http\Controllers\Controller;
use App\Models\Tribunale;
use App\Models\PlanEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    /*
     * MÉTODO DESACTIVADO - Ya no se usa
     * El botón de "Exportar Acta PDF" ahora redirige al perfil del tribunal
     * donde se genera el PDF completo con todas las calificaciones
     * utilizando el método exportarActa() de TribunalProfile
     */
    /*
    public function exportarActaDirecto($tribunalId)
    {
        $user = auth()->user();

        // Cargar tribunal con todas las relaciones necesarias
        $tribunal = Tribunale::with([
            'estudiante',
            'carrerasPeriodo.carrera',
            'carrerasPeriodo.periodo',
            'carrerasPeriodo.director',
            'miembrosTribunales.user'
        ])->find($tribunalId);

        if (!$tribunal) {
            abort(404, 'Tribunal no encontrado');
        }

        // Verificar que el usuario es presidente de este tribunal
        if (!Gate::allows('subir-acta-firmada-este-tribunal-como-presidente', $tribunal)) {
            abort(403, 'Solo el presidente del tribunal puede exportar el acta.');
        }

        // Verificar que el tribunal esté cerrado
        if ($tribunal->estado !== 'CERRADO') {
            return redirect()->back()->with('danger', 'El acta solo puede exportarse cuando el tribunal esté cerrado.');
        }

        try {
            // Cargar plan de evaluación
            $planEvaluacionActivo = PlanEvaluacion::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
                ->with([
                    'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica.criteriosComponente',
                    'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
                ])
                ->first();

            // Por ahora enviamos arrays vacíos - el PDF se generará con los datos básicos
            // Si necesitas las calificaciones completas, tendrás que replicar toda la lógica de TribunalProfile
            $resumenNotasCalculadas = [];
            $todasLasCalificacionesDelTribunal = [];
            $notaFinalCalculadaDelTribunal = 0;

            // Convertir logo a base64
            $logoPath = public_path('storage/logos/LOGO-ESPE_lg.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }

            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);

            $dompdf = new Dompdf($options);

            // Generar HTML desde vista
            $html = view('pdfs.acta-tribunal', compact(
                'tribunal',
                'planEvaluacionActivo',
                'resumenNotasCalculadas',
                'todasLasCalificacionesDelTribunal',
                'notaFinalCalculadaDelTribunal',
                'logoBase64'
            ))->render();

            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Generar nombre del archivo
            $nombreEstudiante = $tribunal->estudiante
                ? str_replace(' ', '_', $tribunal->estudiante->apellidos . '_' . $tribunal->estudiante->nombres)
                : 'tribunal_' . $tribunal->id;
            $fecha = $tribunal->fecha ? date('Y-m-d', strtotime($tribunal->fecha)) : date('Y-m-d');
            $nombreArchivo = "acta_tribunal_{$nombreEstudiante}_{$fecha}.pdf";

            // Retornar PDF como descarga directa
            return response()->streamDownload(function() use ($dompdf) {
                echo $dompdf->output();
            }, $nombreArchivo, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al exportar acta PDF directa: ' . $e->getMessage(), [
                'tribunal_id' => $tribunalId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('danger', 'Error al generar el acta: ' . $e->getMessage());
        }
    }
    */
}
