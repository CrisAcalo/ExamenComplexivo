{{-- resources/views/livewire/tribunales/principal/calificar.blade.php --}}
<div>
    @section('title', $tribunal && $estudianteNombreCompleto ? 'Calificar Tribunal: ' . $estudianteNombreCompleto :
        'Calificar Tribunal')


        @push('styles')
            <style>
                .celda-calificacion label {
                    transition: all 0.2s ease-in-out;
                    border: 2px solid transparent;
                    display: flex !important;
                    flex-direction: column;
                    align-items: center;
                    justify-content: flex-start;
                    width: 100% !important;
                    height: 100% !important;
                    min-height: 100px;
                    padding: 15px 10px;
                    margin: 0 !important;
                    box-sizing: border-box;
                }

                .celda-calificacion label:hover {
                    background-color: #f8f9fa;
                    border-radius: 8px;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .celda-calificacion input[type="radio"]:checked+span {
                    color: #0d6efd !important;
                    font-weight: 600;
                }

                .celda-calificacion input[type="radio"]:checked {
                    transform: scale(1.2);
                    border-color: #0d6efd;
                }

                /* Fallback para navegadores sin soporte de :has() */
                .celda-calificacion label.selected {
                    background-color: #e7f1ff !important;
                    border: 2px solid #0d6efd !important;
                    border-radius: 8px !important;
                }

                /* Para navegadores modernos que soportan :has() */
                @supports selector(:has(*)) {
                    .celda-calificacion label:has(input[type="radio"]:checked) {
                        background-color: #e7f1ff;
                        border: 2px solid #0d6efd;
                        border-radius: 8px;
                    }
                }

                .cursor-pointer {
                    cursor: pointer !important;
                }

                .table-rubrica-calificacion .celda-calificacion {
                    vertical-align: top;
                    padding: 0 !important;
                    position: relative;
                    height: 120px;
                }

                .celda-calificacion .form-check-input {
                    margin-bottom: 8px;
                    position: static;
                    z-index: 2;
                }

                .celda-calificacion .descripcion-texto {
                    text-align: center;
                    text-justify: inter-word;
                    hyphens: auto;
                    word-wrap: break-word;
                    line-height: 1.3;
                    flex-grow: 1;
                    display: flex;
                    align-items: flex-start;
                    justify-content: center;
                    text-align: center;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Agregar event listeners para mejorar la selección visual
                    function updateRadioSelection() {
                        // Remover clase 'selected' de todos los labels de radio buttons
                        document.querySelectorAll('.celda-calificacion label').forEach(label => {
                            label.classList.remove('selected');
                        });

                        // Agregar clase 'selected' a labels que contienen radio buttons seleccionados
                        document.querySelectorAll('.celda-calificacion input[type="radio"]:checked').forEach(radio => {
                            const label = radio.closest('label');
                            if (label) {
                                label.classList.add('selected');
                            }
                        });
                    }

                    // Actualizar al cargar la página
                    updateRadioSelection();

                    // Actualizar cuando cambien los radio buttons
                    document.addEventListener('change', function(e) {
                        if (e.target.type === 'radio' && e.target.closest('.celda-calificacion')) {
                            updateRadioSelection();
                        }
                    });

                    // Mejorar efectos hover - ahora que los labels ocupan toda la celda
                    document.querySelectorAll('.celda-calificacion label').forEach(label => {
                        label.addEventListener('mouseenter', function() {
                            if (!this.classList.contains('selected')) {
                                this.style.transform = 'translateY(-2px)';
                            }
                        });

                        label.addEventListener('mouseleave', function() {
                            if (!this.classList.contains('selected')) {
                                this.style.transform = 'translateY(0)';
                            }
                        });
                    });

                    // Asegurar que las celdas tengan la altura correcta
                    function adjustCellHeights() {
                        const rows = document.querySelectorAll('.table-rubrica-calificacion tbody tr');
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('.celda-calificacion');
                            if (cells.length > 0) {
                                // Encontrar la altura máxima en esta fila
                                let maxHeight = 0;
                                cells.forEach(cell => {
                                    const label = cell.querySelector('label');
                                    if (label) {
                                        const height = label.scrollHeight;
                                        maxHeight = Math.max(maxHeight, height);
                                    }
                                });

                                // Aplicar la altura máxima a todas las celdas de la fila
                                if (maxHeight > 0) {
                                    cells.forEach(cell => {
                                        cell.style.height = Math.max(maxHeight, 120) + 'px';
                                    });
                                }
                            }
                        });
                    }

                    // Ajustar alturas al cargar y cuando la ventana cambie de tamaño
                    adjustCellHeights();
                    window.addEventListener('resize', adjustCellHeights);
                });
            </script>
        @endpush

        <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="fs-2 fw-semibold">
                    <a href="{{ route('tribunales.principal') }}" class="text-decoration-none text-dark">Mis Evaluaciones</a>
                    /
                    @if ($tribunal && $estudianteNombreCompleto)
                        <span class="text-muted">Calificando a: {{ $estudianteNombreCompleto }} ({{ $carreraNombre }} -
                            {{ $periodoCodigo }})</span>
                    @else
                        <span class="text-muted">Calificar Tribunal</span>
                    @endif
                </div>
                @if ($tribunal && $puedeVerBotonActa)
                    {{-- Usar la propiedad del componente --}}
                    <div>
                        <button wire:click="exportarActa" class="btn btn-danger" wire:loading.attr="disabled"
                            wire:target="exportarActa">
                            <span wire:loading wire:target="exportarActa" class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                            <i class="bi bi-file-earmark-pdf-fill" wire:loading.remove wire:target="exportarActa"></i>
                            Generar Acta
                        </button>
                    </div>
                @endif
            </div>

            @include('partials.alerts')

            {{-- Mostrar estado del tribunal --}}
            @if ($tribunal && $tribunal->estado === 'CERRADO')
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="bi bi-lock-fill fs-4 me-3"></i>
                    <div>
                        <strong>Tribunal Cerrado:</strong> Este tribunal está cerrado y no se pueden realizar calificaciones.
                        Solo se permite consultar información y exportar el acta.
                    </div>
                </div>
            @endif

            @if ($tribunal && $planEvaluacionActivo && $tieneAlgoQueCalificar && $tribunal->estado === 'ABIERTO')
                <div class="card shadow-sm">
                    {{-- En resources/views/livewire/tribunales/principal/calificar.blade.php --}}

                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-clipboard2-check-fill text-success"></i> Formulario de
                                    Calificación para: <strong>{{ $estudianteNombreCompleto ?? 'N/D' }}</strong></h5>
                                @if ($planEvaluacionActivo)
                                    <p class="mb-0 small text-muted">Plan de Evaluación: {{ $planEvaluacionActivo->nombre }}
                                    </p>
                                @endif
                            </div>
                            {{-- En el card-header --}}
                            <div class="text-end">
                                @php
                                    $rolMostrado = 'Indefinido'; // Default más genérico
                                    if ($rolUsuarioActualEnTribunal) {
                                        // Prioridad si es miembro físico
                                        $rolMostrado = Str::title(
                                            Str::lower(Str_replace('_', ' ', $rolUsuarioActualEnTribunal)),
                                        );
                                    } elseif ($tribunal && $tribunal->carrerasPeriodo) {
                                        if ($tribunal->carrerasPeriodo->director_id == $usuarioActual?->id) {
                                            $rolMostrado = 'Director de Carrera';
                                        } elseif ($tribunal->carrerasPeriodo->docente_apoyo_id == $usuarioActual?->id) {
                                            $rolMostrado = 'Docente de Apoyo';
                                        } elseif ($esCalificadorGeneral) {
                                            // Usar la propiedad pública del componente
                                            $rolMostrado = 'Calificador General';
                                        }
                                    }
                                @endphp
                                <span class="text-muted small">Su Rol de Evaluación:</span><br>
                                <span
                                    class="badge
                                    @if ($rolUsuarioActualEnTribunal === 'PRESIDENTE' || $rolMostrado === 'Director de Carrera') bg-success
                                    @elseif($rolUsuarioActualEnTribunal === 'INTEGRANTE1' || $rolMostrado === 'Docente de Apoyo') bg-info text-dark
                                    @elseif($rolUsuarioActualEnTribunal === 'INTEGRANTE2') bg-secondary
                                    @elseif($rolMostrado === 'Calificador General') bg-warning text-dark
                                    @else bg-dark @endif">
                                    {{ $rolMostrado }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="guardarCalificaciones">
                            @php $itemRenderedCount = 0; @endphp {{-- Para saber si se renderizó algo calificable --}}

                            @foreach ($planEvaluacionActivo->itemsPlanEvaluacion->sortBy('orden') as $itemPlan)
                                @php
                                    $itemPlanId = $itemPlan->id;
                                    // Solo mostrar el bloque del ítem si el usuario tiene algo que calificar en él
                                    $mostrarBloqueItem = $itemsACalificarPorUsuario[$itemPlanId] ?? false;
                                @endphp

                                @if ($mostrarBloqueItem)
                                    @php $itemRenderedCount++; @endphp
                                    <div class="mb-4 p-3 border rounded item-evaluacion-block shadow-sm bg-light">
                                        <h5>{{ $loop->iteration }}. {{ $itemPlan->nombre_item }}
                                            <span class="badge bg-secondary">{{ $itemPlan->ponderacion_global }}%</span>
                                        </h5>

                                        @if ($itemPlan->tipo_item === 'NOTA_DIRECTA')
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="nota_directa_{{ $itemPlanId }}" class="form-label">Nota
                                                        (sobre 20)
                                                    </label>
                                                    <input type="number" step="0.01" min="0" max="20"
                                                        class="form-control @error('calificaciones.' . $itemPlanId . '.nota_directa') is-invalid @enderror"
                                                        id="nota_directa_{{ $itemPlanId }}"
                                                        wire:model.defer="calificaciones.{{ $itemPlanId }}.nota_directa">
                                                    @error('calificaciones.' . $itemPlanId . '.nota_directa')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-8 mb-3">
                                                    <label for="obs_general_item_{{ $itemPlanId }}"
                                                        class="form-label">Observación General (Opcional)</label>
                                                    <textarea
                                                        class="form-control @error('calificaciones.' . $itemPlanId . '.observacion_general_item') is-invalid @enderror"
                                                        id="obs_general_item_{{ $itemPlanId }}" rows="2"
                                                        wire:model.defer="calificaciones.{{ $itemPlanId }}.observacion_general_item"></textarea>
                                                    @error('calificaciones.' . $itemPlanId . '.observacion_general_item')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        @elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla)
                                            @php
                                                $rubricaParaCalificar = $itemPlan->rubricaPlantilla;

                                                $opcionesDelPrimerCriterio = collect(); // Default a colección vacía
                                                if (
                                                    $rubricaParaCalificar->componentesRubrica->isNotEmpty() &&
                                                    $rubricaParaCalificar->componentesRubrica
                                                        ->first()
                                                        ->criteriosComponente->isNotEmpty()
                                                ) {
                                                    $primerComponenteId = $rubricaParaCalificar->componentesRubrica->first()
                                                        ->id;
                                                    $primerCriterioId = $rubricaParaCalificar->componentesRubrica
                                                        ->first()
                                                        ->criteriosComponente->first()->id;

                                                    // Acceder a la estructura de calificaciones que preparamos en el backend
                                                    $opcionesData =
                                                        $calificaciones[$itemPlanId]['componentes_evaluados'][
                                                            $primerComponenteId
                                                        ]['criterios_evaluados'][$primerCriterioId][
                                                            'opciones_calificacion'
                                                        ] ?? null;

                                                    if ($opcionesData instanceof \Illuminate\Support\Collection) {
                                                        $opcionesDelPrimerCriterio = $opcionesData;
                                                    } elseif (is_array($opcionesData)) {
                                                        // Si es un array, convertirlo a colección de objetos (asumiendo que son arrays asociativos)
                                                        $opcionesDelPrimerCriterio = collect($opcionesData)->map(
                                                            fn($item) => is_array($item) ? (object) $item : $item,
                                                        );
                                                    }
                                                }
                                                $nivelesEncabezado = $opcionesDelPrimerCriterio;

                                            @endphp
                                            <p class="text-muted small">Usando plantilla:
                                                {{ $rubricaParaCalificar->nombre }}</p>

                                            @php $componenteCalificableRenderedCount = 0; @endphp
                                            @foreach ($rubricaParaCalificar->componentesRubrica as $componenteR)
                                                @php
                                                    $puedeCalificarEsteComponente =
                                                        $componentesACalificarPorUsuario[$itemPlanId][
                                                            $componenteR->id
                                                        ] ?? false;
                                                @endphp

                                                @if ($puedeCalificarEsteComponente)
                                                    @php $componenteCalificableRenderedCount++; @endphp
                                                    <div
                                                        class="mb-4 p-3 border-start border-3 {{ $loop->parent->even ? 'border-primary' : 'border-info' }} bg-white shadow-sm">
                                                        <h6 class="text-primary">{{ $componenteR->nombre }} <small
                                                                class="text-muted">({{ $componenteR->ponderacion }}% de
                                                                esta rúbrica)</small></h6>
                                                        <div class="table-responsive">
                                                            <table
                                                                class="table table-bordered table-rubrica-calificacion align-middle">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th class="text-center" style="width: 25%;">Criterio
                                                                        </th>
                                                                        @if ($nivelesEncabezado->isNotEmpty())
                                                                            @foreach ($nivelesEncabezado as $nivel)
                                                                                <th class="text-center">
                                                                                    {{ $nivel->nombre }} <br>
                                                                                    ({{ $nivel->valor }})
                                                                                </th>
                                                                            @endforeach
                                                                        @else
                                                                            <th class="text-center">Niveles de Calificación
                                                                            </th>
                                                                        @endif
                                                                        <th class="text-center" style="width: 20%;">
                                                                            Observación (Opcional)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($componenteR->criteriosComponente as $criterioR)
                                                                        @php
                                                                            $pathBaseCalif = "calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteR->id}.criterios_evaluados.{$criterioR->id}";
                                                                            $opcionesData =
                                                                                $calificaciones[$itemPlanId][
                                                                                    'componentes_evaluados'
                                                                                ][$componenteR->id][
                                                                                    'criterios_evaluados'
                                                                                ][$criterioR->id][
                                                                                    'opciones_calificacion'
                                                                                ] ?? null;

                                                                            if (
                                                                                $opcionesData instanceof
                                                                                \Illuminate\Support\Collection
                                                                            ) {
                                                                                $opcionesParaEsteCriterio = $opcionesData;
                                                                            } elseif (is_array($opcionesData)) {
                                                                                $opcionesParaEsteCriterio = collect(
                                                                                    $opcionesData,
                                                                                )->map(
                                                                                    fn($item) => is_array($item)
                                                                                        ? (object) $item
                                                                                        : $item,
                                                                                );
                                                                            } else {
                                                                                $opcionesParaEsteCriterio = collect();
                                                                            }
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="criterio-nombre">
                                                                                {{ $criterioR->nombre }}
                                                                                @error($pathBaseCalif .
                                                                                    '.calificacion_criterio_id')
                                                                                    <br><span
                                                                                        class="text-danger d-block small mt-1">{{ $message }}</span>
                                                                                @enderror
                                                                            </td>

                                                                            @if ($nivelesEncabezado->isNotEmpty())
                                                                                @foreach ($nivelesEncabezado as $nivelColumna)
                                                                                    @php
                                                                                        $opcionCalifParaColumnaCruda = $opcionesParaEsteCriterio->firstWhere(
                                                                                            'valor',
                                                                                            (string) $nivelColumna->valor,
                                                                                        );

                                                                                        $opcionCalifParaColumna = null;
                                                                                        if (
                                                                                            $opcionCalifParaColumnaCruda
                                                                                        ) {
                                                                                            if (
                                                                                                is_string(
                                                                                                    $opcionCalifParaColumnaCruda,
                                                                                                )
                                                                                            ) {
                                                                                                $decoded = json_decode(
                                                                                                    $opcionCalifParaColumnaCruda,
                                                                                                );
                                                                                                $opcionCalifParaColumna = is_object(
                                                                                                    $decoded,
                                                                                                )
                                                                                                    ? $decoded
                                                                                                    : (is_array(
                                                                                                        $decoded,
                                                                                                    )
                                                                                                        ? (object) $decoded
                                                                                                        : null);
                                                                                            } elseif (
                                                                                                is_array(
                                                                                                    $opcionCalifParaColumnaCruda,
                                                                                                )
                                                                                            ) {
                                                                                                $opcionCalifParaColumna = (object) $opcionCalifParaColumnaCruda;
                                                                                            } elseif (
                                                                                                is_object(
                                                                                                    $opcionCalifParaColumnaCruda,
                                                                                                )
                                                                                            ) {
                                                                                                $opcionCalifParaColumna = $opcionCalifParaColumnaCruda;
                                                                                            }
                                                                                        }
                                                                                    @endphp
                                                                                    <td
                                                                                        class="text-center celda-calificacion p-0">

                                                                                        @if ($opcionCalifParaColumna && is_object($opcionCalifParaColumna) && isset($opcionCalifParaColumna->id))
                                                                                            <label
                                                                                                class="cursor-pointer position-relative"
                                                                                                for="calif_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalifParaColumna->id }}">
                                                                                                <input
                                                                                                    class="form-check-input"
                                                                                                    type="radio"
                                                                                                    wire:model.defer="{{ $pathBaseCalif }}.calificacion_criterio_id"
                                                                                                    name="calif_radio_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}"
                                                                                                    id="calif_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalifParaColumna->id }}"
                                                                                                    value="{{ $opcionCalifParaColumna->id }}">
                                                                                                <span
                                                                                                    class="small text-muted descripcion-texto">
                                                                                                    {{ $opcionCalifParaColumna->descripcion }}
                                                                                                </span>
                                                                                            </label>
                                                                                        @else
                                                                                            <span
                                                                                                class="text-muted small">-</span>
                                                                                        @endif
                                                                                    </td>
                                                                                @endforeach
                                                                            @else
                                                                                <td class="text-center text-muted small"
                                                                                    colspan="1"><em>(Niveles N/A)</em>
                                                                                </td>
                                                                            @endif

                                                                            <td>
                                                                                <textarea class="form-control form-control-sm @error($pathBaseCalif . '.observacion_criterio') is-invalid @enderror"
                                                                                    rows="3" placeholder="Observación específica..."
                                                                                    wire:model.defer="{{ $pathBaseCalif }}.observacion_criterio"></textarea>
                                                                                @error($pathBaseCalif .
                                                                                    '.observacion_criterio')
                                                                                    <span
                                                                                        class="invalid-feedback">{{ $message }}</span>
                                                                                @enderror
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if ($componenteCalificableRenderedCount == 0 && $itemPlan->rubricaPlantilla->componentesRubrica->isNotEmpty())
                                                <p class="text-muted small"><em>Usted no tiene asignado ningún componente
                                                        para calificar dentro de esta rúbrica.</em></p>
                                            @endif

                                            <div class="mt-3">
                                                <label for="obs_general_item_rubrica_{{ $itemPlanId }}"
                                                    class="form-label">Observación General para
                                                    {{ $itemPlan->nombre_item }} (Opcional)</label>
                                                <textarea
                                                    class="form-control @error('calificaciones.' . $itemPlanId . '.observacion_general_item') is-invalid @enderror"
                                                    id="obs_general_item_rubrica_{{ $itemPlanId }}" rows="2"
                                                    wire:model.defer="calificaciones.'.$itemPlanId.'.observacion_general_item'"></textarea>
                                                @error('calificaciones.' . $itemPlanId . '.observacion_general_item')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @else
                                            <p class="text-warning">No se encontró la plantilla de rúbrica asociada o el
                                                tipo es incorrecto para este ítem.</p>
                                        @endif
                                    </div> {{-- Fin .item-evaluacion-block --}}
                                @endif {{-- Fin @if ($mostrarBloqueItem) --}}
                            @endforeach

                            @if ($itemRenderedCount > 0)
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success px-4" wire:loading.attr="disabled">
                                        <span wire:loading wire:target="guardarCalificaciones"
                                            class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        <i class="bi bi-check-circle-fill" wire:loading.remove
                                            wire:target="guardarCalificaciones"></i>
                                        Guardar Mis Calificaciones
                                    </button>
                                    <a href="{{ route('tribunales.principal') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Volver a Mis Evaluaciones
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-info text-center shadow-sm">
                                    <i class="bi bi-info-circle-fill fs-4 d-block mb-2"></i>
                                    No tiene ítems o componentes asignados para calificar en este tribunal según el plan de
                                    evaluación actual.
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('tribunales.principal') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left-circle"></i> Volver a Mis Evaluaciones
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @else
                @if (session()->has('danger') || session()->has('warning'))
                    {{-- La alerta ya se muestra con @include('partials.alerts') --}}
                @else
                    <div class="alert alert-warning text-center shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>
                        No se pueden cargar los datos para la calificación. <br>Verifique que esté asignado a este tribunal,
                        que exista un plan de evaluación activo y que tenga ítems asignados para calificar.
                    </div>
                @endif
                <div class="text-center mt-3">
                    <a href="{{ route('tribunales.principal') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left-circle"></i> Volver a Mis Evaluaciones
                    </a>
                </div>
            @endif
        </div>
    </div>
