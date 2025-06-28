<?php

namespace App\Http\Livewire;

use App\Models\ComponenteRubrica;
use App\Models\Tribunale; // Asumiendo que tu modelo se llama Tribunale
use App\Models\User;
use App\Models\PlanEvaluacion;
use App\Models\CalificacionCriterio; // Para buscar detalles de la calificación elegida
use App\Models\ItemPlanEvaluacion;
use App\Models\MiembrosTribunale;    // Para iterar sobre los miembros del tribunal
use App\Models\MiembroCalificacion;  // Para obtener las calificaciones
use App\Models\MiembrosTribunal;
use App\Models\TribunalLog;         // Para el historial
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str; // Para Str::title y Str::lower

class TribunalProfile extends Component
{
    public $tribunalId;
    public ?Tribunale $tribunal = null;

    // Para edición de datos del tribunal
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $presidente_id;
    public $integrante1_id;
    public $integrante2_id;
    public $profesoresDisponibles; // Profesores para los selects de edición
    public $modoEdicionTribunal = false;

    // Plan de evaluación y visualización de calificaciones
    public ?PlanEvaluacion $planEvaluacionActivo = null;
    public $todasLasCalificacionesDelTribunal = []; // Detalle por miembro para el modal
    public $resumenNotasCalculadas = [];          // Resumen para la tabla principal
    public $notaFinalCalculadaDelTribunal = 0;
    public $sumaPonderacionesGlobalesItems = 0; // Para verificar si el plan suma 100%

    // Permisos del usuario
    public $usuarioPuedeEditarDatosTribunal = false;
    public $usuarioPuedeVerTodasLasCalificaciones = false; // Este permiso ahora controla la visibilidad del resumen
    public $usuarioPuedeExportarActa = false;
    public $detalleRubricasParaModal = [];

    protected function rules()
    {
        $rules = [];
        if ($this->modoEdicionTribunal && $this->usuarioPuedeEditarDatosTribunal) {
            $rules = [
                'fecha' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i', // Asumiendo que guardas como string 'HH:MM'
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'presidente_id' => 'required|exists:users,id|different:integrante1_id|different:integrante2_id',
                'integrante1_id' => 'required|exists:users,id|different:presidente_id|different:integrante2_id',
                'integrante2_id' => 'required|exists:users,id|different:presidente_id|different:integrante1_id',
            ];
        }
        return $rules;
    }

    public function validationAttributes()
    {
        return [
            'fecha' => 'Fecha del Tribunal',
            'hora_inicio' => 'Hora de Inicio',
            'hora_fin' => 'Hora de Fin',
            'presidente_id' => 'Presidente',
            'integrante1_id' => 'Integrante 1',
            'integrante2_id' => 'Integrante 2',
        ];
    }

    public function mount($tribunalId)
    {
        $this->tribunalId = $tribunalId;
        if (!$this->loadAndPrepareTribunalData()) {
            return;
        }

        // Profesores disponibles para la edición de miembros (excluir Super Admin y Admin)
        $rolesExcluidosEdicion = ['Super Admin', 'Administrador'];
        $this->profesoresDisponibles = User::whereDoesntHave('roles', function ($query) use ($rolesExcluidosEdicion) {
            $query->whereIn('name', $rolesExcluidosEdicion);
        })
            ->orderBy('name')->get();

        $this->checkUserPermissions();

        if ($this->usuarioPuedeVerTodasLasCalificaciones && $this->planEvaluacionActivo) {
            $this->calculateAndLoadAllCalificaciones();
        }
    }

    protected function loadAndPrepareTribunalData(): bool
    {
        $this->tribunal = Tribunale::with([
            'carrerasPeriodo.carrera',
            'carrerasPeriodo.periodo',
            'carrerasPeriodo.director', // Cargar director y apoyo para permisos
            'carrerasPeriodo.docenteApoyo',
            'estudiante',
            'miembrosTribunales.user',
            'logs.user'
        ])->find($this->tribunalId);

        if (!$this->tribunal) {
            session()->flash('danger', 'Tribunal no encontrado o no tiene acceso para visualizarlo.');
            return false;
        }

        $this->fecha = $this->tribunal->fecha;
        // Asegurar que las horas se carguen en formato HH:MM para los inputs type="time"
        $this->hora_inicio = \Carbon\Carbon::parse($this->tribunal->hora_inicio)->format('H:i');
        $this->hora_fin = \Carbon\Carbon::parse($this->tribunal->hora_fin)->format('H:i');

        foreach ($this->tribunal->miembrosTribunales as $miembro) {
            if ($miembro->status == 'PRESIDENTE') $this->presidente_id = $miembro->user_id;
            if ($miembro->status == 'INTEGRANTE1') $this->integrante1_id = $miembro->user_id;
            if ($miembro->status == 'INTEGRANTE2') $this->integrante2_id = $miembro->user_id;
        }

        if ($this->tribunal->carrerasPeriodo) {
            $this->planEvaluacionActivo = PlanEvaluacion::with([
                'itemsPlanEvaluacion.rubricaPlantilla.componentesRubrica.criteriosComponente.calificacionesCriterio',
                'itemsPlanEvaluacion.asignacionesCalificadorComponentes' // Para saber quién califica qué
            ])
                ->where('carrera_periodo_id', $this->tribunal->carrera_periodo_id)
                ->first();
        }
        return true;
    }
    protected function checkUserPermissions()
    {
        $user = Auth::user();
        if (!$user || !$this->tribunal || !$this->tribunal->carrerasPeriodo) return;

        // Un Administrador tiene permisos globales que se manejan con Gate::before o su rol.
        // Director y Docente de Apoyo del carrera_periodo específico pueden editar.
        $esDirectorOApoyoDelCarreraPeriodo = (
            ($user->hasRole('Director de Carrera') && $this->tribunal->carrerasPeriodo->director_id === $user->id) ||
            ($user->hasRole('Docente de Apoyo') && $this->tribunal->carrerasPeriodo->docente_apoyo_id === $user->id)
        );

        $this->usuarioPuedeEditarDatosTribunal = $user->hasRole('Administrador') || $esDirectorOApoyoDelCarreraPeriodo;
        // $this->usuarioPuedeEditarDatosTribunal = Gate::allows('editar-datos-basicos-este-tribunal-como-presidente', $this->tribunal) || $user->hasRole('Administrador'); // Lógica anterior

        $this->usuarioPuedeVerTodasLasCalificaciones = Gate::allows('ver-todas-calificaciones-de-este-tribunal', $this->tribunal);
        $this->usuarioPuedeExportarActa = Gate::allows('exportar-acta-este-tribunal-como-presidente', $this->tribunal) || $user->hasRole('Administrador');
    }


    public function generarDetallePopoverRubrica($calificacionItemRubricaMiembro)
    {
        if (empty($calificacionItemRubricaMiembro) || !isset($calificacionItemRubricaMiembro['componentes_evaluados'])) {
            return addslashes('No hay detalle disponible.');
        }

        $html = '<div style="max-width: 500px; max-height: 350px; overflow-y: auto; font-size: 0.7rem; text-align: left;">';
        $html .= '<h6>Detalle de Rúbrica: ' . htmlspecialchars($calificacionItemRubricaMiembro['rubrica_plantilla_nombre'] ?? '', ENT_QUOTES) . '</h6>';

        foreach ($calificacionItemRubricaMiembro['componentes_evaluados'] as $datosComp) {
            $html .= '<div class="mb-1 border-bottom pb-1">';
            $html .= '<small><strong>' . htmlspecialchars($datosComp['nombre_componente_rubrica'] ?? '', ENT_QUOTES) . ':</strong></small>';
            if (isset($datosComp['criterios_evaluados']) && !empty($datosComp['criterios_evaluados'])) {
                $html .= '<ul class="list-unstyled ps-2 mb-0">';
                foreach ($datosComp['criterios_evaluados'] as $datosCrit) {
                    $html .= '<li><small>';
                    $html .= htmlspecialchars($datosCrit['nombre_criterio_rubrica'] ?? '', ENT_QUOTES) . ': ';
                    $html .= '<em>' . htmlspecialchars($datosCrit['calificacion_elegida_nombre'] ?? 'N/R', ENT_QUOTES) . ' (' . htmlspecialchars($datosCrit['calificacion_elegida_valor'] ?? 'N/R', ENT_QUOTES) . ')</em>';
                    if (!empty($datosCrit['observacion'])) {
                        $html .= '<br><span class="text-muted" style="font-size: 0.9em;">  Obs: ' . htmlspecialchars($datosCrit['observacion'], ENT_QUOTES) . '</span>';
                    }
                    $html .= '</small></li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p class="ms-2 mb-0"><small class="text-muted"><em>Sin criterios calificados.</em></small></p>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return addslashes($html); // Escapar para el atributo data-bs-content
    }

    public function toggleModoEdicionTribunal()
    {
        if ($this->usuarioPuedeEditarDatosTribunal) {
            $this->modoEdicionTribunal = !$this->modoEdicionTribunal;
            if (!$this->modoEdicionTribunal) {
                // Recargar datos originales si se cancela
                $this->fecha = $this->tribunal->fecha;
                $this->hora_inicio = $this->tribunal->hora_inicio;
                $this->hora_fin = $this->tribunal->hora_fin;
                foreach ($this->tribunal->miembrosTribunales as $miembro) {
                    if ($miembro->status == 'PRESIDENTE') $this->presidente_id = $miembro->user_id;
                    if ($miembro->status == 'INTEGRANTE1') $this->integrante1_id = $miembro->user_id;
                    if ($miembro->status == 'INTEGRANTE2') $this->integrante2_id = $miembro->user_id;
                }
                $this->resetValidation(); // Limpiar errores de validación
            }
        } else {
            session()->flash('danger', 'No tiene permisos para editar los datos de este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
        }
    }

    public function actualizarDatosTribunal()
    {
        if (!$this->usuarioPuedeEditarDatosTribunal) {
            session()->flash('danger', 'No tiene permisos para actualizar este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        $validatedData = $this->validate(); // Usa las rules definidas

        // Obtener datos antiguos para el log
        $datosAntiguos = [
            'fecha' => $this->tribunal->fecha,
            'hora_inicio' => $this->tribunal->hora_inicio,
            'hora_fin' => $this->tribunal->hora_fin,
            'presidente_id' => $this->tribunal->miembrosTribunales->firstWhere('status', 'PRESIDENTE')->user_id ?? null,
            'integrante1_id' => $this->tribunal->miembrosTribunales->firstWhere('status', 'INTEGRANTE1')->user_id ?? null,
            'integrante2_id' => $this->tribunal->miembrosTribunales->firstWhere('status', 'INTEGRANTE2')->user_id ?? null,
        ];
        $profesores = User::whereIn('id', [$datosAntiguos['presidente_id'], $datosAntiguos['integrante1_id'], $datosAntiguos['integrante2_id'], $this->presidente_id, $this->integrante1_id, $this->integrante2_id])->get()->keyBy('id');


        DB::transaction(function () use ($validatedData, $datosAntiguos, $profesores) {
            $this->tribunal->update([
                'fecha' => $validatedData['fecha'],
                'hora_inicio' => $validatedData['hora_inicio'],
                'hora_fin' => $validatedData['hora_fin'],
            ]);

            // Actualizar miembros (eliminar y recrear)
            $this->tribunal->miembrosTribunales()->delete();
            $nuevosMiembrosData = [
                ['user_id' => $validatedData['presidente_id'], 'status' => 'PRESIDENTE'],
                ['user_id' => $validatedData['integrante1_id'], 'status' => 'INTEGRANTE1'],
                ['user_id' => $validatedData['integrante2_id'], 'status' => 'INTEGRANTE2'],
            ];
            $this->tribunal->miembrosTribunales()->createMany($nuevosMiembrosData);

            // --- Registrar Logs ---
            $cambios = [];
            if ($datosAntiguos['fecha'] != $validatedData['fecha']) $cambios[] = "Fecha de '{$datosAntiguos['fecha']}' a '{$validatedData['fecha']}'";
            if ($datosAntiguos['hora_inicio'] != $validatedData['hora_inicio']) $cambios[] = "Hora inicio de '{$datosAntiguos['hora_inicio']}' a '{$validatedData['hora_inicio']}'";
            if ($datosAntiguos['hora_fin'] != $validatedData['hora_fin']) $cambios[] = "Hora fin de '{$datosAntiguos['hora_fin']}' a '{$validatedData['hora_fin']}'";

            $nombreAntiguoP = $datosAntiguos['presidente_id'] ? ($profesores[$datosAntiguos['presidente_id']]->name ?? 'N/A') : 'N/A';
            $nombreNuevoP = $validatedData['presidente_id'] ? ($profesores[$validatedData['presidente_id']]->name ?? 'N/A') : 'N/A';
            if ($datosAntiguos['presidente_id'] != $validatedData['presidente_id']) $cambios[] = "Presidente de '{$nombreAntiguoP}' a '{$nombreNuevoP}'";

            $nombreAntiguoI1 = $datosAntiguos['integrante1_id'] ? ($profesores[$datosAntiguos['integrante1_id']]->name ?? 'N/A') : 'N/A';
            $nombreNuevoI1 = $validatedData['integrante1_id'] ? ($profesores[$validatedData['integrante1_id']]->name ?? 'N/A') : 'N/A';
            if ($datosAntiguos['integrante1_id'] != $validatedData['integrante1_id']) $cambios[] = "Integrante 1 de '{$nombreAntiguoI1}' a '{$nombreNuevoI1}'";

            $nombreAntiguoI2 = $datosAntiguos['integrante2_id'] ? ($profesores[$datosAntiguos['integrante2_id']]->name ?? 'N/A') : 'N/A';
            $nombreNuevoI2 = $validatedData['integrante2_id'] ? ($profesores[$validatedData['integrante2_id']]->name ?? 'N/A') : 'N/A';
            if ($datosAntiguos['integrante2_id'] != $validatedData['integrante2_id']) $cambios[] = "Integrante 2 de '{$nombreAntiguoI2}' a '{$nombreNuevoI2}'";


            if (!empty($cambios)) {
                TribunalLog::create([
                    'tribunal_id' => $this->tribunal->id,
                    'user_id' => Auth::id(),
                    'accion' => 'ACTUALIZACION_DATOS_TRIBUNAL',
                    'descripcion' => "Se actualizaron los datos: " . implode('; ', $cambios) . ".",
                    'datos_antiguos' => $datosAntiguos,
                    'datos_nuevos' => $validatedData // O solo los campos que cambian
                ]);
            }
        });

        session()->flash('success', 'Datos del tribunal actualizados exitosamente.');
        $this->modoEdicionTribunal = false;
        $this->loadAndPrepareTribunalData(); // Recargar todos los datos, incluyendo los logs
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    protected function calculateAndLoadAllCalificaciones()
    {
        if (!$this->planEvaluacionActivo || !$this->tribunal) return;

        $this->resumenNotasCalculadas = [];
        $this->todasLasCalificacionesDelTribunal = []; // Para el detalle del modal
        $this->notaFinalCalculadaDelTribunal = 0;
        $this->sumaPonderacionesGlobalesItems = 0;

        $miembrosDelTribunal = $this->tribunal->miembrosTribunales; // Ya cargados con 'user'
        $idsMiembrosDelTribunal = $miembrosDelTribunal->pluck('id')->all();

        // Calificadores generales del carrera_periodo
        $calificadoresGeneralesUsers = $this->tribunal->carrerasPeriodo->docentesCalificadoresGenerales ?? collect();
        $idsCalificadoresGenerales = $calificadoresGeneralesUsers->pluck('id')->all();

        // Director y Apoyo IDs
        $directorId = $this->tribunal->carrerasPeriodo->director_id;
        $apoyoId = $this->tribunal->carrerasPeriodo->docente_apoyo_id;

        // Obtener todas las calificaciones para este tribunal de una vez
        $todasLasMiembroCalificacion = MiembroCalificacion::where('tribunal_id', $this->tribunal->id)
            ->with(['itemPlanEvaluacion', 'criterioCalificado', 'opcionCalificacionElegida'])
            ->get();

        foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $this->sumaPonderacionesGlobalesItems += $itemPlan->ponderacion_global;
            $notaItemParaTribunalSobre20 = null;
            $observacionGeneralItem = '';
            $puntajePonderadoDelItem = 0;

            $calificacionesParaEsteItem = $todasLasMiembroCalificacion->where('item_plan_evaluacion_id', $itemPlan->id);

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                // Buscar la calificación del Director o Apoyo para este ítem de nota directa
                $califNotaDirecta = $calificacionesParaEsteItem
                    ->whereIn('user_id', array_filter([$directorId, $apoyoId])) // Solo de Director o Apoyo
                    ->whereNull('criterio_id')
                    ->first();

                if ($califNotaDirecta && is_numeric($califNotaDirecta->nota_obtenida_directa)) {
                    $notaItemParaTribunalSobre20 = (float) $califNotaDirecta->nota_obtenida_directa;
                    $observacionGeneralItem = $califNotaDirecta->observacion ?? '';
                }
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                $notasRubricaPorGrupoCalificador = []; // [calificado_por_value => [notas_sobre_20]]
                $observacionesGeneralesRubrica = []; // [user_id => observacion]

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

                        // Observación general del ítem de rúbrica por este usuario
                        $obsGeneral = $calificacionesDelUsuarioParaItem->whereNull('criterio_id')->first();
                        if ($obsGeneral && !empty($obsGeneral->observacion)) {
                            $observacionesGeneralesRubrica[$userIdCalificador] = $obsGeneral->observacion;
                        }

                        $notaComponenteParaUsuario = $this->calcularNotaComponenteParaUsuario($componenteRubrica, $calificacionesDelUsuarioParaItem);
                        if (is_numeric($notaComponenteParaUsuario)) {
                            $sumaNotasComponenteEsteGrupo += $notaComponenteParaUsuario; // Esto está en base a la ponderación del componente
                            $conteoNotasComponenteEsteGrupo++;
                        }
                    }

                    if ($conteoNotasComponenteEsteGrupo > 0) {
                        $promedioNotaComponenteEsteGrupo = $sumaNotasComponenteEsteGrupo / $conteoNotasComponenteEsteGrupo;
                        // Guardar el promedio del componente para este grupo calificador, ponderado por el componente.
                        // La clave podría ser $grupoCalificadorResponsable o el $componenteR->id para luego promediar todos los componentes.
                        if (!isset($notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable])) {
                            $notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable] = [];
                        }
                        // Este promedio es el aporte del componente a la nota de la rúbrica (escala 0-ponderacion_componente)
                        $notasRubricaPorGrupoCalificador[$grupoCalificadorResponsable][$componenteRubrica->id] = $promedioNotaComponenteEsteGrupo;
                    }
                }

                // Calcular la nota final de la rúbrica (sobre 20)
                // Sumar los aportes de cada componente (ya ponderados por su peso interno)
                // y luego normalizar a 20.
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
                    // La nota (escala 0-100 de la rúbrica) es $sumaPuntajesPonderadosComponentes (ya que cada uno es puntaje*pond/max_puntaje)
                    // Si $sumaPonderacionesDeComponentesUsados no es 100, hay que normalizar
                    $notaRubricaBase100 = ($sumaPuntajesPonderadosComponentes / $sumaPonderacionesDeComponentesUsados) * 100;
                    $notaItemParaTribunalSobre20 = ($notaRubricaBase100 / 100) * 20;
                }
                // La observación general para una rúbrica podría ser un compendio o la del presidente si la puso.
                // Por ahora, dejaremos $observacionGeneralItem vacía para rúbricas en el resumen, el detalle está en el modal.

            } // Fin RUBRICA_TABULAR

            if (is_numeric($notaItemParaTribunalSobre20)) {
                $puntajePonderadoDelItem = $notaItemParaTribunalSobre20 * ($itemPlan->ponderacion_global / 100);
            }
            $this->notaFinalCalculadaDelTribunal += $puntajePonderadoDelItem;

            $this->resumenNotasCalculadas[$itemPlan->id] = [
                'nombre_item_plan' => $itemPlan->nombre_item,
                'ponderacion_global' => $itemPlan->ponderacion_global,
                'tipo_item' => $itemPlan->tipo_item,
                'rubrica_plantilla_nombre' => ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) ? $itemPlan->rubricaPlantilla->nombre : null,
                'nota_tribunal_sobre_20' => is_numeric($notaItemParaTribunalSobre20) ? round($notaItemParaTribunalSobre20, 2) : null,
                'puntaje_ponderado_item' => round($puntajePonderadoDelItem, 2),
                'observacion_general' => ($itemPlan->tipo_item === 'NOTA_DIRECTA') ? $observacionGeneralItem : '', // Solo para nota directa en resumen
            ];
        } // Fin foreach $itemPlan

        // Poblar $todasLasCalificacionesDelTribunal para el modal de detalle de rúbricas
        // Combinar miembros del tribunal, calificadores generales, director, apoyo en una lista de calificadores relevantes
        $todosLosCalificadoresRelevantesUsers = collect();
        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->merge($miembrosDelTribunal->map(fn($mt) => $mt->user->setAttribute('rol_evaluador', $mt->status)));
        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->merge($calificadoresGeneralesUsers->map(fn($u) => $u->setAttribute('rol_evaluador', 'CALIFICADOR_GENERAL')));
        if ($directorId) $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->push(User::find($directorId)?->setAttribute('rol_evaluador', 'DIRECTOR_CARRERA'));
        if ($apoyoId) $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->push(User::find($apoyoId)?->setAttribute('rol_evaluador', 'DOCENTE_APOYO'));

        $todosLosCalificadoresRelevantesUsers = $todosLosCalificadoresRelevantesUsers->filter()->unique('id');


        foreach ($todosLosCalificadoresRelevantesUsers as $calificadorUser) {
            if (!$calificadorUser) continue;

            $calificacionesFormateadasMiembro = [];
            $susCalificacionesGuardadas = $todasLasMiembroCalificacion->where('user_id', $calificadorUser->id);

            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                // Solo nos interesa el detalle de Rúbricas Tabulares para el modal
                if ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    $califGeneralRubricaDelMiembro = $susCalificacionesGuardadas
                        ->where('item_plan_evaluacion_id', $itemPlan->id)
                        ->whereNull('criterio_id')
                        ->first();

                    $datosItemRubrica = [
                        'nombre_item_plan' => $itemPlan->nombre_item, // Para el modal
                        'tipo' => $itemPlan->tipo_item,
                        'observacion_general' => $califGeneralRubricaDelMiembro?->observacion ?? '',
                        'rubrica_plantilla_nombre' => $itemPlan->rubricaPlantilla->nombre,
                        'componentes_evaluados' => [],
                    ];

                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        // Verificar si este calificadorUser debía calificar este componenteR
                        $asignacionComp = $itemPlan->asignacionesCalificadorComponentes->firstWhere('componente_rubrica_id', $componenteR->id);
                        $debiaCalificarEsteComponente = false;
                        if ($asignacionComp) {
                            if ($asignacionComp->calificado_por === 'MIEMBROS_TRIBUNAL' && $miembrosDelTribunal->contains('user_id', $calificadorUser->id)) $debiaCalificarEsteComponente = true;
                            if ($asignacionComp->calificado_por === 'CALIFICADORES_GENERALES' && $calificadoresGeneralesUsers->contains('id', $calificadorUser->id)) $debiaCalificarEsteComponente = true;
                            if ($asignacionComp->calificado_por === 'DIRECTOR_CARRERA' && $calificadorUser->id == $directorId) $debiaCalificarEsteComponente = true;
                            if ($asignacionComp->calificado_por === 'DOCENTE_APOYO' && $calificadorUser->id == $apoyoId) $debiaCalificarEsteComponente = true;
                        }

                        if ($debiaCalificarEsteComponente) { // Solo mostrar si este usuario debía calificarlo
                            $criteriosEvaluadosArray = [];
                            foreach ($componenteR->criteriosComponente as $criterioR) {
                                $califCriterioDelMiembro = $susCalificacionesGuardadas
                                    ->where('item_plan_evaluacion_id', $itemPlan->id)
                                    ->where('criterio_id', $criterioR->id)
                                    ->first();

                                $opcionElegida = $califCriterioDelMiembro ? $califCriterioDelMiembro->opcionCalificacionElegida : null;

                                $criteriosEvaluadosArray[$criterioR->id] = [
                                    'nombre_criterio_rubrica' => $criterioR->nombre,
                                    'calificacion_elegida_nombre' => $opcionElegida?->nombre ?? null,
                                    'calificacion_elegida_valor' => $opcionElegida?->valor ?? null,
                                    'observacion' => $califCriterioDelMiembro?->observacion ?? '',
                                ];
                            }
                            $datosItemRubrica['componentes_evaluados'][$componenteR->id] = [
                                'nombre_componente_rubrica' => $componenteR->nombre,
                                'criterios_evaluados' => $criteriosEvaluadosArray,
                            ];
                        }
                    }
                    // Solo añadir al array si tiene componentes evaluados (es decir, si este usuario calificó algo de esta rúbrica)
                    if (!empty($datosItemRubrica['componentes_evaluados'])) {
                        $calificacionesFormateadasMiembro[$itemPlan->id] = $datosItemRubrica;
                    }
                }
            }
            // Solo añadir al global si tiene calificaciones de rúbrica formateadas
            if (!empty($calificacionesFormateadasMiembro)) {
                $rolDelCalificador = $calificadorUser->rol_evaluador ?? ($miembrosDelTribunal->firstWhere('user_id', $calificadorUser->id)?->status ?? 'N/D');

                $this->todasLasCalificacionesDelTribunal[$calificadorUser->id] = [
                    'nombre_miembro' => $calificadorUser->name,
                    'rol_miembro' => $rolDelCalificador,
                    'calificaciones_ingresadas' => $calificacionesFormateadasMiembro
                ];
            }
        }

        $this->detalleRubricasParaModal = [];
        if ($this->planEvaluacionActivo) {
            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                if ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    $componentesParaEsteItemModal = [];
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        $calificacionesPorUsuarioParaEsteComponente = [];

                        // Iterar sobre los calificadores relevantes que realmente calificaron ALGO de este ítem de rúbrica
                        // Usamos $todosLasCalificacionesDelTribunal que ya tiene los datos por usuario.
                        foreach ($this->todasLasCalificacionesDelTribunal as $userIdCalificador => $datosMiembro) {
                            // Verificar si este miembro calificó este ítem de rúbrica y este componente específico
                            $califItemMiembro = $datosMiembro['calificaciones_ingresadas'][$itemPlan->id] ?? null;
                            if ($califItemMiembro && isset($califItemMiembro['componentes_evaluados'][$componenteR->id])) {
                                $datosComponenteCalificadoPorMiembro = $califItemMiembro['componentes_evaluados'][$componenteR->id];

                                $criteriosFormateados = [];
                                if (isset($datosComponenteCalificadoPorMiembro['criterios_evaluados'])) {
                                    foreach ($datosComponenteCalificadoPorMiembro['criterios_evaluados'] as $criterioId => $datosCrit) {
                                        $criteriosFormateados[$criterioId] = [
                                            'nombre_criterio_rubrica' => $datosCrit['nombre_criterio_rubrica'],
                                            'calificacion_elegida_nombre' => $datosCrit['calificacion_elegida_nombre'],
                                            'calificacion_elegida_valor' => $datosCrit['calificacion_elegida_valor'],
                                            'observacion' => $datosCrit['observacion'],
                                        ];
                                    }
                                }

                                $calificacionesPorUsuarioParaEsteComponente[$userIdCalificador] = [
                                    'nombre_usuario' => $datosMiembro['nombre_miembro'],
                                    'rol_evaluador' => $datosMiembro['rol_miembro'], // El rol que tenía al calificar
                                    'criterios_evaluados' => $criteriosFormateados,
                                    // Podrías añadir la observación general del ítem por este miembro aquí si es relevante para el componente
                                    'observacion_general_item_miembro' => $califItemMiembro['observacion_general'] ?? '',
                                ];
                            }
                        }
                        if (!empty($calificacionesPorUsuarioParaEsteComponente)) {
                            $componentesParaEsteItemModal[$componenteR->id] = [
                                'nombre_componente_rubrica' => $componenteR->nombre,
                                'calificaciones_por_usuario' => $calificacionesPorUsuarioParaEsteComponente,
                            ];
                        }
                    }
                    if (!empty($componentesParaEsteItemModal)) {
                        $this->detalleRubricasParaModal[$itemPlan->id] = [
                            'nombre_item_plan' => $itemPlan->nombre_item,
                            'rubrica_plantilla_nombre' => $itemPlan->rubricaPlantilla->nombre,
                            'componentes' => $componentesParaEsteItemModal,
                        ];
                    }
                }
            }
        }
    }

    protected function calcularNotaComponenteParaUsuario(ComponenteRubrica $componenteR, Collection $calificacionesDelUsuarioParaComp): ?float
    {
        $puntajeObtenidoCriterios = 0;
        $maxPuntajePosibleCriterios = 0;
        $criteriosCalificadosCount = 0;

        foreach ($componenteR->criteriosComponente as $criterioR) {
            if ($criterioR->calificacionesCriterio->isNotEmpty()) {
                $maxValorCriterio = $criterioR->calificacionesCriterio->max('valor');
                if (is_numeric($maxValorCriterio)) {
                    $maxPuntajePosibleCriterios += (float) $maxValorCriterio;
                }
            }

            $califCriterioActual = $calificacionesDelUsuarioParaComp
                ->where('criterio_id', $criterioR->id)
                ->first();

            if ($califCriterioActual && $califCriterioActual->opcionCalificacionElegida) {
                $opcionElegida = $califCriterioActual->opcionCalificacionElegida; // Ya es el objeto CalificacionCriterio
                if (is_numeric($opcionElegida->valor)) {
                    $puntajeObtenidoCriterios += (float) $opcionElegida->valor;
                    $criteriosCalificadosCount++;
                }
            }
        }

        if ($maxPuntajePosibleCriterios > 0 && $criteriosCalificadosCount === $componenteR->criteriosComponente->count()) {
            // Normalizar el puntaje del componente a una escala de 0 a ponderacion_interna_del_componente
            return ($puntajeObtenidoCriterios / $maxPuntajePosibleCriterios) * $componenteR->ponderacion;
        }
        return null; // No se calificaron todos los criterios o no hay puntaje máximo
    }

    public function exportarActa()
    {
        // Lógica para generar y descargar el PDF del acta
        // Esto requerirá una librería como DomPDF o Browsershot
        // Por ahora, solo un placeholder
        if ($this->usuarioPuedeExportarActa) {
            session()->flash('info', 'Funcionalidad de exportar acta aún no implementada.');
            // Aquí llamarías a un servicio o generarías el PDF
            // return response()->streamDownload(function () {
            //     $pdf = \PDF::loadView('pdfs.acta_tribunal', ['tribunal' => $this->tribunal, 'calificaciones' => $this->todasLasCalificacionesDelTribunal, 'plan' => $this->planEvaluacionActivo]);
            //     echo $pdf->output();
            // }, 'acta_tribunal_' . $this->tribunal->id . '.pdf');
        } else {
            session()->flash('danger', 'No tiene permisos para exportar el acta.');
        }
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function loadTodasLasCalificacionesDelTribunal()
    {
        if (!$this->planEvaluacionActivo || !$this->tribunal) return;

        $this->todasLasCalificacionesDelTribunal = [];
        //$this->calificacionesGlobalesTribunal = [];
        $this->resumenNotasCalculadas = [];
        $this->notaFinalCalculadaDelTribunal = 0;
        $this->sumaPonderacionesGlobalesItems = 0;

        // ... (obtención de miembros, presidente, $todasLasMiembroCalificacionDelTribunal sin cambios) ...
        $miembrosDelTribunal = MiembrosTribunal::with(['user'])->where('tribunal_id', $this->tribunal->id)->get();
        $registroPresidente = $miembrosDelTribunal->firstWhere('status', 'PRESIDENTE');
        $miembroTribunalIdDelPresidente = $registroPresidente ? $registroPresidente->id : null;

        $todasLasMiembroCalificacionDelTribunal = MiembroCalificacion::whereIn(
            'miembro_tribunal_id',
            $miembrosDelTribunal->pluck('id')->all()
        )
            ->with(['itemPlanEvaluacion.rubricaPlantilla', 'criterioCalificado.calificacionesCriterio'])
            ->get();


        foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
            $this->sumaPonderacionesGlobalesItems += $itemPlan->ponderacion_global;
            $notaItemParaTribunalSobre20 = null;
            $observacionGeneralItem = ''; // Para NOTA_DIRECTA
            $puntajePonderadoDelItem = 0;

            if ($itemPlan->tipo_item === 'NOTA_DIRECTA') {
                $califNotaDirectaDelPresidente = null;
                if ($miembroTribunalIdDelPresidente) {
                    $califNotaDirectaDelPresidente = $todasLasMiembroCalificacionDelTribunal
                        ->where('miembro_tribunal_id', $miembroTribunalIdDelPresidente)
                        ->where('item_plan_evaluacion_id', $itemPlan->id)
                        ->whereNull('criterio_id')
                        ->first();
                }
                if ($califNotaDirectaDelPresidente && is_numeric($califNotaDirectaDelPresidente->nota_obtenida_directa)) {
                    $notaItemParaTribunalSobre20 = (float) $califNotaDirectaDelPresidente->nota_obtenida_directa;
                    $observacionGeneralItem = $califNotaDirectaDelPresidente->observacion ?? '';
                }
                // Llenar $calificacionesGlobalesTribunal (si aún lo usas para algo más, sino podría eliminarse)
                $this->calificacionesGlobalesTribunal[$itemPlan->id] = [
                    'nombre_item_plan' => $itemPlan->nombre_item,
                    'ponderacion_item_plan' => $itemPlan->ponderacion_global,
                    'tipo' => $itemPlan->tipo_item,
                    'nota_directa' => $notaItemParaTribunalSobre20,
                    'observacion_general' => $observacionGeneralItem,
                ];
            } elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                $notasRubricaPorMiembroParaEsteItem = [];
                foreach ($miembrosDelTribunal as $miembro) {
                    $susCalificacionesGuardadas = $todasLasMiembroCalificacionDelTribunal->where('miembro_tribunal_id', $miembro->id);
                    $notaCalculadaEsteMiembroEstaRubrica = $this->calcularNotaRubricaParaMiembro($itemPlan, $susCalificacionesGuardadas);
                    if (is_numeric($notaCalculadaEsteMiembroEstaRubrica)) {
                        $notasRubricaPorMiembroParaEsteItem[$miembro->user_id] = $notaCalculadaEsteMiembroEstaRubrica;
                    }
                }
                if (count($notasRubricaPorMiembroParaEsteItem) > 0) {
                    $notaItemParaTribunalSobre20 = array_sum($notasRubricaPorMiembroParaEsteItem) / count($notasRubricaPorMiembroParaEsteItem);
                }
            }

            // --- AJUSTE DEL CÁLCULO DEL PUNTAJE PONDERADO DEL ÍTEM ---
            if (is_numeric($notaItemParaTribunalSobre20) && $itemPlan->ponderacion_global > 0) {
                // El puntaje ponderado ahora también es sobre una escala efectiva de 20 para la nota final
                $puntajePonderadoDelItem = $notaItemParaTribunalSobre20 * ($itemPlan->ponderacion_global / 100);
            }
            // La nota final se acumula con estos nuevos puntajes ponderados
            $this->notaFinalCalculadaDelTribunal += $puntajePonderadoDelItem;


            $this->resumenNotasCalculadas[$itemPlan->id] = [
                'nombre_item_plan' => $itemPlan->nombre_item,
                'ponderacion_global' => $itemPlan->ponderacion_global,
                'tipo_item' => $itemPlan->tipo_item,
                'rubrica_plantilla_nombre' => ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) ? $itemPlan->rubricaPlantilla->nombre : null,
                'nota_tribunal_sobre_20' => is_numeric($notaItemParaTribunalSobre20) ? round($notaItemParaTribunalSobre20, 2) : null,
                'puntaje_ponderado_item' => round($puntajePonderadoDelItem, 2), // Este valor ya está en la escala correcta para sumar a una nota final sobre 20
                'observacion_general' => ($itemPlan->tipo_item === 'NOTA_DIRECTA') ? $observacionGeneralItem : null,
            ];
        }

        // (La lógica para poblar $todasLasCalificacionesDelTribunal para el detalle del popover se mantiene sin cambios)
        // ...
        foreach ($miembrosDelTribunal as $miembro) {
            $calificacionesFormateadasParaEsteMiembro = [];
            $susCalificacionesGuardadas = $todasLasMiembroCalificacionDelTribunal->where('miembro_tribunal_id', $miembro->id);

            foreach ($this->planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan) {
                if ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla) {
                    $califGeneralRubricaDelMiembro = $susCalificacionesGuardadas
                        ->where('item_plan_evaluacion_id', $itemPlan->id)
                        ->whereNull('criterio_id')
                        ->first();

                    $datosItemRubrica = [
                        'nombre_item_plan' => $itemPlan->nombre_item,
                        'ponderacion_item_plan' => $itemPlan->ponderacion_global,
                        'tipo' => $itemPlan->tipo_item,
                        'observacion_general' => $califGeneralRubricaDelMiembro?->observacion ?? '',
                        'rubrica_plantilla_nombre' => $itemPlan->rubricaPlantilla->nombre,
                        'componentes_evaluados' => [],
                    ];
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
                        $criteriosEvaluadosArray = [];
                        foreach ($componenteR->criteriosComponente as $criterioR) {
                            $califCriterioDelMiembro = $susCalificacionesGuardadas
                                ->where('item_plan_evaluacion_id', $itemPlan->id)
                                ->where('criterio_id', $criterioR->id)
                                ->first();

                            $opcionCalificacionElegida = null;
                            if ($califCriterioDelMiembro && $califCriterioDelMiembro->calificacion_criterio_id) {
                                $opcionCalificacionElegida = $criterioR->calificacionesCriterio
                                    ->firstWhere('id', $califCriterioDelMiembro->calificacion_criterio_id);
                            }
                            $criteriosEvaluadosArray[$criterioR->id] = [
                                'nombre_criterio_rubrica' => $criterioR->nombre,
                                'calificacion_elegida_nombre' => $opcionCalificacionElegida?->nombre ?? null,
                                'calificacion_elegida_valor' => $opcionCalificacionElegida?->valor ?? null,
                                'observacion' => $califCriterioDelMiembro?->observacion ?? '',
                            ];
                        }
                        $datosItemRubrica['componentes_evaluados'][$componenteR->id] = [
                            'nombre_componente_rubrica' => $componenteR->nombre,
                            'criterios_evaluados' => $criteriosEvaluadosArray,
                        ];
                    }
                    $calificacionesFormateadasParaEsteMiembro[$itemPlan->id] = $datosItemRubrica;
                }
            }
            if (!empty($calificacionesFormateadasParaEsteMiembro)) {
                $this->todasLasCalificacionesDelTribunal[$miembro->user_id] = [
                    'nombre_miembro' => $miembro->user->name,
                    'rol_miembro' => $miembro->status,
                    'calificaciones_ingresadas' => $calificacionesFormateadasParaEsteMiembro
                ];
            }
        }
    }
    protected function calcularNotaRubricaParaMiembro(ItemPlanEvaluacion $itemPlan, Collection $calificacionesDelMiembro)
    {
        if (!$itemPlan->rubricaPlantilla) return null;

        $puntajeTotalObtenidoMiembro = 0;
        $sumaPonderacionesComponentes = 0; // Suma de las ponderaciones INTERNAS de los componentes de la rúbrica

        foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $componenteR) {
            $sumaPonderacionesComponentes += $componenteR->ponderacion;
            $puntajeObtenidoCriteriosComp = 0;
            $maxPuntajePosibleCriteriosComp = 0;

            foreach ($componenteR->criteriosComponente as $criterioR) {
                if ($criterioR->calificacionesCriterio->isNotEmpty()) {
                    // Asumimos que el valor máximo es el de la opción con mayor valor numérico
                    $maxValorCriterio = $criterioR->calificacionesCriterio->max('valor');
                    if (is_numeric($maxValorCriterio)) {
                        $maxPuntajePosibleCriteriosComp += (float) $maxValorCriterio;
                    }
                }

                $califCriterioActual = $calificacionesDelMiembro
                    ->where('item_plan_evaluacion_id', $itemPlan->id)
                    ->where('criterio_id', $criterioR->id)
                    ->first();

                if ($califCriterioActual && $califCriterioActual->calificacion_criterio_id) {
                    $opcionElegida = $criterioR->calificacionesCriterio->firstWhere('id', $califCriterioActual->calificacion_criterio_id);
                    if ($opcionElegida && is_numeric($opcionElegida->valor)) {
                        $puntajeObtenidoCriteriosComp += (float) $opcionElegida->valor;
                    }
                }
            }
            // Calcular puntaje del componente normalizado y ponderado por su peso en la rúbrica
            if ($maxPuntajePosibleCriteriosComp > 0) {
                $puntajeTotalObtenidoMiembro += ($puntajeObtenidoCriteriosComp / $maxPuntajePosibleCriteriosComp) * $componenteR->ponderacion;
            }
        }

        if ($sumaPonderacionesComponentes > 0) {
            // Normalizar el puntaje total obtenido a una base de 100 (si la suma de ponderaciones de componentes no es 100)
            // y luego convertir a base 20.
            $notaBase100 = ($puntajeTotalObtenidoMiembro / $sumaPonderacionesComponentes) * 100;
            return ($notaBase100 / 100) * 20;
        }
        return null;
    }


    public function render()
    {
        if (!$this->tribunal && $this->tribunalId) {
            // Si loadAndPrepareTribunalData falló (ej. tribunal no encontrado)
            // el mensaje flash ya se estableció. Esta vista se mostrará con la alerta.
            return view('livewire.tribunales.profile.tribunal-profile') // Renderiza la vista normal, la alerta se mostrará
                ->layout('layouts.panel');
        }
        return view('livewire.tribunales.profile.tribunal-profile')
            ->layout('layouts.panel');
    }
}
