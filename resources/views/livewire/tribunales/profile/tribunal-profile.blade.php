<div>
    @section('title', $tribunal && $tribunal->estudiante ? 'Perfil Tribunal: ' .
        $tribunal->estudiante->nombres_completos_id : 'Perfil del Tribunal')

        <div class="container-fluid p-0">
            {{-- Breadcrumbs --}}
            @if (
                $tribunal &&
                    $tribunal->carrerasPeriodo &&
                    $tribunal->carrerasPeriodo->periodo &&
                    $tribunal->carrerasPeriodo->carrera &&
                    $tribunal->estudiante)
                <div class="fs-2 fw-semibold mb-4">
                    <a href="{{ route('periodos.') }}" class="text-decoration-none text-dark">Períodos</a> /
                    <a href="{{ route('periodos.profile', $tribunal->carrerasPeriodo->periodo->id) }}"
                        class="text-decoration-none text-dark">{{ $tribunal->carrerasPeriodo->periodo->codigo_periodo }}</a>
                    /
                    <a href="{{ route('periodos.tribunales.index', $tribunal->carrerasPeriodo->id) }}" {{-- Asegúrate que esta ruta y parámetro sean correctos --}}
                        class="text-decoration-none text-dark">{{ $tribunal->carrerasPeriodo->carrera->nombre }}</a> /
                    <span class="text-muted">{{ $tribunal->estudiante->nombres_completos_id }}</span>
                </div>
            @else
                <div class="fs-2 fw-semibold mb-4">
                    <span class="text-muted">Perfil del Tribunal</span>
                </div>
            @endif

            @include('partials.alerts')

            @if ($tribunal)
                {{-- SECCIÓN 1: DATOS DEL TRIBUNAL (Con Edición) --}}
                {{-- Incluimos la vista parcial para los datos del tribunal --}}
                @include('livewire.tribunales.profile.data-tribunal-form')

                {{-- SECCIÓN 2: RESUMEN DE CALIFICACIONES Y NOTA FINAL --}}
                @if ($usuarioPuedeVerTodasLasCalificaciones && $planEvaluacionActivo)
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-calculator-fill text-info"></i> Resumen de Calificaciones y
                                Nota del Tribunal</h5>
                        </div>
                        <div class="card-body">
                            @if (empty($resumenNotasCalculadas))
                                <p class="text-muted text-center py-3">
                                    <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>
                                    No hay ítems definidos en el Plan de Evaluación o aún no hay calificaciones para mostrar
                                    un resumen.
                                </p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ítem de Evaluación</th>
                                                <th class="text-center">Ponderación Global</th>
                                                <th class="text-center">Nota Ítem (sobre 20)</th>
                                                <th class="text-center">Puntaje Ponderado (sobre 20)</th>
                                                <th class="text-center" style="width:15%">Detalles / Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($resumenNotasCalculadas as $itemPlanId => $itemResumen)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $loop->iteration }}.
                                                            {{ $itemResumen['nombre_item_plan'] }}</strong>
                                                        @if ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR' && !empty($itemResumen['rubrica_plantilla_nombre']))
                                                            <br><small class="text-muted">Rúbrica:
                                                                {{ $itemResumen['rubrica_plantilla_nombre'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $itemResumen['ponderacion_global'] }}%</td>
                                                    <td
                                                        class="text-center fw-bold fs-5 @if (is_null($itemResumen['nota_tribunal_sobre_20'])) text-muted @else text-primary @endif">
                                                        {{ !is_null($itemResumen['nota_tribunal_sobre_20']) ? number_format($itemResumen['nota_tribunal_sobre_20'], 2) : 'N/R' }}
                                                    </td>
                                                    <td
                                                        class="text-center fw-bold fs-5 @if (($itemResumen['puntaje_ponderado_item'] ?? 0) > 0) text-success @else text-muted @endif">
                                                        {{ number_format($itemResumen['puntaje_ponderado_item'] ?? 0, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA')
                                                            @if (!empty($itemResumen['observacion_general']))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary py-1 px-2"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    title="{{ htmlspecialchars($itemResumen['observacion_general']) }}">
                                                                    <i class="bi bi-chat-left-text"></i> Ver Obs.
                                                                </button>
                                                            @else
                                                                <small class="text-muted"><em>Sin obs.</em></small>
                                                            @endif
                                                        @elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR')
                                                            @if (!empty($todasLasCalificacionesDelTribunal))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-info py-1 px-2"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#detalleRubricaModal_{{ $itemPlanId }}">
                                                                    <i class="bi bi-search"></i> Ver Detalle
                                                                </button>
                                                            @else
                                                                <small class="text-muted"><em>Detalle no
                                                                        disponible.</em></small>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-group-divider">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold fs-5 align-middle">NOTA FINAL DEL
                                                    TRIBUNAL (sobre 20):</td>
                                                <td class="text-center fw-bold fs-4 @if ($notaFinalCalculadaDelTribunal >= 14) text-success @elseif(is_numeric($notaFinalCalculadaDelTribunal)) text-danger @else text-muted @endif"
                                                    colspan="2">
                                                    {{ is_numeric($notaFinalCalculadaDelTribunal) ? number_format($notaFinalCalculadaDelTribunal, 2) : 'N/C' }}
                                                </td>
                                            </tr>
                                            @if (round($sumaPonderacionesGlobalesItems, 2) != 100.0 && $planEvaluacionActivo->itemsPlanEvaluacion->isNotEmpty())
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger small pt-2">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Advertencia: La suma
                                                        de las ponderaciones globales de los ítems en el Plan de Evaluación
                                                        ({{ number_format($sumaPonderacionesGlobalesItems, 2) }}%) no es
                                                        100%. El cálculo de la nota final podría ser incorrecto.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- Modales para el detalle de cada rúbrica por calificador --}}
                                @foreach ($resumenNotasCalculadas as $itemPlanId => $itemResumen)
                                    @if ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR' && isset($detalleRubricasParaModal[$itemPlanId]))
                                        @include(
                                            'livewire.tribunales.profile.modal-detalle-rubrica',
                                            [
                                                'itemPlanId' => $itemPlanId,
                                                'detalleItemRubrica' => $detalleRubricasParaModal[$itemPlanId], // Pasar el nuevo dato
                                            ]
                                        )
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                {{-- SECCIÓN 3: HISTORIAL DE CAMBIOS --}}
                <div class="card mb-4 shadow-sm">
                    {{-- ... (código del historial como lo tenías, sin cambios) ... --}}
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-clock-history text-success"></i> Historial de Cambios</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @if ($tribunal->logs && $tribunal->logs->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach ($tribunal->logs->sortByDesc('created_at') as $log)
                                    <li class="list-group-item py-2">
                                        <div class="d-flex w-100 justify-content-between">
                                            <strong
                                                class="mb-1">{{ Str::title(Str_replace('_', ' ', $log->accion)) }}</strong>
                                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1 small">{{ $log->descripcion }}</p>
                                        <small class="text-muted">
                                            {{ $log->created_at->isoFormat('DD MMM YYYY, hh:mm A') }}
                                            @if ($log->user)
                                                por <strong>{{ $log->user->name }}</strong>
                                            @else
                                                (Sistema)
                                            @endif
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted text-center py-3">
                                <i class="bi bi-journal-x fs-4 d-block mb-1"></i>
                                No hay historial de cambios para este tribunal.
                            </p>
                        @endif
                    </div>
                </div>


                {{-- SECCIÓN 4: ACTA --}}
                @if ($usuarioPuedeExportarActa)
                    <div class="card mb-4 shadow-sm">
                        {{-- ... (código del acta como lo tenías, sin cambios) ... --}}
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text-fill text-danger"></i> Acta del Tribunal
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Aquí se podrá generar y exportar el acta oficial del tribunal una vez que todas las
                                calificaciones estén completas y el proceso haya finalizado.</p>
                            <button class="btn btn-danger" wire:click="exportarActa" wire:loading.attr="disabled">
                                <span wire:loading wire:target="exportarActa" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <i class="bi bi-file-pdf-fill" wire:loading.remove wire:target="exportarActa"></i>
                                Exportar Acta (PDF)
                            </button>
                            {{-- Futuro: Botón para "Cerrar/Finalizar Tribunal" --}}
                        </div>
                    </div>
                @endif
            @else
                {{-- Mensaje si el tribunal no se carga --}}
                @if (!session()->has('danger'))
                    {{-- Evitar duplicar el mensaje si ya hay uno de error --}}
                    <div class="alert alert-warning text-center shadow-sm">
                        <i class="bi bi-exclamation-octagon-fill fs-3 d-block mb-2"></i>
                        Cargando datos del tribunal o el tribunal especificado no fue encontrado. <br>Por favor, verifique
                        el ID o sus permisos de acceso.
                    </div>
                @endif
            @endif
        </div>

        {{-- Script para inicializar tooltips de Bootstrap (si usas alguno) --}}
        @push('scripts')
            <script>
                document.addEventListener('livewire:load', function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                });
                // Si los tooltips se añaden dinámicamente con Livewire, necesitarías reinicializar:
                // Livewire.hook('message.processed', (message, component) => {
                //     var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                //     var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                //         // Destruir tooltip existente para evitar duplicados
                //         var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                //         if (existingTooltip) {
                //             existingTooltip.dispose();
                //         }
                //         return new bootstrap.Tooltip(tooltipTriggerEl)
                //     })
                // });
            </script>
        @endpush
    </div>
