<div>
    @section('title', 'Gestionar Plan de Evaluación')

    <div class="container-fluid p-0">
        {{-- Breadcrumbs --}}
        <div class="fs-2 fw-semibold mb-4">
            <a href="{{ route('periodos.') }}" class="text-decoration-none text-dark">Períodos</a> /
            @if ($periodo)
                <a href="{{ route('periodos.profile', $periodo->id) }}"
                    class="text-decoration-none text-dark">{{ $periodo->codigo_periodo }}</a> /
            @endif
            @if ($carrera)
                <a href="{{ route('periodos.tribunales.index', $carreraPeriodoId) }}"
                    class="text-decoration-none text-dark">{{ $carrera->nombre }}</a> /
            @endif
            <span class="text-muted">Gestionar Plan de Evaluación</span>
        </div>

        @include('partials.alerts')
        @if ($errors->has('ponderacion_total_global'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first('ponderacion_total_global') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form wire:submit.prevent="savePlan">
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5>Datos del Plan de Evaluación</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nombrePlan" class="form-label">Nombre del Plan</label>
                        <input type="text" class="form-control @error('nombrePlan') is-invalid @enderror"
                            id="nombrePlan" wire:model.defer="nombrePlan" placeholder="Ej: Plan Complexivo TI 2024S1">
                        @error('nombrePlan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="descripcionPlan" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control @error('descripcionPlan') is-invalid @enderror" id="descripcionPlan"
                            wire:model.defer="descripcionPlan" rows="3" placeholder="Breve descripción del propósito del plan..."></textarea>
                        @error('descripcionPlan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Ítems del Plan de Evaluación</h5>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="addItem">
                        <i class="bi bi-plus-lg"></i> Añadir Ítem
                    </button>
                </div>
                <div class="card-body">
                    @if (empty($items))
                        <p class="text-muted text-center py-3">
                            <i class="bi bi-info-circle fs-4"></i><br>
                            No hay ítems definidos en este plan. <br>Haga clic en "Añadir Ítem" para comenzar.
                        </p>
                    @endif

                    @foreach ($items as $index => $item)
                        <div class="border p-3 mb-3 rounded shadow-sm bg-light" wire:key="{{ $item['id_temporal'] ?? $index }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-primary mb-0 pt-1">Ítem {{ $index + 1 }}</h6>
                                @if(count($items) > 0) {{-- Mostrar botón solo si hay items, idealmente > 1 pero por si se añade uno y se quiere borrar al instante --}}
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="removeItem({{ $index }})" title="Eliminar Ítem">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row">
                                {{-- Nombre del Ítem --}}
                                <div class="col-md-6 mb-3">
                                    <label for="item_nombre_{{ $index }}" class="form-label">Nombre del Ítem</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('items.'.$index.'.nombre_item') is-invalid @enderror"
                                        id="item_nombre_{{ $index }}"
                                        wire:model.defer="items.{{ $index }}.nombre_item"
                                        placeholder="Ej: Cuestionario Teórico">
                                    @error('items.'.$index.'.nombre_item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                {{-- Tipo de Ítem --}}
                                <div class="col-md-3 mb-3">
                                    <label for="item_tipo_{{ $index }}" class="form-label">Tipo de Ítem</label>
                                    <select
                                        class="form-select form-select-sm @error('items.'.$index.'.tipo_item') is-invalid @enderror"
                                        id="item_tipo_{{ $index }}"
                                        wire:model="items.{{ $index }}.tipo_item">
                                        @foreach ($tiposItemDisponibles as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.tipo_item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                {{-- Ponderación Global --}}
                                <div class="col-md-3 mb-3">
                                    <label for="item_ponderacion_{{ $index }}" class="form-label">Ponderación Global (%)</label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        class="form-control form-control-sm @error('items.'.$index.'.ponderacion_global') is-invalid @enderror"
                                        id="item_ponderacion_{{ $index }}"
                                        wire:model="items.{{ $index }}.ponderacion_global">
                                    @error('items.'.$index.'.ponderacion_global') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Campos específicos según el tipo de ítem --}}
                            @if ($item['tipo_item'] === 'NOTA_DIRECTA')
                                <div class="row">
                                    <div class="col-md-12 mb-2"> {{-- O col-md-6 --}}
                                        <label class="form-label">Calificado por:</label>
                                        <p class="form-control-plaintext bg-white p-2 rounded border mb-0">
                                            <i class="bi bi-person-check-fill text-info"></i> Director de Carrera / Docente de Apoyo
                                        </p>
                                        {{-- No hay error de validación para un campo no seleccionable por el usuario --}}
                                    </div>
                                </div>
                            @elseif ($item['tipo_item'] === 'RUBRICA_TABULAR')
                                <div class="row">
                                    <div class="col-md-12 mb-3"> {{-- O col-md-6 --}}
                                        <label for="item_rubrica_{{ $index }}" class="form-label">Plantilla de Rúbrica</label>
                                        <select class="form-select form-select-sm @error('items.'.$index.'.rubrica_plantilla_id') is-invalid @enderror"
                                            id="item_rubrica_{{ $index }}" wire:model="items.{{ $index }}.rubrica_plantilla_id">
                                            <option value="">Seleccione una plantilla...</option>
                                            @foreach ($plantillasRubricasDisponibles as $plantilla)
                                                <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('items.'.$index.'.rubrica_plantilla_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Sección para mostrar la distribución de ponderación --}}
                                @php
                                    // Asegurar que componentes_rubrica_seleccionada es iterable
                                    $componentesParaDistribucion = is_iterable($item['componentes_rubrica_seleccionada'] ?? null) ? $item['componentes_rubrica_seleccionada'] : [];
                                @endphp
                                @if (!empty($componentesParaDistribucion) && $item['rubrica_plantilla_id'])
                                    <div class="mb-3 p-2 border rounded bg-white">
                                        <p class="mb-1 small fw-bold">Distribución de la Ponderación Global ({{ $item['ponderacion_global'] ?? 0 }}%):</p>
                                        <ul class="list-unstyled mb-0 small">
                                            @php $sumaCalculada = 0; @endphp
                                            @foreach ($componentesParaDistribucion as $compDetalle)
                                                <li>
                                                    {{ $compDetalle->nombre ?? 'N/A' }} (interna: {{ $compDetalle->ponderacion_interna ?? 'N/A' }}%):
                                                    <strong class="text-primary">{{ $compDetalle->ponderacion_calculada_global ?? 'N/A' }}%</strong>
                                                    (del total)
                                                    @php $sumaCalculada += ($compDetalle->ponderacion_calculada_global ?? 0); @endphp
                                                </li>
                                            @endforeach
                                            @if (count($componentesParaDistribucion) > 0 && abs($sumaCalculada - (float)($item['ponderacion_global'] ?? 0)) > 0.01 && (float)($item['ponderacion_global'] ?? 0) > 0)
                                                <li class="text-danger small mt-1"><em>Nota: Suma calculada ({{ round($sumaCalculada, 2) }}%) difiere de la ponderación global por redondeos.</em></li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif

                                {{-- Asignación de quién califica cada componente de la rúbrica --}}
                                @php
                                    $plantillaComponentesParaIterar = $item['plantilla_componentes'] ?? collect();
                                    if (!($plantillaComponentesParaIterar instanceof \Illuminate\Support\Collection)) {
                                        $plantillaComponentesParaIterar = collect($plantillaComponentesParaIterar);
                                    }
                                @endphp
                                @if ($item['rubrica_plantilla_id'] && $plantillaComponentesParaIterar->isNotEmpty())
                                    <div class="mt-3">
                                        <h6 class="mb-2">Asignar Calificadores a Componentes de la Rúbrica:</h6>
                                        @foreach ($plantillaComponentesParaIterar as $compPlantilla)
                                            <div class="row mb-2 align-items-center">
                                                <div class="col-md-6">
                                                    <label for="comp_calif_{{ $index }}_{{ $compPlantilla->id }}" class="form-label small mb-0 ps-2">
                                                        <i class="bi bi-diagram-2"></i> Componente: <strong>{{ $compPlantilla->nombre }}</strong>
                                                        <small class="text-muted"> ({{ $compPlantilla->ponderacion }}% de esta rúbrica)</small>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <select class="form-select form-select-sm @error('items.'.$index.'.asignaciones_componentes.'.$compPlantilla->id) is-invalid @enderror"
                                                        id="comp_calif_{{ $index }}_{{ $compPlantilla->id }}"
                                                        wire:model.defer="items.{{ $index }}.asignaciones_componentes.{{ $compPlantilla->id }}">
                                                        <option value="">Seleccione quién califica...</option>
                                                        @foreach ($opcionesCalificadoPorComponenteRubricaFiltradas as $keyOpt => $valueOpt)
                                                            <option value="{{ $keyOpt }}">{{ $valueOpt }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('items.'.$index.'.asignaciones_componentes.'.$compPlantilla->id) <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($item['rubrica_plantilla_id'])
                                    <p class="text-muted small mt-2">La plantilla de rúbrica seleccionada no tiene componentes definidos o no se han podido cargar.</p>
                                @endif
                            @endif
                        </div> {{-- Fin .border .p-3 (bloque del ítem) --}}
                    @endforeach
                </div>
            </div>

            <div class="mt-4 mb-5">
                <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                    <span wire:loading wire:target="savePlan" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <i class="bi bi-save" wire:loading.remove wire:target="savePlan"></i>
                    Guardar Plan de Evaluación
                </button>
                <a href="{{ route('periodos.tribunales.index', $carreraPeriodoId) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar y Volver a Tribunales
                </a>
            </div>
        </form>
    </div>
</div>
