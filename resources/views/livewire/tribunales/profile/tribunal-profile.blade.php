@section('title', $tribunal && $tribunal->estudiante ? 'Perfil Tribunal: ' .
    $tribunal->estudiante->nombres_completos_id : 'Perfil del Tribunal')
<div class="container-fluid p-0">
    <!-- Banner Verde ESPE -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                        <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                             style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                    @else
                        <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                            <i class="bi bi-people-fill fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            PERFIL DEL TRIBUNAL
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            @if ($tribunal && $tribunal->estudiante)
                                {{ $tribunal->estudiante->nombres_completos_id }}
                            @else
                                Gestión de información del tribunal
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumbs -->
    @if (
        $tribunal &&
            $tribunal->carrerasPeriodo &&
            $tribunal->carrerasPeriodo->periodo &&
            $tribunal->carrerasPeriodo->carrera &&
            $tribunal->estudiante)
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('periodos.') }}" class="text-decoration-none">
                        <i class="bi bi-calendar-event me-1"></i>Períodos
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('periodos.profile', $tribunal->carrerasPeriodo->periodo->id) }}"
                       class="text-decoration-none">
                        {{ $tribunal->carrerasPeriodo->periodo->codigo_periodo }}
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('periodos.tribunales.index', $tribunal->carrerasPeriodo->id) }}"
                       class="text-decoration-none">
                        {{ $tribunal->carrerasPeriodo->carrera->nombre }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $tribunal->estudiante->nombres_completos_id }}
                </li>
            </ol>
        </nav>
    @endif

    @include('partials.alerts')

            @if ($tribunal)
                {{-- SECCIÓN 1: DATOS DEL TRIBUNAL (Con Edición) --}}
                {{-- Incluimos la vista parcial para los datos del tribunal --}}
                @include('livewire.tribunales.profile.data-tribunal-form')

                {{-- SECCIÓN 2: RESUMEN DE CALIFICACIONES Y NOTA FINAL --}}
                @if ($usuarioPuedeVerTodasLasCalificaciones && $planEvaluacionActivo)
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-calculator-fill me-2"></i>Resumen de Calificaciones y Nota del Tribunal
                            </h5>
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
                                    <table class="table table-hover align-middle mb-0">
                                        <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                            <tr>
                                                <th class="fw-semibold"><i class="bi bi-list-ol me-1"></i>Ítem de Evaluación</th>
                                                <th class="text-center fw-semibold"><i class="bi bi-percent me-1"></i>Ponderación Global</th>
                                                <th class="text-center fw-semibold"><i class="bi bi-clipboard-check me-1"></i>Nota Ítem (sobre 20)</th>
                                                <th class="text-center fw-semibold"><i class="bi bi-calculator me-1"></i>Puntaje Ponderado</th>
                                                <th class="text-center fw-semibold" style="width:15%"><i class="bi bi-info-circle me-1"></i>Detalles</th>
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
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    title="{{ htmlspecialchars($itemResumen['observacion_general']) }}"
                                                                    style="transition: all 0.3s ease;">
                                                                    <i class="bi bi-chat-left-text me-1"></i>Ver Obs.
                                                                </button>
                                                            @else
                                                                <small class="text-muted"><em>Sin obs.</em></small>
                                                            @endif
                                                        @elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR')
                                                            @if (!empty($todasLasCalificacionesDelTribunal))
                                                                <button type="button"
                                                                    class="btn btn-sm text-white"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#detalleRubricaModal_{{ $itemPlanId }}"
                                                                    style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); border: none; transition: all 0.3s ease;"
                                                                    onmouseover="this.style.transform='scale(1.05)'"
                                                                    onmouseout="this.style.transform='scale(1)'">
                                                                    <i class="bi bi-search me-1"></i>Ver Detalle
                                                                </button>
                                                            @else
                                                                <small class="text-muted"><em>Detalle no disponible.</em></small>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot style="background: linear-gradient(135deg, #f0f8f5 0%, #e7f6f2 100%);">
                                            <tr style="border-top: 3px solid #3d8e72ff;">
                                                <td colspan="3" class="text-end fw-bold fs-5 align-middle py-3" style="color: #2d7a5f;">
                                                    <i class="bi bi-trophy-fill me-2"></i>NOTA FINAL DEL TRIBUNAL (sobre 20):
                                                </td>
                                                <td class="text-center fw-bold fs-3 py-3 @if ($notaFinalCalculadaDelTribunal >= 14) text-success @elseif(is_numeric($notaFinalCalculadaDelTribunal)) text-danger @else text-muted @endif"
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
                                        @include('livewire.tribunales.profile.modal-detalle-rubrica', [
                                            'itemPlanId' => $itemPlanId,
                                            'detalleItemRubrica' => $detalleRubricasParaModal[$itemPlanId], // Pasar el nuevo dato
                                        ])
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                {{-- SECCIÓN 3: HISTORIAL DE CAMBIOS --}}
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                        <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-clock-history me-2"></i>Historial de Cambios
                        </h5>
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
                                                por <strong>{{ $log->user->name }} {{ $log->user->lastname }}</strong>
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
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-file-earmark-text-fill me-2"></i>Acta del Tribunal
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- <p class="mb-3">El acta oficial del tribunal está disponible para exportación en diferentes formatos.</p> -->

                            <div class="d-flex flex-wrap gap-2">
                                {{-- Botón PDF Hardcodeado (actual) --}}
                                <button class="btn text-white px-4 py-2" wire:click="exportarActa" wire:loading.attr="disabled"
                                        style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(220,53,69,0.4)'"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                        title="Exportar PDF">
                                    <span wire:loading wire:target="exportarActa" class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>
                                    <i class="bi bi-file-pdf-fill me-2" wire:loading.remove wire:target="exportarActa"></i>
                                    PDF 
                                </button>

                                {{-- Botón Word --}}
                                <!-- <button class="btn text-white px-4 py-2" wire:click="exportarActaWord" wire:loading.attr="disabled"
                                        style="background: linear-gradient(135deg, #2b5797 0%, #1e3a5f 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(43,87,151,0.4)'"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                        title="Exportar Word usando plantilla personalizada">
                                    <span wire:loading wire:target="exportarActaWord" class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>
                                    <i class="bi bi-file-word-fill me-2" wire:loading.remove wire:target="exportarActaWord"></i>
                                    Word (Plantilla)
                                </button> -->

                                {{-- Botón PDF desde Word --}}
                                <!-- <button class="btn text-white px-4 py-2" wire:click="exportarActaPdfDesdeWord" wire:loading.attr="disabled"
                                        style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.4)'"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                        title="Exportar PDF convertido desde plantilla Word">
                                    <span wire:loading wire:target="exportarActaPdfDesdeWord" class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>
                                    <i class="bi bi-file-earmark-pdf-fill me-2" wire:loading.remove wire:target="exportarActaPdfDesdeWord"></i>
                                    PDF (desde Word)
                                </button> -->
                            </div>

                            <!-- <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>PDF (Hardcodeado):</strong> Usa plantilla fija del sistema.
                                    <strong>Word:</strong> Usa plantilla personalizada (.docx).
                                    <strong>PDF (desde Word):</strong> Convierte plantilla Word a PDF.
                                </small>
                            </div> -->

                            {{-- Sección de acta firmada --}}
                            @if($tribunal->acta_firmada_path)
                                <hr class="my-4">
                                <div class="alert alert-success d-flex align-items-start" role="alert">
                                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <strong>Acta Firmada Disponible</strong><br>
                                        <small class="text-muted">
                                            Subida el {{ \Carbon\Carbon::parse($tribunal->acta_firmada_fecha)->format('d/m/Y H:i') }}
                                            por {{ $tribunal->usuarioSubioActa->name ?? 'Usuario desconocido' }}
                                        </small>
                                    </div>
                                </div>
                                @can('descargar-acta-firmada-de-este-tribunal', $tribunal)
                                    <button class="btn text-white px-4 py-2" wire:click="descargarActaFirmada" wire:loading.attr="disabled"
                                            style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.4)'"
                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                            title="Descargar Acta Firmada">
                                        <span wire:loading wire:target="descargarActaFirmada" class="spinner-border spinner-border-sm me-2"
                                            role="status" aria-hidden="true"></span>
                                        <i class="bi bi-download me-2" wire:loading.remove wire:target="descargarActaFirmada"></i>
                                        Descargar Acta Firmada
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                @elseif($tribunal && $tribunal->estado !== 'CERRADO')
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-file-earmark-text-fill me-2"></i>Acta del Tribunal
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Acta no disponible</strong><br>
                                    El acta oficial del tribunal estará disponible para exportación una vez que el tribunal sea cerrado oficialmente.
                                    <br><small class="text-muted">Estado actual: <span class="badge bg-warning">{{ $tribunal->estado }}</span></small>
                                </div>
                            </div>
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

                // Manejar descarga de archivos PDF
                window.addEventListener('downloadFile', event => {
                    const filename = event.detail.path;
                    const downloadUrl = "{{ route('download.temp.pdf', ':filename') }}".replace(':filename', filename);

                    // Crear un enlace temporal para descargar
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            </script>
        @endpush

    </div>
