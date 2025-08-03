<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
use App\Models\MiembrosTribunal;
use App\Models\PlanEvaluacion;
use App\Models\MiembroCalificacion;
use App\Models\CalificadorGeneralCarreraPeriodo;
use App\Models\Tribunale;
use App\Models\CarrerasPeriodo;
use App\Models\User;
use App\Models\CalificacionCriterio;
use App\Models\ComponenteRubrica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Dompdf\Dompdf;
use Dompdf\Options;

class TribunalesPrincipal extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';
    public $filtroEstado = 'PENDIENTES'; // Opciones: PENDIENTES, COMPLETADOS, TODOS
    public $tribunalesActuales;

    public function mount()
    {
        // No es necesario cargar todos los tribunales aquí si se hace en render
    }

    /**
     * Exportar el acta de un tribunal específico
     */
    public function exportarActaTribunal($tribunalId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                session()->flash('danger', 'Usuario no autenticado.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Cargar tribunal con todas las relaciones necesarias
            $tribunal = Tribunale::with([
                'estudiante',
                'carrerasPeriodo.carrera',
                'carrerasPeriodo.periodo',
                'carrerasPeriodo.director',
                'carrerasPeriodo.docenteApoyo',
                'miembrosTribunales.user',
                'logs.user'
            ])->find($tribunalId);

            if (!$tribunal) {
                session()->flash('danger', 'Tribunal no encontrado.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el usuario tenga permisos para exportar el acta
            if (!ContextualAuth::canCalifyInTribunal($user, $tribunal)) {
                session()->flash('danger', 'No tienes permisos para exportar el acta de este tribunal.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el tribunal esté cerrado
            if ($tribunal->estado !== 'CERRADO') {
                session()->flash('warning', 'Solo se puede exportar el acta de tribunales cerrados.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Cargar plan de evaluación activo
            $planEvaluacionActivo = null;
            if ($tribunal->carrerasPeriodo) {
                $planEvaluacionActivo = PlanEvaluacion::with([
                    'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica.criteriosComponente.calificacionesCriterio',
                    'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
                ])
                    ->where('carrera_periodo_id', $tribunal->carrera_periodo_id)
                    ->first();
            }

            // Calcular calificaciones para el PDF
            $resumenNotasCalculadas = [];
            $todasLasCalificacionesDelTribunal = [];
            $notaFinalCalculadaDelTribunal = 0;

            if ($planEvaluacionActivo) {
                $datosCalculados = $this->calcularCalificacionesParaPDF($tribunal, $planEvaluacionActivo);
                $resumenNotasCalculadas = $datosCalculados['resumen'];
                $todasLasCalificacionesDelTribunal = $datosCalculados['detalle'];
                $notaFinalCalculadaDelTribunal = $datosCalculados['notaFinal'];
            }

            // Convertir logo a base64 para que funcione en PDF
            $logoPath = public_path('storage/logos/LOGO-ESPE_500.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }

            // Generar el PDF usando DomPDF
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('debugPng', false);
            $options->set('debugKeepTemp', false);
            $options->set('debugCss', false);

            $dompdf = new Dompdf($options);

            try {
                // Renderizar la vista como HTML
                $html = view('pdfs.acta-tribunal', compact(
                    'tribunal',
                    'planEvaluacionActivo',
                    'resumenNotasCalculadas',
                    'todasLasCalificacionesDelTribunal',
                    'notaFinalCalculadaDelTribunal',
                    'logoBase64'
                ))->render();

                // Limpiar el HTML de caracteres problemáticos
                $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
            } catch (\Exception $renderError) {
                throw new \Exception('Error al renderizar el PDF: ' . $renderError->getMessage());
            }

            // Generar nombre del archivo
            $nombreEstudiante = $tribunal->estudiante->nombres_completos_id ?? 'Estudiante';
            $nombreEstudiante = Str::slug($nombreEstudiante, '_');
            $fecha = $tribunal->fecha ? date('Y-m-d', strtotime($tribunal->fecha)) : date('Y-m-d');
            $nombreArchivo = "acta_tribunal_{$nombreEstudiante}_{$fecha}.pdf";

            // Guardar temporalmente el PDF
            $pdfContent = $dompdf->output();
            $tempPath = storage_path('app/temp/' . $nombreArchivo);

            // Crear directorio si no existe
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            file_put_contents($tempPath, $pdfContent);

            // Mostrar mensaje de éxito y enviar evento para descargar
            session()->flash('info', 'Acta generada exitosamente. Descargando...');
            $this->dispatchBrowserEvent('showFlashMessage');
            $this->dispatchBrowserEvent('downloadFile', ['path' => $nombreArchivo]);
        } catch (\Exception $e) {
            session()->flash('danger', 'Error al generar el acta: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
            Log::error('Error al exportar acta del tribunal: ' . $e->getMessage(), [
                'tribunal_id' => $tribunalId,
                'usuario_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Calcula las calificaciones para generar el PDF del acta
     */
    private function calcularCalificacionesParaPDF($tribunal, $planEvaluacionActivo)
    {
        $resumenNotasCalculadas = [];
        $todasLasCalificacionesDelTribunal = [];
        $notaFinalCalculadaDelTribunal = 0;
        $sumaPonderacionesGlobalesItems = 0;

        if (!$planEvaluacionActivo || !$tribunal) {
            return [
                'resumen' => $resumenNotasCalculadas,
                'detalle' => $todasLasCalificacionesDelTribunal,
                'notaFinal' => $notaFinalCalculadaDelTribunal
            ];
        }

        $miembrosDelTribunal = $tribunal->miembrosTribunales;
        $idsMiembrosDelTribunal = $miembrosDelTribunal->pluck('id')->all();

        // Calificadores generales del carrera_periodo
        $calificadoresGeneralesUsers = $tribunal->carrerasPeriodo->docentesCalificadoresGenerales ?? collect();
        $idsCalificadoresGenerales = $calificadoresGeneralesUsers->pluck('id')->all();

        // Director y Apoyo IDs
        $directorId = $tribunal->carrerasPeriodo->director_id;
        $apoyoId = $tribunal->carrerasPeriodo->docente_apoyo_id;

        // Obtener todas las calificaciones para este tribunal de una vez
        $todasLasMiembroCalificacion = MiembroCalificacion::where('tribunal_id', $tribunal->id)
            ->with(['itemPlanEvaluacion', 'userCalificador', 'criterioCalificado', 'opcionCalificacionElegida'])
            ->get();

        // Agrupar las calificaciones primero por item_plan_evaluacion_id y luego por user_id
        $calificacionesAgrupadasPorItem = $todasLasMiembroCalificacion->groupBy('item_plan_evaluacion_id');
        $calificacionesAgrupadasPorItemYUsuario = $calificacionesAgrupadasPorItem->map(function ($calificacionesDelItem) {
            return $calificacionesDelItem->groupBy('user_id');
        });

        foreach ($planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $calificacionesParaEsteItem = $todasLasMiembroCalificacion->where('item_plan_evaluacion_id', $itemPlan->id);
            $notaFinalItemCalculada = 0;
            $sumaPonderacionesGlobalesItems += $itemPlan->ponderacion_global;

            // Inicializar resumen para este item
            $resumenNotasCalculadas[$itemPlan->id] = [
                'nombre_item_plan' => $itemPlan->nombre_item,
                'tipo_item' => $itemPlan->tipo_item,
                'ponderacion_global' => $itemPlan->ponderacion_global,
                'rubrica_plantilla_nombre' => ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) ? $itemPlan->rubricaPlantilla->nombre : null,
                'nota_tribunal_sobre_20' => 0,
                'puntaje_ponderado_item' => 0,
                'observacion_general' => ''
            ];

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // Buscar la calificación del Director o Apoyo para este ítem de nota directa
                $califNotaDirecta = $calificacionesParaEsteItem
                    ->whereIn('user_id', array_filter([$directorId, $apoyoId])) // Solo de Director o Apoyo
                    ->whereNull('criterio_id')
                    ->first();

                if ($califNotaDirecta && is_numeric($califNotaDirecta->nota_obtenida_directa)) {
                    $notaFinalItemCalculada = (float) $califNotaDirecta->nota_obtenida_directa;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                $notasRubricaPorGrupoCalificador = []; // [calificado_por_value => [componente_id => nota]]

                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    $componenteRubrica = $itemPlan->rubricaPlantilla->componentesRubrica->find($asignacion->componente_rubrica_id);
                    if (!$componenteRubrica) continue;

                    $grupoCalificadorResponsable = $asignacion->calificado_por;
                    $idsUsuariosDeEsteGrupo = [];

                    if ($grupoCalificadorResponsable === 'MIEMBROS_TRIBUNAL') {
                        $idsUsuariosDeEsteGrupo = $miembrosDelTribunal->pluck('user_id')->all();
                    } elseif ($grupoCalificadorResponsable === 'CALIFICADORES_GENERALES') {
                        $idsUsuariosDeEsteGrupo = $idsCalificadoresGenerales;
                    } elseif ($grupoCalificadorResponsable === 'DIRECTOR_CARRERA') {
                        $idsUsuariosDeEsteGrupo = [$directorId];
                    } elseif ($grupoCalificadorResponsable === 'DOCENTE_APOYO') {
                        $idsUsuariosDeEsteGrupo = [$apoyoId];
                    }

                    $idsUsuariosDeEsteGrupo = array_filter($idsUsuariosDeEsteGrupo); // Eliminar nulls

                    $sumaNotasComponenteEsteGrupo = 0;
                    $conteoNotasComponenteEsteGrupo = 0;

                    foreach ($idsUsuariosDeEsteGrupo as $userIdCalificador) {
                        $calificacionesDelUsuarioParaItem = $calificacionesParaEsteItem->where('user_id', $userIdCalificador);

                        $notaComponenteParaUsuario = $this->calcularNotaComponenteParaUsuario($componenteRubrica, $calificacionesDelUsuarioParaItem);
                        if (is_numeric($notaComponenteParaUsuario)) {
                            $sumaNotasComponenteEsteGrupo += $notaComponenteParaUsuario; // Ya viene ponderado por el componente
                            $conteoNotasComponenteEsteGrupo++;
                        }
                    }

                    if ($conteoNotasComponenteEsteGrupo > 0) {
                        $promedioNotaComponenteEsteGrupo = $sumaNotasComponenteEsteGrupo / $conteoNotasComponenteEsteGrupo;
                        if (!isset($notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable])) {
                            $notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable] = [];
                        }
                        $notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable][$componenteRubrica->id] = $promedioNotaComponenteEsteGrupo;
                    }
                }

                // Calcular la nota final de la rúbrica (sobre 20)
                $sumaPuntajesPonderadosComponentes = 0;
                $sumaPonderacionesDeComponentesUsados = 0;

                if ($itemPlan->rubricaPlantilla && $itemPlan->rubricaPlantilla->componentesRubrica) {
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $compR) {
                        $asignacionComp = $itemPlan->asignacionesCalificadorComponentes->firstWhere('componente_rubrica_id', $compR->id);
                        if ($asignacionComp && isset($notasRubricaPorGrupoCalificador[$asignacionComp->calificado_por][$compR->id])) {
                            $sumaPuntajesPonderadosComponentes += $notasRubricaPorGrupoCalificador[$asignacionComp->calificado_por][$compR->id];
                            $sumaPonderacionesDeComponentesUsados += $compR->ponderacion;
                        }
                    }
                }

                if ($sumaPonderacionesDeComponentesUsados > 0) {
                    // Normalizar a escala 0-100 y luego a 0-20
                    $notaRubricaBase100 = ($sumaPuntajesPonderadosComponentes / $sumaPonderacionesDeComponentesUsados) * 100;
                    $notaFinalItemCalculada = ($notaRubricaBase100 / 100) * 20;
                }
            }

            $resumenNotasCalculadas[$itemPlan->id]['nota_tribunal_sobre_20'] = $notaFinalItemCalculada;
            $resumenNotasCalculadas[$itemPlan->id]['puntaje_ponderado_item'] =
                ($notaFinalItemCalculada * $itemPlan->ponderacion_global) / 100;

            $notaFinalCalculadaDelTribunal += $resumenNotasCalculadas[$itemPlan->id]['puntaje_ponderado_item'];
        }

        // Crear detalle para el modal (simplificado para PDF)
        $todosLosCalificadoresRelevantesUsers = collect();
        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->merge(
            $miembrosDelTribunal->map(fn($mt) => $mt->user->setAttribute('rol_evaluador', $mt->status))
        );
        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->merge(
            $calificadoresGeneralesUsers->map(fn($u) => $u->setAttribute('rol_evaluador', 'CALIFICADOR_GENERAL'))
        );

        if ($directorId) {
            $director = User::find($directorId);
            if ($director) {
                $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->push(
                    $director->setAttribute('rol_evaluador', 'DIRECTOR_CARRERA')
                );
            }
        }

        if ($apoyoId) {
            $apoyo = User::find($apoyoId);
            if ($apoyo) {
                $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->push(
                    $apoyo->setAttribute('rol_evaluador', 'DOCENTE_APOYO')
                );
            }
        }

        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->filter()->unique('id');

        foreach ($todosLosCalificadoresRelevantesUsers as $calificadorUser) {
            $todasLasCalificacionesDelTribunal[$calificadorUser->id] = [
                'usuario' => $calificadorUser,
                'rol_evaluador' => $calificadorUser->rol_evaluador,
                'calificaciones_por_item' => []
            ];

            foreach ($planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                $calificacionDelUsuarioParaItem = $todasLasMiembroCalificacion
                    ->where('item_plan_evaluacion_id', $itemPlan->id)
                    ->where('user_id', $calificadorUser->id)
                    ->first();

                $todasLasCalificacionesDelTribunal[$calificadorUser->id]['calificaciones_por_item'][$itemPlan->id] =
                    $calificacionDelUsuarioParaItem;
            }
        }

        return [
            'resumen' => $resumenNotasCalculadas,
            'detalle' => $todasLasCalificacionesDelTribunal,
            'notaFinal' => $notaFinalCalculadaDelTribunal
        ];
    }

    /**
     * Calcula la nota de un componente específico para un usuario
     */
    private function calcularNotaComponenteParaUsuario(ComponenteRubrica $componenteR, $calificacionesDelUsuario)
    {
        $puntajeObtenidoCriterios = 0;
        $maxPuntajePosibleCriterios = 0;
        $criteriosCalificadosCount = 0;

        foreach ($componenteR->criteriosComponente as $criterioR) {
            // Calcular el puntaje máximo posible para este criterio
            if ($criterioR->calificacionesCriterio->isNotEmpty()) {
                $maxValorCriterio = $criterioR->calificacionesCriterio->max('valor');
                if (is_numeric($maxValorCriterio)) {
                    $maxPuntajePosibleCriterios += (float) $maxValorCriterio;
                }
            }

            // Buscar la calificación para este criterio específico usando criterio_id
            $calificacionCriterio = $calificacionesDelUsuario
                ->where('criterio_id', $criterioR->id)
                ->first();

            if ($calificacionCriterio && $calificacionCriterio->opcionCalificacionElegida) {
                $opcionElegida = $calificacionCriterio->opcionCalificacionElegida;
                if (is_numeric($opcionElegida->valor)) {
                    $puntajeObtenidoCriterios += (float) $opcionElegida->valor;
                    $criteriosCalificadosCount++;
                }
            }
        }

        if ($maxPuntajePosibleCriterios > 0 && $criteriosCalificadosCount === $componenteR->criteriosComponente->count()) {
            // Normalizar el puntaje del componente a una escala de 0 a ponderacion del componente
            return ($puntajeObtenidoCriterios / $maxPuntajePosibleCriterios) * $componenteR->ponderacion;
        }

        return null;
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user) {
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(),
                'mensajeNoAutorizado' => 'Usuario no autenticado.'
            ]);
        }

        // Obtener todos los tribunales que el usuario debe calificar
        // Esto incluye tanto usuarios regulares como híbridos (Admin + Director/Apoyo/etc.)
        $tribunalesParaCalificar = $this->obtenerTodosLosTribunalesDelUsuario($user);

        // Si es Admin/SuperAdmin puro (sin asignaciones contextuales), mostrar mensaje informativo
        if (ContextualAuth::isSuperAdminOrAdmin($user) && $tribunalesParaCalificar->isEmpty()) {
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(),
                'mensajeNoAutorizado' => 'Como administrador, no tienes tribunales asignados específicos. Usa las vistas administrativas para gestionar todos los tribunales.'
            ]);
        }

        // Si no hay tribunales para calificar (usuario regular sin asignaciones)
        if ($tribunalesParaCalificar->isEmpty()) {
            return view('livewire.tribunales.principal.view', [
                'tribunalesAsignados' => collect(),
            ]);
        }

        // Obtener IDs únicos de tribunales
        $tribunalIds = $tribunalesParaCalificar->pluck('id')->unique();

        // Cargar tribunales completos con sus relaciones
        $tribunales = Tribunale::whereIn('id', $tribunalIds)
            ->with([
                'estudiante',
                'carrerasPeriodo.carrera',
                'carrerasPeriodo.periodo',
                'carrerasPeriodo.director',
                'carrerasPeriodo.docenteApoyo',
                'miembrosTribunales' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->get();

        // Cargar planes de evaluación para evitar consultas N+1
        $carreraPeriodoIds = $tribunales->pluck('carrera_periodo_id')->unique();
        $planes = PlanEvaluacion::whereIn('carrera_periodo_id', $carreraPeriodoIds)
            ->with(['itemsPlanEvaluacion.asignacionesCalificadorComponentes'])
            ->get()
            ->keyBy('carrera_periodo_id');

        // Verificar calificadores generales del usuario
        $calificadorGeneralIds = CalificadorGeneralCarreraPeriodo::whereIn('carrera_periodo_id', $carreraPeriodoIds)
            ->where('user_id', $user->id)
            ->pluck('carrera_periodo_id')
            ->toArray();

        // Precargar calificaciones del usuario
        $calificacionesUsuario = MiembroCalificacion::whereIn('tribunal_id', $tribunalIds)
            ->where('user_id', $user->id)
            ->get()
            ->groupBy(function ($calificacion) {
                return $calificacion->tribunal_id . '_' . $calificacion->item_plan_evaluacion_id;
            });

        // Crear información de roles para la vista
        $tribunalesConRoles = $tribunales->map(function ($tribunal) use ($user, $calificadorGeneralIds) {
            $tribunal->tipoAsignacionUsuario = $this->determinarTipoAsignacion($tribunal, $user, $calificadorGeneralIds);
            return $tribunal;
        });

        // Aplicar filtros de búsqueda
        if (!empty($this->searchTerm)) {
            $tribunalesConRoles = $tribunalesConRoles->filter(function ($tribunal) {
                $estudiante = $tribunal->estudiante;
                $carrera = $tribunal->carrerasPeriodo->carrera ?? null;
                $periodo = $tribunal->carrerasPeriodo->periodo ?? null;

                $searchTerm = strtolower($this->searchTerm);

                // Buscar en nombres del estudiante
                if ($estudiante) {
                    $nombreCompleto = strtolower($estudiante->nombres . ' ' . $estudiante->apellidos);
                    $idEstudiante = strtolower($estudiante->ID_estudiante);
                    if (str_contains($nombreCompleto, $searchTerm) || str_contains($idEstudiante, $searchTerm)) {
                        return true;
                    }
                }

                // Buscar en nombre de carrera
                if ($carrera && str_contains(strtolower($carrera->nombre), $searchTerm)) {
                    return true;
                }

                // Buscar en código de período
                if ($periodo && str_contains(strtolower($periodo->codigo_periodo), $searchTerm)) {
                    return true;
                }

                return false;
            });
        }

        // Aplicar filtro de estado
        if ($this->filtroEstado !== 'TODOS') {
            $tribunalesConRoles = $tribunalesConRoles->filter(function ($tribunal) use ($user, $planes, $calificadorGeneralIds, $calificacionesUsuario) {
                if ($this->filtroEstado === 'CERRADOS') {
                    // Filtrar solo tribunales cerrados
                    return $tribunal->estado === 'CERRADO';
                }

                // Para otros filtros, solo considerar tribunales abiertos
                if ($tribunal->estado === 'CERRADO') {
                    return false;
                }

                $estadoTribunal = $this->determinarEstadoTribunalOptimizado($tribunal, $user, $planes, $calificadorGeneralIds, $calificacionesUsuario);

                if ($this->filtroEstado === 'PENDIENTES') {
                    return $estadoTribunal === 'PENDIENTE';
                } elseif ($this->filtroEstado === 'COMPLETADOS') {
                    return $estadoTribunal === 'COMPLETADO';
                }

                return true; // Para cualquier otro caso
            });
        }

        // Ordenar tribunales por fecha y hora
        $tribunalesConRoles = $tribunalesConRoles->sortByDesc(function ($tribunal) {
            return $tribunal->fecha . ' ' . $tribunal->hora_inicio;
        });

        // Paginación manual
        $page = $this->resolvePage();
        $perPage = 10;
        $itemsForCurrentPage = $tribunalesConRoles->slice(($page - 1) * $perPage, $perPage);
        $paginatedTribunales = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $tribunalesConRoles->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('livewire.tribunales.principal.view', [
            'tribunalesAsignados' => $paginatedTribunales,
        ]);
    }

    /**
     * Obtiene todos los tribunales que el usuario debe calificar
     */
    private function obtenerTodosLosTribunalesDelUsuario($user)
    {
        $tribunales = collect();

        // 1. Tribunales donde es miembro del tribunal
        $tribunalesComoMiembro = MiembrosTribunal::where('user_id', $user->id)
            ->with('tribunal')
            ->get()
            ->pluck('tribunal')
            ->filter();

        $tribunales = $tribunales->merge($tribunalesComoMiembro);

        // 2. Tribunales donde es calificador general
        $carreraPeriodosComoCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('user_id', $user->id)
            ->pluck('carrera_periodo_id');

        if ($carreraPeriodosComoCalificadorGeneral->isNotEmpty()) {
            $tribunalesComoCalificadorGeneral = Tribunale::whereIn('carrera_periodo_id', $carreraPeriodosComoCalificadorGeneral)
                ->get();
            $tribunales = $tribunales->merge($tribunalesComoCalificadorGeneral);
        }

        // 3. Tribunales donde debe calificar como Director o Docente de Apoyo (calificaciones directas)
        $carreraPeriodosComoDirectorOApoyo = CarrerasPeriodo::where(function ($query) use ($user) {
            $query->where('director_id', $user->id)
                ->orWhere('docente_apoyo_id', $user->id);
        })->pluck('id');

        if ($carreraPeriodosComoDirectorOApoyo->isNotEmpty()) {
            // Solo incluir tribunales que tengan planes con items de nota directa
            $planesConNotaDirecta = PlanEvaluacion::whereIn('carrera_periodo_id', $carreraPeriodosComoDirectorOApoyo)
                ->whereHas('itemsPlanEvaluacion', function ($query) {
                    $query->where('tipo_item', 'NOTA_DIRECTA');
                })
                ->pluck('carrera_periodo_id');

            if ($planesConNotaDirecta->isNotEmpty()) {
                $tribunalesConNotaDirecta = Tribunale::whereIn('carrera_periodo_id', $planesConNotaDirecta)
                    ->get();
                $tribunales = $tribunales->merge($tribunalesConNotaDirecta);
            }
        }

        // Eliminar duplicados basándose en el ID del tribunal
        return $tribunales->unique('id');
    }

    /**
     * Determina el tipo de asignación del usuario para un tribunal específico
     */
    private function determinarTipoAsignacion($tribunal, $user, $calificadorGeneralIds)
    {
        // 1. Verificar si es miembro directo del tribunal
        $miembroDirecto = $tribunal->miembrosTribunales->first();
        if ($miembroDirecto) {
            return [
                'tipo' => 'miembro_tribunal',
                'descripcion' => ucwords(strtolower(str_replace('_', ' ', $miembroDirecto->status))),
                'badge_class' => 'bg-primary'
            ];
        }

        // 2. Verificar si es Director de Carrera
        if ($tribunal->carrerasPeriodo->director_id == $user->id) {
            return [
                'tipo' => 'director',
                'descripcion' => 'Director de Carrera',
                'badge_class' => 'bg-success'
            ];
        }

        // 3. Verificar si es Docente de Apoyo
        if ($tribunal->carrerasPeriodo->docente_apoyo_id == $user->id) {
            return [
                'tipo' => 'apoyo',
                'descripcion' => 'Docente de Apoyo',
                'badge_class' => 'bg-info text-dark'
            ];
        }

        // 4. Verificar si es Calificador General
        if (in_array($tribunal->carrera_periodo_id, $calificadorGeneralIds)) {
            return [
                'tipo' => 'calificador_general',
                'descripcion' => 'Calificador General',
                'badge_class' => 'bg-warning text-dark'
            ];
        }

        // 5. Si llegamos aquí, es un caso no identificado
        return [
            'tipo' => 'no_definido',
            'descripcion' => 'No Definido',
            'badge_class' => 'bg-secondary'
        ];
    }

    /**
     * Determina el estado de un tribunal para un usuario específico (versión optimizada)
     * @param $tribunal
     * @param $user
     * @param $planes Collection de planes precargados
     * @param $calificadorGeneralIds Array de IDs donde el usuario es calificador general
     * @param $calificacionesUsuario Collection de calificaciones precargadas agrupadas
     * @return string 'PENDIENTE' o 'COMPLETADO'
     */
    private function determinarEstadoTribunalOptimizado($tribunal, $user, $planes, $calificadorGeneralIds, $calificacionesUsuario)
    {
        // Obtener el plan de evaluación desde la colección precargada
        $plan = $planes->get($tribunal->carrera_periodo_id);

        if (!$plan) {
            return 'PENDIENTE'; // Si no hay plan, consideramos pendiente
        }

        $itemsQueDebeCalificar = [];
        $itemsCalificados = [];

        // Verificar si el usuario es calificador general (desde array precargado)
        $esCalificadorGeneral = in_array($tribunal->carrera_periodo_id, $calificadorGeneralIds);

        // Verificar si es director o docente de apoyo
        $esDirector = $tribunal->carrerasPeriodo->director_id == $user->id;
        $esDocenteApoyo = $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id;

        foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
            $debeCalificarEsteItem = false;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // Para items de nota directa, verificar quién debe calificar
                if ($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $esDirector) {
                    $debeCalificarEsteItem = true;
                } elseif ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                    $debeCalificarEsteItem = true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                // Para items de rúbrica, verificar las asignaciones de componentes
                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL') {
                        // Ya sabemos que el usuario es miembro del tribunal (por la consulta inicial)
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $esDirector) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                        $debeCalificarEsteItem = true;
                        break;
                    }
                }
            }

            if ($debeCalificarEsteItem) {
                $itemsQueDebeCalificar[] = $itemPlan->id;

                // Verificar si ya calificó este ítem usando datos precargados
                $claveCalificacion = $tribunal->id . '_' . $itemPlan->id;
                if ($calificacionesUsuario->has($claveCalificacion)) {
                    $itemsCalificados[] = $itemPlan->id;
                }
            }
        }

        // Si no debe calificar nada, consideramos como PENDIENTE
        if (empty($itemsQueDebeCalificar)) {
            return 'PENDIENTE';
        }

        // Si calificó todos los items que debe calificar, está COMPLETADO
        if (count($itemsCalificados) === count($itemsQueDebeCalificar)) {
            return 'COMPLETADO';
        }

        // Si aún le faltan items por calificar, está PENDIENTE
        return 'PENDIENTE';
    }

    /**
     * Determina el estado de un tribunal para un usuario específico
     * @param $tribunal
     * @param $user
     * @return string 'PENDIENTE' o 'COMPLETADO'
     */
    private function determinarEstadoTribunal($tribunal, $user)
    {
        // Obtener el plan de evaluación para este tribunal
        $plan = PlanEvaluacion::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
            ->with([
                'itemsPlanEvaluacion.asignacionesCalificadorComponentes'
            ])
            ->first();

        if (!$plan) {
            return 'PENDIENTE'; // Si no hay plan, consideramos pendiente
        }

        $itemsQueDebeCalificar = [];
        $itemsCalificados = [];

        // Verificar si el usuario es calificador general
        $esCalificadorGeneral = CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)
            ->where('user_id', $user->id)
            ->exists();

        $esDirector = $tribunal->carrerasPeriodo->director_id == $user->id;
        $esDocenteApoyo = $tribunal->carrerasPeriodo->docente_apoyo_id == $user->id;

        foreach ($plan->itemsPlanEvaluacion as $itemPlan) {
            $debeCalificarEsteItem = false;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                if ($itemPlan->calificado_por_nota_directa === 'DIRECTOR_CARRERA' && $esDirector) {
                    $debeCalificarEsteItem = true;
                } elseif ($itemPlan->calificado_por_nota_directa === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                    $debeCalificarEsteItem = true;
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR') {
                foreach ($itemPlan->asignacionesCalificadorComponentes as $asignacion) {
                    if ($asignacion->calificado_por === 'MIEMBROS_TRIBUNAL') {
                        $esMiembroTribunal = MiembrosTribunal::where('tribunal_id', $tribunal->id)
                            ->where('user_id', $user->id)
                            ->exists();
                        if ($esMiembroTribunal) {
                            $debeCalificarEsteItem = true;
                            break;
                        }
                    } elseif ($asignacion->calificado_por === 'CALIFICADORES_GENERALES' && $esCalificadorGeneral) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DIRECTOR_CARRERA' && $esDirector) {
                        $debeCalificarEsteItem = true;
                        break;
                    } elseif ($asignacion->calificado_por === 'DOCENTE_APOYO' && $esDocenteApoyo) {
                        $debeCalificarEsteItem = true;
                        break;
                    }
                }
            }

            if ($debeCalificarEsteItem) {
                $itemsQueDebeCalificar[] = $itemPlan->id;

                $calificacionExistente = MiembroCalificacion::where('tribunal_id', $tribunal->id)
                    ->where('user_id', $user->id)
                    ->where('item_plan_evaluacion_id', $itemPlan->id)
                    ->exists();

                if ($calificacionExistente) {
                    $itemsCalificados[] = $itemPlan->id;
                }
            }
        }

        if (empty($itemsQueDebeCalificar)) {
            return 'PENDIENTE';
        }

        if (count($itemsCalificados) === count($itemsQueDebeCalificar)) {
            return 'COMPLETADO';
        }

        return 'PENDIENTE';
    }
}
