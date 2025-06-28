{{-- resources/views/livewire/tribunales/principal/calificar.blade.php --}}
<div>
    @section('title', $tribunal && $estudianteNombreCompleto ? 'Calificar Tribunal: ' . $estudianteNombreCompleto :
        'Calificar Tribunal')

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

            @if ($tribunal && $planEvaluacionActivo && $tieneAlgoQueCalificar)
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
                                                // $nivelesEncabezado =
                                                //     $calificaciones[$itemPlanId]['componentes_evaluados'][
                                                //         $rubricaParaCalificar->componentesRubrica->first()->id
                                                //     ]['criterios_evaluados'][
                                                //         $rubricaParaCalificar->componentesRubrica
                                                //             ->first()
                                                //             ->criteriosComponente->first()->id
                                                //     ]['opciones_calificacion'] ?? collect();



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
                                                // $opcionesDelPrimerCriterio ahora es una colección de objetos (CalificacionCriterio o stdClass)
                                                // Si ya tiene la estructura correcta (nombre, valor) para los encabezados, la usamos.
                                                // Si $opcionesDelPrimerCriterio contiene los modelos CalificacionCriterio completos, está bien.
                                                $nivelesEncabezado = $opcionesDelPrimerCriterio; // Asumimos que esto ya está ordenado y es único si es necesario.
                                                // La lógica anterior de unique y map la podemos simplificar si
                                                // $opciones_calificacion ya viene bien desde el backend.
                                                // Si necesitas procesarla más (ej. solo nombre y valor):
                                                // $nivelesEncabezado = $opcionesDelPrimerCriterio->map(fn($op) => (object)['nombre' => $op->nombre, 'valor' => $op->valor])
                                                //                                             ->unique(fn($item) => $item->nombre . '-' . $item->valor)
                                                //                                             ->sortByDesc('valor') // Asegurar orden
                                                //                                             ->values();
                                                // Si $nivelesEncabezado sigue siendo una colección de modelos CalificacionCriterio, está bien.
                                                // Si necesitas solo nombre y valor, puedes mapearlo como antes.
                                                // $nivelesEncabezado = $nivelesEncabezado->map(fn($nc) => (object)['nombre' => $nc->nombre, 'valor' => $nc->valor])->unique(fn($item) => $item->nombre . '-' . $item->valor)->values();
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
                                                                                {{-- $nivel es un objeto CalificacionCriterio --}}
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
                                                                    {{-- En calificar.blade.php, dentro del bucle de criteriosComponente --}}
                                                                    @foreach ($componenteR->criteriosComponente as $criterioR)
                                                                        @php
                                                                            $pathBaseCalif = "calificaciones.{$itemPlanId}.componentes_evaluados.{$componenteR->id}.criterios_evaluados.{$criterioR->id}";

                                                                            // Obtener los datos de las opciones
                                                                            $opcionesData =
                                                                                $calificaciones[$itemPlanId][
                                                                                    'componentes_evaluados'
                                                                                ][$componenteR->id][
                                                                                    'criterios_evaluados'
                                                                                ][$criterioR->id][
                                                                                    'opciones_calificacion'
                                                                                ] ?? null;

                                                                            // Asegurar que sea una colección para poder usar firstWhere
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
                                                                                    {{-- $nivelColumna es un objeto CalificacionCriterio o stdClass con 'valor' --}}
                                                                                    @php
                                                                                        $opcionCalifParaColumnaCruda = $opcionesParaEsteCriterio->firstWhere(
                                                                                            'valor',
                                                                                            (string) $nivelColumna->valor,
                                                                                        ); // Comparar como string por si acaso

                                                                                        // Paso de decodificación/aseguramiento de objeto:
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
                                                                                                // json_decode puede devolver null si falla, o un objeto/array
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
                                                                                        class="text-center celda-calificacion">
                                                                                        {{-- DEBUG: Descomenta para ver el tipo y contenido exacto de $opcionCalifParaColumna antes del if --}}
                                                                                        {{-- <pre style="font-size: 0.7rem; text-align: left;">Tipo: {{ gettype($opcionCalifParaColumna) }} || Contenido: {{ print_r($opcionCalifParaColumna, true) }}</pre> --}}
                                                                                        {{-- {{$opcionCalifParaColumna}} --}}
                                                                                        @if ($opcionCalifParaColumna && is_object($opcionCalifParaColumna) && isset($opcionCalifParaColumna->id))
                                                                                            {{-- <p syle="color:red">{{$opcionCalifParaColumna}}</p> --}}
                                                                                            <div
                                                                                                class="form-check d-flex flex-column align-items-center">
                                                                                                <input
                                                                                                    class="form-check-input mb-1"
                                                                                                    type="radio"
                                                                                                    wire:model.defer="{{ $pathBaseCalif }}.calificacion_criterio_id"
                                                                                                    name="calif_radio_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}"
                                                                                                    id="calif_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalifParaColumna->id }}"
                                                                                                    value="{{ $opcionCalifParaColumna->id }}">
                                                                                                <label
                                                                                                    class="form-check-label small text-muted"
                                                                                                    for="calif_{{ $itemPlanId }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalifParaColumna->id }}">
                                                                                                    {{ $opcionCalifParaColumna->descripcion }}
                                                                                                </label>
                                                                                            </div>
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
                                                                                    rows="3" placeholder="Observación específica..." {{-- Ajustado rows a 3 --}}
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
