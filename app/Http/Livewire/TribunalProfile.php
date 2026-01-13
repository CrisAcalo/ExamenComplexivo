<?php

namespace App\Http\Livewire;

use App\Helpers\ContextualAuth;
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
use App\Models\PlantillaActaWord;   // Para plantillas Word
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str; // Para Str::title y Str::lower
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

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

        // Verificar acceso contextual al tribunal
        $this->verificarAccesoTribunal();

        // Profesores disponibles para la edición de miembros (excluir Super Admin)
        $rolesExcluidosEdicion = ['Super Admin'];
        $this->profesoresDisponibles = User::whereDoesntHave('roles', function ($query) use ($rolesExcluidosEdicion) {
            $query->whereIn('name', $rolesExcluidosEdicion);
        })
            ->orderBy('name')->get();

        $this->checkUserPermissions();

        if ($this->usuarioPuedeVerTodasLasCalificaciones && $this->planEvaluacionActivo) {
            $this->calculateAndLoadAllCalificaciones();
        }
    }

    /**
     * Verifica el acceso contextual al tribunal específico
     */
    protected function verificarAccesoTribunal()
    {
        $user = auth()->user();
        $carreraPeriodoId = $this->tribunal->carrera_periodo_id;

        // Super Admin tiene acceso total
        if ($user->hasRole('Super Admin')) {
            return;
        }

        // Director y Docente de Apoyo de la carrera-período específica
        $puedeAcceder = ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodoId) ||
            ContextualAuth::isMemberOfTribunal($user, $this->tribunalId);

        if (!$puedeAcceder) {
            abort(403, 'No tienes permisos para acceder a este tribunal.');
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

        $carreraPeriodoId = $this->tribunal->carrera_periodo_id;

        // Super Admin tiene acceso total
        if ($user->hasRole('Super Admin')) {
            $this->usuarioPuedeEditarDatosTribunal = true;
            $this->usuarioPuedeVerTodasLasCalificaciones = true;
            $this->usuarioPuedeExportarActa = $this->tribunal->estado === 'CERRADO'; // Solo si está cerrado
            return;
        }

        // Director y Docente de Apoyo de esta carrera-período específica
        $esDirectorOApoyo = ContextualAuth::canAccessCarreraPeriodo($user, $carreraPeriodoId);

        // Verificar si es miembro del tribunal
        $esMiembroTribunal = ContextualAuth::isMemberOfTribunal($user, $this->tribunalId);

        if ($esDirectorOApoyo) {
            // Director/Apoyo pueden editar datos, ver calificaciones y exportar actas
            $this->usuarioPuedeEditarDatosTribunal = true;
            $this->usuarioPuedeVerTodasLasCalificaciones = true;
            $this->usuarioPuedeExportarActa = $this->tribunal->estado === 'CERRADO'; // Solo si está cerrado
        } elseif ($esMiembroTribunal) {
            // Miembros del tribunal no pueden editar datos básicos, pero pueden ver calificaciones y exportar acta
            $this->usuarioPuedeEditarDatosTribunal = false;
            $this->usuarioPuedeVerTodasLasCalificaciones = true;
            $this->usuarioPuedeExportarActa = $this->tribunal->estado === 'CERRADO'; // Solo si está cerrado
        } else {
            // Sin acceso
            $this->usuarioPuedeEditarDatosTribunal = false;
            $this->usuarioPuedeVerTodasLasCalificaciones = false;
            $this->usuarioPuedeExportarActa = false;
        }
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
        if (!$this->usuarioPuedeEditarDatosTribunal) {
            session()->flash('danger', 'No tienes permisos para editar los datos de este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($this->tribunal->estado === 'CERRADO') {
            session()->flash('danger', 'No se puede editar un tribunal cerrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

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
    }

    public function actualizarDatosTribunal()
    {
        if (!$this->usuarioPuedeEditarDatosTribunal) {
            session()->flash('danger', 'No tienes permisos para actualizar este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($this->tribunal->estado === 'CERRADO') {
            session()->flash('danger', 'No se puede actualizar un tribunal cerrado.');
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

    public function cerrarTribunal()
    {
        if (!$this->usuarioPuedeEditarDatosTribunal) {
            session()->flash('danger', 'No tienes permisos para cerrar este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($this->tribunal->estado === 'CERRADO') {
            session()->flash('info', 'El tribunal ya está cerrado.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        DB::transaction(function () {
            $this->tribunal->update(['estado' => 'CERRADO']);

            TribunalLog::create([
                'tribunal_id' => $this->tribunal->id,
                'user_id' => Auth::id(),
                'accion' => 'CIERRE_TRIBUNAL',
                'descripcion' => 'Tribunal cerrado. No se permitirán más modificaciones ni evaluaciones.',
                'datos_antiguos' => ['estado' => 'ABIERTO'],
                'datos_nuevos' => ['estado' => 'CERRADO']
            ]);
        });

        session()->flash('success', 'Tribunal cerrado exitosamente. No se permitirán más modificaciones ni evaluaciones.');
        $this->loadAndPrepareTribunalData();
        $this->checkUserPermissions(); // Recalcular permisos después del cambio de estado
        $this->dispatchBrowserEvent('showFlashMessage');
    }

    public function abrirTribunal()
    {
        if (!$this->usuarioPuedeEditarDatosTribunal) {
            session()->flash('danger', 'No tienes permisos para abrir este tribunal.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        if ($this->tribunal->estado === 'ABIERTO') {
            session()->flash('info', 'El tribunal ya está abierto.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Validar que el tribunal esté dentro de su franja horaria
        $fechaHoraActual = now();
        $fechaHoraFinTribunal = \Carbon\Carbon::parse($this->tribunal->fecha . ' ' . $this->tribunal->hora_fin);

        if ($fechaHoraActual->greaterThan($fechaHoraFinTribunal)) {
            session()->flash('warning', 'No se puede abrir el tribunal. La franja horaria ya ha finalizado (' .
                $fechaHoraFinTribunal->format('d/m/Y H:i') . ').');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        DB::transaction(function () {
            $this->tribunal->update(['estado' => 'ABIERTO']);

            // Registrar log
            TribunalLog::create([
                'tribunal_id' => $this->tribunal->id,
                'user_id' => Auth::id(),
                'accion' => 'APERTURA_TRIBUNAL',
                'descripcion' => 'Tribunal abierto. Se permiten modificaciones y evaluaciones.',
                'datos_antiguos' => ['estado' => 'CERRADO'],
                'datos_nuevos' => ['estado' => 'ABIERTO']
            ]);
        });

        session()->flash('success', 'Tribunal abierto exitosamente. Se permiten modificaciones y evaluaciones.');
        $this->loadAndPrepareTribunalData(); // Recargar datos
        $this->checkUserPermissions(); // Recalcular permisos después del cambio de estado
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

                // Generar componentes individuales de rúbrica como elementos separados
                if ($itemPlan->rubricaPlantilla && $itemPlan->rubricaPlantilla->componentesRubrica) {
                    foreach ($itemPlan->rubricaPlantilla->componentesRubrica as $compR) {
                        $asignacionComp = $itemPlan->asignacionesCalificadorComponentes->firstWhere('componente_rubrica_id', $compR->id);
                        if ($asignacionComp && isset($notasRubricaPorGrupoCalificador[$asignacionComp->calificado_por][$compR->id])) {
                            // Calcular la nota del componente sobre 20
                            $notaComponenteSobre20 = ($notasRubricaPorGrupoCalificador[$asignacionComp->calificado_por][$compR->id] / $compR->ponderacion) * 20;

                            // Calcular la ponderación de este componente dentro del ítem total
                            $ponderacionComponenteDentroDelItem = ($compR->ponderacion / $sumaPonderacionesDeComponentesUsados) * $itemPlan->ponderacion_global;

                            // Calcular el puntaje ponderado del componente
                            $puntajePonderadoComponente = $notaComponenteSobre20 * ($ponderacionComponenteDentroDelItem / 100);

                            // Agregar como elemento separado
                            $this->resumenNotasCalculadas['componente_' . $compR->id . '_item_' . $itemPlan->id] = [
                                'nombre_item_plan' => $compR->nombre,
                                'ponderacion_global' => $ponderacionComponenteDentroDelItem,
                                'tipo_item' => 'RUBRICA_COMPONENTE',
                                'rubrica_plantilla_nombre' => $itemPlan->rubricaPlantilla->nombre,
                                'nota_tribunal_sobre_20' => round($notaComponenteSobre20, 2),
                                'puntaje_ponderado_item' => round($puntajePonderadoComponente, 2),
                                'observacion_general' => '',
                            ];
                        }
                    }
                }

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
                                    'apellido_usuario' => User::find($userIdCalificador)?->lastname ?? '',
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

        // Debug temporal
        logger('DEBUG TribunalProfile - Total items en detalleRubricasParaModal: ' . count($this->detalleRubricasParaModal));
        logger('DEBUG TribunalProfile - Total en todasLasCalificacionesDelTribunal: ' . count($this->todasLasCalificacionesDelTribunal));
        foreach ($this->detalleRubricasParaModal as $itemId => $itemData) {
            logger("DEBUG TribunalProfile - Item $itemId: " . json_encode($itemData));
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
        try {
            // Verificar permisos
            if (!$this->usuarioPuedeExportarActa) {
                session()->flash('danger', 'No tienes permisos para exportar el acta.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el tribunal esté cerrado
            if ($this->tribunal->estado !== 'CERRADO') {
                session()->flash('danger', 'El acta solo puede exportarse cuando el tribunal esté cerrado.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Obtener datos necesarios para el PDF
            $tribunal = $this->tribunal;
            $planEvaluacionActivo = $this->planEvaluacionActivo;
            $resumenNotasCalculadas = $this->resumenNotasCalculadas;
            $todasLasCalificacionesDelTribunal = $this->todasLasCalificacionesDelTribunal;
            $notaFinalCalculadaDelTribunal = $this->notaFinalCalculadaDelTribunal;

            // Convertir logo a base64 para que funcione en PDF
            $logoPath = public_path('storage/logos/LOGO-ESPE_lg.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }

            // Validar que tenemos los datos necesarios
            if (!$tribunal) {
                throw new \Exception('Datos del tribunal no disponibles');
            }

            $options = new Options();
            $options->set('defaultFont', 'Arial');
            //tamaño de fuente
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('debugPng', false);
            $options->set('debugKeepTemp', false);
            $options->set('debugCss', false);

            $dompdf = new Dompdf($options);

            try {
                // Generar HTML desde vista blade
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
                'tribunal_id' => $this->tribunal->id ?? null,
                'usuario_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function exportarActaWord()
    {
        try {
            // Verificar permisos
            if (!$this->usuarioPuedeExportarActa) {
                session()->flash('danger', 'No tienes permisos para exportar el acta.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el tribunal esté cerrado
            if ($this->tribunal->estado !== 'CERRADO') {
                session()->flash('danger', 'El acta solo puede exportarse cuando el tribunal esté cerrado.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Obtener plantilla activa
            $plantillaActiva = PlantillaActaWord::obtenerPlantillaActiva();

            if (!$plantillaActiva) {
                session()->flash('warning', 'No hay una plantilla Word activa. Por favor, sube y activa una plantilla primero.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el archivo exista
            $archivoPath = storage_path('app/public/' . $plantillaActiva->archivo_path);
            if (!file_exists($archivoPath)) {
                session()->flash('danger', 'El archivo de plantilla no existe en el servidor.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Cargar plantilla con TemplateProcessor
            $templateProcessor = new TemplateProcessor($archivoPath);

            // Preparar datos del tribunal
            $estudiante = $this->tribunal->estudiante;
            $carrera = $this->tribunal->carrerasPeriodo->carrera ?? null;
            $periodo = $this->tribunal->carrerasPeriodo->periodo ?? null;
            $presidente = $this->tribunal->miembrosTribunales->where('status', 'PRESIDENTE')->first();
            $integrante1 = $this->tribunal->miembrosTribunales->where('status', 'INTEGRANTE1')->first();
            $integrante2 = $this->tribunal->miembrosTribunales->where('status', 'INTEGRANTE2')->first();
            $director = $this->tribunal->carrerasPeriodo->director ?? null;

            // Procesar nombre de carrera (igual que en la vista)
            $nombreCarrera = $carrera->nombre ?? 'N/A';
            $nombreCarreraMayus = mb_strtoupper($nombreCarrera, 'UTF-8');
            $partes = explode(' ', $nombreCarreraMayus);
            if (count($partes) > 2) {
                array_splice($partes, -2);
            }
            $nombreCarreraFinal = implode(' ', $partes);

            // Separar notas por componentes (igual que en el PDF hardcodeado)
            $itemsNotaDirecta = [];
            $itemsRubrica = [];
            foreach ($this->resumenNotasCalculadas as $itemPlanId => $itemResumen) {
                if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                    $itemsNotaDirecta[] = $itemResumen;
                } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR') {
                    $itemsRubrica[] = $itemResumen;
                }
            }

            $notaComponenteTeorico = !empty($itemsNotaDirecta) ? ($itemsNotaDirecta[0]['puntaje_ponderado_item'] ?? 0) : 0;
            $notaComponentePractico = !empty($itemsRubrica) ? ($itemsRubrica[0]['puntaje_ponderado_item'] ?? 0) : 0;

            // Reemplazar variables
            $templateProcessor->setValue('tribunal_id', $this->tribunal->id ?? 'N/A');
            $templateProcessor->setValue('estudiante_nombre', $estudiante->nombres ?? 'N/A');
            $templateProcessor->setValue('estudiante_apellidos', $estudiante->apellidos ?? 'N/A');
            $templateProcessor->setValue('estudiante_cedula', $estudiante->cedula ?? 'N/A');
            $templateProcessor->setValue('carrera_nombre', $nombreCarreraFinal);
            $templateProcessor->setValue('carrera_modalidad', $carrera->modalidad ?? 'N/A');
            $templateProcessor->setValue('periodo_codigo', $periodo->codigo_periodo ?? 'N/A');
            $templateProcessor->setValue('fecha_examen', $this->tribunal->fecha ? \Carbon\Carbon::parse($this->tribunal->fecha)->format('d/m/Y') : 'N/A');
            $templateProcessor->setValue('presidente_nombre', $presidente ? ($presidente->user->name ?? '') . ' ' . ($presidente->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('presidente_cedula', $presidente ? str_pad($presidente->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('integrante1_nombre', $integrante1 ? ($integrante1->user->name ?? '') . ' ' . ($integrante1->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('integrante1_cedula', $integrante1 ? str_pad($integrante1->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('integrante2_nombre', $integrante2 ? ($integrante2->user->name ?? '') . ' ' . ($integrante2->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('integrante2_cedula', $integrante2 ? str_pad($integrante2->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('director_nombre', $director ? ($director->name ?? '') . ' ' . ($director->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('director_cedula', $director ? str_pad($director->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('nota_componente_teorico', number_format($notaComponenteTeorico, 2));
            $templateProcessor->setValue('nota_componente_practico', number_format($notaComponentePractico, 2));
            $templateProcessor->setValue('nota_final', number_format($this->notaFinalCalculadaDelTribunal, 2));
            $templateProcessor->setValue('nota_final_letras', $this->numeroALetrasConPunto($this->notaFinalCalculadaDelTribunal));
            $templateProcessor->setValue('aprobado', $this->notaFinalCalculadaDelTribunal >= 14 ? 'SÍ' : 'NO');
            $templateProcessor->setValue('aprobado_si', $this->notaFinalCalculadaDelTribunal >= 14 ? 'X' : '');
            $templateProcessor->setValue('aprobado_no', $this->notaFinalCalculadaDelTribunal < 14 ? 'X' : '');
            $templateProcessor->setValue('fecha_actual', \Carbon\Carbon::now()->format('d/m/Y'));

            // Datos del estudiante
            $templateProcessor->setValue('estudiante_id', $estudiante->ID_estudiante ?? 'N/A');
            $templateProcessor->setValue('estudiante_nombre_completo', 
                ($estudiante->apellidos ?? '') . ' ' . ($estudiante->nombres ?? ''));

            // Procesar nombre de carrera (eliminar últimas dos palabras como en la vista)
            $nombreCarrera = $carrera->nombre ?? 'N/A';
            $nombreCarreraMayus = mb_strtoupper($nombreCarrera, 'UTF-8');
            $partes = explode(' ', $nombreCarreraMayus);
            if (count($partes) > 2) {
                array_splice($partes, -2);
            }
            $nombreCarreraFinal = implode(' ', $partes);
            $templateProcessor->setValue('carrera_nombre_procesado', $nombreCarreraFinal);

            // Fechas en diferentes formatos
            $fechaExamen = $this->tribunal->fecha ? \Carbon\Carbon::parse($this->tribunal->fecha) : \Carbon\Carbon::now();
            $templateProcessor->setValue('fecha_examen_formato_completo', $fechaExamen->format('d/m/Y'));
            $templateProcessor->setValue('fecha_formato_barra', $fechaExamen->format('d/m/Y'));
            $templateProcessor->setValue('fecha_formato_mes_dia_ano', $fechaExamen->format('m/d/Y'));

            // Calcular notas por componente (similar a la lógica del PDF hardcodeado)
            $notaTeoricoSobre20 = 0;
            $notaPracticoSobre20 = 0;
            $ponderacionTeorico = 0;
            $ponderacionPractico = 0;
            $calificacionTeoricoPonderada = 0;
            $calificacionPracticoPonderada = 0;

            if (!empty($this->resumenNotasCalculadas)) {
                foreach ($this->resumenNotasCalculadas as $itemResumen) {
                    if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                        $notaTeoricoSobre20 = $itemResumen['nota_tribunal_sobre_20'] ?? 0;
                        $ponderacionTeorico = $itemResumen['ponderacion_global'] ?? 50;
                        $calificacionTeoricoPonderada = $itemResumen['puntaje_ponderado_item'] ?? 0;
                    } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR' || 
                            $itemResumen['tipo_item'] === 'RUBRICA_COMPONENTE') {
                        $notaPracticoSobre20 = $itemResumen['nota_tribunal_sobre_20'] ?? 0;
                        $ponderacionPractico = $itemResumen['ponderacion_global'] ?? 50;
                        $calificacionPracticoPonderada = $itemResumen['puntaje_ponderado_item'] ?? 0;
                    }
                }
            }

            // Variables de calificación detallada
            $templateProcessor->setValue('nota_teorico_sobre_20', number_format($notaTeoricoSobre20, 2));
            $templateProcessor->setValue('ponderacion_teorico', intval($ponderacionTeorico));
            $templateProcessor->setValue('calificacion_teorico_ponderada', number_format($calificacionTeoricoPonderada, 2));

            $templateProcessor->setValue('nota_practico_sobre_20', number_format($notaPracticoSobre20, 2));
            $templateProcessor->setValue('ponderacion_practico', intval($ponderacionPractico));
            $templateProcessor->setValue('calificacion_practico_ponderada', number_format($calificacionPracticoPonderada, 2));

            // Variables para componentes individuales de rúbrica
            $componentesRubrica = [];
            foreach ($this->resumenNotasCalculadas as $itemResumen) {
                if ($itemResumen['tipo_item'] === 'RUBRICA_COMPONENTE') {
                    $componentesRubrica[] = $itemResumen;
                }
            }

            // Componente 1
            if (isset($componentesRubrica[0])) {
                $templateProcessor->setValue('componente1_nombre', 'Parte escrita (resolución del problema profesional / estudio de caso)');
                $templateProcessor->setValue('componente1_nota', number_format($componentesRubrica[0]['nota_tribunal_sobre_20'] ?? 0, 2));
                $templateProcessor->setValue('componente1_ponderacion', number_format(($componentesRubrica[0]['ponderacion_global'] ?? 0) * 2, 0));
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente1_calificacion_ponderada', number_format($componentesRubrica[0]['puntaje_ponderado_item'] ?? 0, 2));
            } else {
                $templateProcessor->setValue('componente1_nombre', 'N/A');
                $templateProcessor->setValue('componente1_nota', '0.00');
                $templateProcessor->setValue('componente1_ponderacion', '0');
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente1_calificacion_ponderada', '0.00');
            }

            // Componente 2
            if (isset($componentesRubrica[1])) {
                $templateProcessor->setValue('componente2_nombre', 'Defensa / sustentación/ exposición oral');
                $templateProcessor->setValue('componente2_nota', number_format($componentesRubrica[1]['nota_tribunal_sobre_20'] ?? 0, 2));
                $templateProcessor->setValue('componente2_ponderacion', number_format(($componentesRubrica[1]['ponderacion_global'] ?? 0) * 2, 0));
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente2_calificacion_ponderada', number_format($componentesRubrica[1]['puntaje_ponderado_item'] ?? 0, 2));
            } else {
                $templateProcessor->setValue('componente2_nombre', 'N/A');
                $templateProcessor->setValue('componente2_nota', '0.00');
                $templateProcessor->setValue('componente2_ponderacion', '0');
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente2_calificacion_ponderada', '0.00');
            }

            // Generar nombre del archivo
            $nombreEstudiante = $estudiante->nombres_completos_id ?? 'Estudiante';
            $nombreEstudiante = Str::slug($nombreEstudiante, '_');
            $fecha = $this->tribunal->fecha ? date('Y-m-d', strtotime($this->tribunal->fecha)) : date('Y-m-d');
            $nombreArchivo = "acta_tribunal_{$nombreEstudiante}_{$fecha}.docx";

            // Guardar Word temporal
            $tempPath = storage_path('app/temp/' . $nombreArchivo);
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            $templateProcessor->saveAs($tempPath);

            session()->flash('success', 'Acta Word generada exitosamente.');
            $this->dispatchBrowserEvent('showFlashMessage');
            $this->dispatchBrowserEvent('downloadFile', ['path' => $nombreArchivo]);
        } catch (\Exception $e) {
            session()->flash('danger', 'Error al generar el acta Word: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
            Log::error('Error al exportar acta Word: ' . $e->getMessage(), [
                'tribunal_id' => $this->tribunal->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function exportarActaPdfDesdeWord()
    {
        try {
            // Verificar permisos
            if (!$this->usuarioPuedeExportarActa) {
                session()->flash('danger', 'No tienes permisos para exportar el acta.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el tribunal esté cerrado
            if ($this->tribunal->estado !== 'CERRADO') {
                session()->flash('danger', 'El acta solo puede exportarse cuando el tribunal esté cerrado.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Obtener plantilla activa
            $plantillaActiva = PlantillaActaWord::obtenerPlantillaActiva();

            if (!$plantillaActiva) {
                session()->flash('warning', 'No hay una plantilla Word activa. Por favor, sube y activa una plantilla primero.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Verificar que el archivo exista
            $archivoPath = storage_path('app/public/' . $plantillaActiva->archivo_path);
            if (!file_exists($archivoPath)) {
                session()->flash('danger', 'El archivo de plantilla no existe en el servidor.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            // Cargar plantilla con TemplateProcessor
            $templateProcessor = new TemplateProcessor($archivoPath);

            // Preparar datos del tribunal
            $estudiante = $this->tribunal->estudiante;
            $carrera = $this->tribunal->carrerasPeriodo->carrera ?? null;
            $periodo = $this->tribunal->carrerasPeriodo->periodo ?? null;
            $presidente = $this->tribunal->miembrosTribunales->where('status', 'PRESIDENTE')->first();
            $integrante1 = $this->tribunal->miembrosTribunales->where('status', 'INTEGRANTE1')->first();
            $integrante2 = $this->tribunal->miembrosTribunales->where('status', 'INTEGRANTE2')->first();
            $director = $this->tribunal->carrerasPeriodo->director ?? null;

            // Procesar nombre de carrera
            $nombreCarrera = $carrera->nombre ?? 'N/A';
            $nombreCarreraMayus = mb_strtoupper($nombreCarrera, 'UTF-8');
            $partes = explode(' ', $nombreCarreraMayus);
            if (count($partes) > 2) {
                array_splice($partes, -2);
            }
            $nombreCarreraFinal = implode(' ', $partes);

            // Separar notas por componentes (igual que en el PDF hardcodeado)
            $itemsNotaDirecta = [];
            $itemsRubrica = [];
            foreach ($this->resumenNotasCalculadas as $itemPlanId => $itemResumen) {
                if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                    $itemsNotaDirecta[] = $itemResumen;
                } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR') {
                    $itemsRubrica[] = $itemResumen;
                }
            }

            $notaComponenteTeorico = !empty($itemsNotaDirecta) ? ($itemsNotaDirecta[0]['puntaje_ponderado_item'] ?? 0) : 0;
            $notaComponentePractico = !empty($itemsRubrica) ? ($itemsRubrica[0]['puntaje_ponderado_item'] ?? 0) : 0;

            // Reemplazar variables
            $templateProcessor->setValue('tribunal_id', $this->tribunal->id ?? 'N/A');
            $templateProcessor->setValue('estudiante_nombre', $estudiante->nombres ?? 'N/A');
            $templateProcessor->setValue('estudiante_apellidos', $estudiante->apellidos ?? 'N/A');
            $templateProcessor->setValue('estudiante_cedula', $estudiante->cedula ?? 'N/A');
            $templateProcessor->setValue('carrera_nombre', $nombreCarreraFinal);
            $templateProcessor->setValue('carrera_modalidad', $carrera->modalidad ?? 'N/A');
            $templateProcessor->setValue('periodo_codigo', $periodo->codigo_periodo ?? 'N/A');
            $templateProcessor->setValue('fecha_examen', $this->tribunal->fecha ? \Carbon\Carbon::parse($this->tribunal->fecha)->format('d/m/Y') : 'N/A');
            $templateProcessor->setValue('presidente_nombre', $presidente ? ($presidente->user->name ?? '') . ' ' . ($presidente->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('presidente_cedula', $presidente ? str_pad($presidente->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('integrante1_nombre', $integrante1 ? ($integrante1->user->name ?? '') . ' ' . ($integrante1->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('integrante1_cedula', $integrante1 ? str_pad($integrante1->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('integrante2_nombre', $integrante2 ? ($integrante2->user->name ?? '') . ' ' . ($integrante2->user->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('integrante2_cedula', $integrante2 ? str_pad($integrante2->user->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('director_nombre', $director ? ($director->name ?? '') . ' ' . ($director->lastname ?? '') : 'N/A');
            $templateProcessor->setValue('director_cedula', $director ? str_pad($director->cedula ?? '', 10, '0', STR_PAD_LEFT) : 'N/A');
            $templateProcessor->setValue('nota_componente_teorico', number_format($notaComponenteTeorico, 2));
            $templateProcessor->setValue('nota_componente_practico', number_format($notaComponentePractico, 2));
            $templateProcessor->setValue('nota_final', number_format($this->notaFinalCalculadaDelTribunal, 2));
            $templateProcessor->setValue('nota_final_letras', $this->numeroALetrasConPunto($this->notaFinalCalculadaDelTribunal));
            $templateProcessor->setValue('aprobado', $this->notaFinalCalculadaDelTribunal >= 14 ? 'SÍ' : 'NO');
            $templateProcessor->setValue('aprobado_si', $this->notaFinalCalculadaDelTribunal >= 14 ? 'X' : '');
            $templateProcessor->setValue('aprobado_no', $this->notaFinalCalculadaDelTribunal < 14 ? 'X' : '');
            $templateProcessor->setValue('fecha_actual', \Carbon\Carbon::now()->format('d/m/Y'));

            // Datos del estudiante
            $templateProcessor->setValue('estudiante_id', $estudiante->ID_estudiante ?? 'N/A');
            $templateProcessor->setValue('estudiante_nombre_completo', 
                ($estudiante->apellidos ?? '') . ' ' . ($estudiante->nombres ?? ''));

            // Procesar nombre de carrera (eliminar últimas dos palabras como en la vista)
            $nombreCarrera = $carrera->nombre ?? 'N/A';
            $nombreCarreraMayus = mb_strtoupper($nombreCarrera, 'UTF-8');
            $partes = explode(' ', $nombreCarreraMayus);
            if (count($partes) > 2) {
                array_splice($partes, -2);
            }
            $nombreCarreraFinal = implode(' ', $partes);
            $templateProcessor->setValue('carrera_nombre_procesado', $nombreCarreraFinal);

            // Fechas en diferentes formatos
            $fechaExamen = $this->tribunal->fecha ? \Carbon\Carbon::parse($this->tribunal->fecha) : \Carbon\Carbon::now();
            $templateProcessor->setValue('fecha_examen_formato_completo', $fechaExamen->format('d/m/Y'));
            $templateProcessor->setValue('fecha_formato_barra', $fechaExamen->format('d/m/Y'));
            $templateProcessor->setValue('fecha_formato_mes_dia_ano', $fechaExamen->format('m/d/Y'));

            // Calcular notas por componente (similar a la lógica del PDF hardcodeado)
            $notaTeoricoSobre20 = 0;
            $notaPracticoSobre20 = 0;
            $ponderacionTeorico = 0;
            $ponderacionPractico = 0;
            $calificacionTeoricoPonderada = 0;
            $calificacionPracticoPonderada = 0;

            if (!empty($this->resumenNotasCalculadas)) {
                foreach ($this->resumenNotasCalculadas as $itemResumen) {
                    if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                        $notaTeoricoSobre20 = $itemResumen['nota_tribunal_sobre_20'] ?? 0;
                        $ponderacionTeorico = $itemResumen['ponderacion_global'] ?? 50;
                        $calificacionTeoricoPonderada = $itemResumen['puntaje_ponderado_item'] ?? 0;
                    } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR' || 
                            $itemResumen['tipo_item'] === 'RUBRICA_COMPONENTE') {
                        $notaPracticoSobre20 = $itemResumen['nota_tribunal_sobre_20'] ?? 0;
                        $ponderacionPractico = $itemResumen['ponderacion_global'] ?? 50;
                        $calificacionPracticoPonderada = $itemResumen['puntaje_ponderado_item'] ?? 0;
                    }
                }
            }

            // Variables de calificación detallada
            $templateProcessor->setValue('nota_teorico_sobre_20', number_format($notaTeoricoSobre20, 2));
            $templateProcessor->setValue('ponderacion_teorico', intval($ponderacionTeorico));
            $templateProcessor->setValue('calificacion_teorico_ponderada', number_format($calificacionTeoricoPonderada, 2));

            $templateProcessor->setValue('nota_practico_sobre_20', number_format($notaPracticoSobre20, 2));
            $templateProcessor->setValue('ponderacion_practico', intval($ponderacionPractico));
            $templateProcessor->setValue('calificacion_practico_ponderada', number_format($calificacionPracticoPonderada, 2));

            // Variables para componentes individuales de rúbrica
            $componentesRubrica = [];
            foreach ($this->resumenNotasCalculadas as $itemResumen) {
                if ($itemResumen['tipo_item'] === 'RUBRICA_COMPONENTE') {
                    $componentesRubrica[] = $itemResumen;
                }
            }

            // Componente 1
            if (isset($componentesRubrica[0])) {
                $templateProcessor->setValue('componente1_nombre', 'Parte escrita (resolución del problema profesional / estudio de caso)');
                $templateProcessor->setValue('componente1_nota', number_format($componentesRubrica[0]['nota_tribunal_sobre_20'] ?? 0, 2));
                $templateProcessor->setValue('componente1_ponderacion', number_format(($componentesRubrica[0]['ponderacion_global'] ?? 0) * 2, 0));
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente1_calificacion_ponderada', number_format($componentesRubrica[0]['puntaje_ponderado_item'] ?? 0, 2));
            } else {
                $templateProcessor->setValue('componente1_nombre', 'N/A');
                $templateProcessor->setValue('componente1_nota', '0.00');
                $templateProcessor->setValue('componente1_ponderacion', '0');
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente1_calificacion_ponderada', '0.00');
            }

            // Componente 2
            if (isset($componentesRubrica[1])) {
                $templateProcessor->setValue('componente2_nombre', 'Defensa / sustentación/ exposición oral');
                $templateProcessor->setValue('componente2_nota', number_format($componentesRubrica[1]['nota_tribunal_sobre_20'] ?? 0, 2));
                $templateProcessor->setValue('componente2_ponderacion', number_format(($componentesRubrica[1]['ponderacion_global'] ?? 0) * 2, 0));
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente2_calificacion_ponderada', number_format($componentesRubrica[1]['puntaje_ponderado_item'] ?? 0, 2));
            } else {
                $templateProcessor->setValue('componente2_nombre', 'N/A');
                $templateProcessor->setValue('componente2_nota', '0.00');
                $templateProcessor->setValue('componente2_ponderacion', '0');
                // AGREGAR ESTA LÍNEA:
                $templateProcessor->setValue('componente2_calificacion_ponderada', '0.00');
            }

            // Generar nombre del archivo
            $nombreEstudiante = $estudiante->nombres_completos_id ?? 'Estudiante';
            $nombreEstudiante = Str::slug($nombreEstudiante, '_');
            $fecha = $this->tribunal->fecha ? date('Y-m-d', strtotime($this->tribunal->fecha)) : date('Y-m-d');
            $nombreArchivoWord = "acta_tribunal_{$nombreEstudiante}_{$fecha}.docx";
            $nombreArchivoPdf = "acta_tribunal_{$nombreEstudiante}_{$fecha}.pdf";

            // Guardar Word temporal
            $tempWordPath = storage_path('app/temp/' . $nombreArchivoWord);
            if (!file_exists(dirname($tempWordPath))) {
                mkdir(dirname($tempWordPath), 0755, true);
            }
            $templateProcessor->saveAs($tempWordPath);

            // Configurar renderer de PDF para PHPWord
            \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/mpdf/mpdf'));
            \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_MPDF);

            // Convertir Word a PDF usando PHPWord
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempWordPath);
            $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');

            $tempPdfPath = storage_path('app/temp/' . $nombreArchivoPdf);
            $pdfWriter->save($tempPdfPath);

            // Eliminar Word temporal
            if (file_exists($tempWordPath)) {
                unlink($tempWordPath);
            }

            session()->flash('success', 'Acta PDF (desde Word) generada exitosamente.');
            $this->dispatchBrowserEvent('showFlashMessage');
            $this->dispatchBrowserEvent('downloadFile', ['path' => $nombreArchivoPdf]);
        } catch (\Exception $e) {
            session()->flash('danger', 'Error al generar el acta PDF desde Word: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
            Log::error('Error al exportar acta PDF desde Word: ' . $e->getMessage(), [
                'tribunal_id' => $this->tribunal->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function descargarActaFirmada()
    {
        if (!$this->tribunal || !$this->tribunal->acta_firmada_path) {
            session()->flash('danger', 'No hay acta firmada disponible para descargar.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        // Verificar permisos usando Gate
        if (!Gate::allows('descargar-acta-firmada-de-este-tribunal', $this->tribunal)) {
            session()->flash('danger', 'No tienes permisos para descargar esta acta firmada.');
            $this->dispatchBrowserEvent('showFlashMessage');
            return;
        }

        try {
            if (!Storage::disk('private')->exists($this->tribunal->acta_firmada_path)) {
                session()->flash('danger', 'El archivo del acta firmada no existe en el servidor.');
                $this->dispatchBrowserEvent('showFlashMessage');
                return;
            }

            $nombreEstudiante = $this->tribunal->estudiante
                ? str_replace(' ', '_', $this->tribunal->estudiante->apellidos . '_' . $this->tribunal->estudiante->nombres)
                : 'tribunal_' . $this->tribunal->id;

            $nombreArchivo = 'Acta_Firmada_' . $nombreEstudiante . '_' . date('Y-m-d') . '.pdf';

            return Storage::disk('private')->download($this->tribunal->acta_firmada_path, $nombreArchivo);

        } catch (\Exception $e) {
            session()->flash('danger', 'Error al descargar el acta firmada: ' . $e->getMessage());
            $this->dispatchBrowserEvent('showFlashMessage');
            Log::error('Error al descargar acta firmada: ' . $e->getMessage(), [
                'tribunal_id' => $this->tribunal->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function numeroALetrasConPunto($numero): string
    {
        $numeros = [
            0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO',
            5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
            15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO',
            19 => 'DIECINUEVE', 20 => 'VEINTE', 30 => 'TREINTA', 40 => 'CUARENTA',
            50 => 'CINCUENTA', 60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA',
            90 => 'NOVENTA', 99 => 'NOVENTA Y NUEVE',
        ];

        $parteEntera = floor($numero);
        $parteDecimal = round(($numero - $parteEntera) * 100);

        $textoEntera = $numeros[$parteEntera] ?? 'ERROR';
        $textoDecimal = $numeros[$parteDecimal] ?? 'ERROR';

        return number_format($numero, 2) . ' ' . $textoEntera . ' PUNTO ' . $textoDecimal;
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
