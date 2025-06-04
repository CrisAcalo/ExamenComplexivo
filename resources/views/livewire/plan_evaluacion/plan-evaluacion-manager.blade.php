{{-- resources/views/livewire/plan-evaluacion-manager.blade.php --}}
<div>
    @section('title', 'Gestionar Plan de Evaluación') {{-- Para el título de la página si tu layout lo usa --}}

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
            <div class="alert alert-danger">{{ $errors->first('ponderacion_total_global') }}</div>
        @endif

        <form wire:submit.prevent="savePlan">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Datos del Plan de Evaluación</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nombrePlan" class="form-label">Nombre del Plan</label>
                        <input type="text" class="form-control @error('nombrePlan') is-invalid @enderror"
                            id="nombrePlan" wire:model.defer="nombrePlan">
                        @error('nombrePlan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="descripcionPlan" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control @error('descripcionPlan') is-invalid @enderror" id="descripcionPlan"
                            wire:model.defer="descripcionPlan" rows="3"></textarea>
                        @error('descripcionPlan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Ítems del Plan de Evaluación</h5>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="addItem">
                        <i class="bi bi-plus-lg"></i> Añadir Ítem
                    </button>
                </div>
                <div class="card-body">
                    @if (empty($items))
                        <p class="text-muted text-center">No hay ítems definidos en este plan. Haga clic en "Añadir
                            Ítem".</p>
                    @endif

                    @foreach ($items as $index => $item)
                        <div class="border p-3 mb-3 rounded" wire:key="{{ $item['id_temporal'] }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6>Ítem {{ $index + 1 }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="removeItem({{ $index }})" title="Eliminar Ítem">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="item_nombre_{{ $index }}" class="form-label">Nombre del
                                        Ítem</label>
                                    <input type="text"
                                        class="form-control @error('items.' . $index . '.nombre_item') is-invalid @enderror"
                                        id="item_nombre_{{ $index }}"
                                        wire:model.defer="items.{{ $index }}.nombre_item"
                                        placeholder="Ej: Cuestionario">
                                    @error('items.' . $index . '.nombre_item')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="item_tipo_{{ $index }}" class="form-label">Tipo de Ítem</label>
                                    <select
                                        class="form-select @error('items.' . $index . '.tipo_item') is-invalid @enderror"
                                        id="item_tipo_{{ $index }}"
                                        wire:model="items.{{ $index }}.tipo_item">
                                        @foreach ($tiposItemDisponibles as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.tipo_item')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="item_ponderacion_{{ $index }}" class="form-label">Ponderación
                                        Global (%)</label>
                                    <input type="number" step="0.01"
                                        class="form-control @error('items.' . $index . '.ponderacion_global') is-invalid @enderror"
                                        id="item_ponderacion_{{ $index }}"
                                        wire:model.defer="items.{{ $index }}.ponderacion_global"
                                        placeholder="Ej: 50">
                                    @error('items.' . $index . '.ponderacion_global')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                @if ($item['tipo_item'] === 'RUBRICA_TABULAR')
                                    <div class="col-md-3 mb-3"> {{-- Columna para la plantilla --}}
                                        <label for="item_rubrica_{{ $index }}" class="form-label">Plantilla de
                                            Rúbrica</label>
                                        <select
                                            class="form-select @error('items.' . $index . '.rubrica_plantilla_id') is-invalid @enderror"
                                            id="item_rubrica_{{ $index }}"
                                            wire:model="items.{{ $index }}.rubrica_plantilla_id">
                                            {{-- Quitar .defer para que actualice al cambiar --}}
                                            <option value="">Seleccione una plantilla...</option>
                                            @foreach ($plantillasRubricasDisponibles as $plantilla)
                                                <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('items.' . $index . '.rubrica_plantilla_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Sección para mostrar la distribución de ponderación --}}
                                    @if (!empty($item['componentes_rubrica_seleccionada']) && $item['rubrica_plantilla_id'])
                                        <div class="col-12 mb-3">
                                            <div class="p-2 border rounded bg-light">
                                                <p class="mb-1 small fw-bold">Distribución de la ponderación global
                                                    ({{ $item['ponderacion_global'] }}%) para la rúbrica seleccionada:
                                                </p>
                                                <ul class="list-unstyled mb-0 small">
                                                    @php $sumaCalculada = 0; @endphp
                                                    @foreach ($item['componentes_rubrica_seleccionada'] as $compDetalle)
                                                        <li>
                                                            {{ $compDetalle['nombre'] }} (pond. interna:
                                                            {{ $compDetalle['ponderacion_interna'] }}%):
                                                            <strong
                                                                class="text-primary">{{ $compDetalle['ponderacion_calculada_global'] }}%</strong>
                                                            (del total del examen)
                                                            @php $sumaCalculada += $compDetalle['ponderacion_calculada_global']; @endphp
                                                        </li>
                                                    @endforeach
                                                    @if (count($item['componentes_rubrica_seleccionada']) > 0 &&
                                                            abs($sumaCalculada - (float) $item['ponderacion_global']) > 0.01 &&
                                                            (float) $item['ponderacion_global'] > 0)
                                                        {{-- Esta advertencia puede aparecer por redondeos si hay muchos componentes. --}}
                                                        <li class="text-danger small mt-1"><em>Nota: La suma de las
                                                                ponderaciones calculadas
                                                                ({{ round($sumaCalculada, 2) }}%) no coincide
                                                                exactamente con la ponderación global del ítem debido a
                                                                redondeos.</em></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 mb-5">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i> Guardar Plan de Evaluación
                </button>
                <a href="{{ route('periodos.tribunales.index', $carreraPeriodoId) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar y Volver a Tribunales
                </a>
            </div>
        </form>
    </div>
</div>
