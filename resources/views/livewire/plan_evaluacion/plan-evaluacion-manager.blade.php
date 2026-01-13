@section('title', 'Gestionar Plan de Evaluación')
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
                            <i class="bi bi-file-earmark-text fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            PLAN DE EVALUACIÓN
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de planes y criterios de evaluación
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('periodos.') }}" class="text-decoration-none">
                    <i class="bi bi-calendar-event me-1"></i>Períodos
                </a>
            </li>
            @if ($periodo)
                <li class="breadcrumb-item">
                    <a href="{{ route('periodos.profile', $periodo->id) }}" class="text-decoration-none" >
                        {{ $periodo->codigo_periodo }}
                    </a>
                </li>
            @endif
            @if ($carrera)
                <li class="breadcrumb-item">
                    <a href="{{ route('periodos.tribunales.index', $carreraPeriodoId) }}" class="text-decoration-none">
                        {{ $carrera->nombre }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">Plan de Evaluación</li>
        </ol>
    </nav>

    @include('partials.alerts')
    @if ($errors->has('ponderacion_total_global'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('ponderacion_total_global') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form wire:submit.prevent="savePlan">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                        <i class="bi bi-clipboard-data me-2"></i>Datos del Plan de Evaluación
                    </h5>
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

            <div class="card shadow-sm border-0">
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-list-check me-2"></i>Ítems del Plan de Evaluación
                        </h5>
                        <button type="button" class="btn text-white" wire:click="addItem"
                                style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); border: none; transition: all 0.3s ease;"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="bi bi-plus-circle me-2"></i>Añadir Ítem
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (empty($items))
                        <p class="text-muted text-center py-3">
                            <i class="bi bi-info-circle fs-4"></i><br>
                            No hay ítems definidos en este plan. <br>Haga clic en "Añadir Ítem" para comenzar.
                        </p>
                    @endif

                    @foreach ($items as $index => $item)
                        <div class="border p-4 mb-3 rounded shadow-sm" style="background-color: #f8f9fa; border-left: 4px solid #3d8e72ff !important;" wire:key="{{ $item['id_temporal'] ?? $index }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0 pt-1 fw-bold" style="color: #2d7a5f;">
                                    <i class="bi bi-file-text me-2"></i>Ítem {{ $index + 1 }}
                                </h6>
                                @if(count($items) > 0)
                                <button type="button" class="btn btn-sm btn-danger"
                                    wire:click="removeItem({{ $index }})" title="Eliminar Ítem"
                                    style="transition: all 0.3s ease;"
                                    onmouseover="this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.transform='scale(1)'">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row">
                                {{-- Nombre del Ítem --}}
                                <div class="col-md-6 mb-3">
                                    <label for="item_nombre_{{ $index }}" class="form-label fw-semibold">
                                        <i class="bi bi-pencil me-1"></i>Nombre del Ítem
                                    </label>
                                    <input type="text"
                                        class="form-control @error('items.'.$index.'.nombre_item') is-invalid @enderror"
                                        id="item_nombre_{{ $index }}"
                                        wire:model.defer="items.{{ $index }}.nombre_item"
                                        placeholder="Ej: Cuestionario Teórico">
                                    @error('items.'.$index.'.nombre_item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                {{-- Tipo de Ítem --}}
                                <div class="col-md-3 mb-3">
                                    <label for="item_tipo_{{ $index }}" class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1"></i>Tipo de Ítem
                                    </label>
                                    <select
                                        class="form-select @error('items.'.$index.'.tipo_item') is-invalid @enderror"
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
                                    <label for="item_ponderacion_{{ $index }}" class="form-label fw-semibold">
                                        <i class="bi bi-percent me-1"></i>Ponderación Global (%)
                                    </label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        class="form-control @error('items.'.$index.'.ponderacion_global') is-invalid @enderror"
                                        id="item_ponderacion_{{ $index }}"
                                        wire:model="items.{{ $index }}.ponderacion_global">
                                    @error('items.'.$index.'.ponderacion_global') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Campos específicos según el tipo de ítem --}}
                            @if ($item['tipo_item'] === 'NOTA_DIRECTA')
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i>Calificado por:
                                        </label>
                                        <div class="alert alert-info mb-0" style="background-color: #e7f6f2; border-color: #3d8e72ff;">
                                            <i class="bi bi-person-check-fill me-2" style="color: #3d8e72ff;"></i>
                                            <span style="color: #2d7a5f;">Director de Carrera / Docente de Apoyo</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($item['tipo_item'] === 'RUBRICA_TABULAR')
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="item_rubrica_{{ $index }}" class="form-label fw-semibold">
                                            <i class="bi bi-grid-3x3 me-1"></i>Plantilla de Rúbrica
                                        </label>
                                        <select class="form-select @error('items.'.$index.'.rubrica_plantilla_id') is-invalid @enderror"
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
                                    <div class="mb-3 p-3 border rounded" style="background-color: #f0f8f5; border-color: #3d8e72ff;">
                                        <p class="mb-2 fw-bold" style="color: #2d7a5f;">
                                            <i class="bi bi-pie-chart me-2"></i>Distribución de la Ponderación Global ({{ $item['ponderacion_global'] ?? 0 }}%):
                                        </p>
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
                                    <div class="mt-3 p-3 rounded" style="background-color: #ffffff; border: 1px solid #dee2e6;">
                                        <h6 class="mb-3 fw-bold" style="color: #2d7a5f;">
                                            <i class="bi bi-people-fill me-2"></i>Asignar Calificadores a Componentes de la Rúbrica:
                                        </h6>
                                        @foreach ($plantillaComponentesParaIterar as $compPlantilla)
                                            <div class="row mb-3 align-items-center p-2 rounded" style="background-color: #f8f9fa;">
                                                <div class="col-md-6">
                                                    <label for="comp_calif_{{ $index }}_{{ $compPlantilla->id }}" class="form-label mb-0 fw-semibold">
                                                        <i class="bi bi-diagram-2 me-1" style="color: #3d8e72ff;"></i>
                                                        <span>{{ $compPlantilla->nombre }}</span>
                                                        <small class="text-muted d-block mt-1">{{ $compPlantilla->ponderacion }}% de esta rúbrica</small>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <select class="form-select @error('items.'.$index.'.asignaciones_componentes.'.$compPlantilla->id) is-invalid @enderror"
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
                <button type="submit" class="btn text-white px-4 py-2" wire:loading.attr="disabled"
                        style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(61,142,114,0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span wire:loading wire:target="savePlan" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <i class="bi bi-save me-2" wire:loading.remove wire:target="savePlan"></i>
                    Guardar Plan de Evaluación
                </button>
                <a href="{{ route('periodos.tribunales.index', $carreraPeriodoId) }}" class="btn btn-outline-secondary px-4 py-2"
                   style="transition: all 0.3s ease; font-weight: 600;"
                   onmouseover="this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-x-circle me-2"></i>Cancelar y Volver
                </a>
            </div>
        </form>
    </div>
</div>
