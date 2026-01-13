<div >
    <!-- Banner Verde ESPE -->
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                        <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                             style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                    @else
                        <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                            <i class="bi bi-clipboard-check fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            {{ $modoEdicion ? 'EDITAR RÚBRICA' : 'CREAR NUEVA RÚBRICA' }}
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            {{ $modoEdicion ? 'Modificación de rúbrica existente: ' . $nombreRubrica : 'Configuración de criterios y niveles de evaluación' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('rubricas.') }}" class="text-decoration-none">Rúbricas</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $modoEdicion ? 'Editar' : 'Nueva' }}</li>
        </ol>
    </nav>

    @include('partials.alerts')
    @if ($errors->has('ponderacion_total'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first('ponderacion_total') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit.prevent="saveRubrica">
        <!-- Fila con dos columnas: Información General y Niveles de Calificación -->
        <div class="row mb-3">
            <!-- Columna Izquierda: Información General -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
                        <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-info-circle me-2"></i>Información General
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <label for="nombreRubrica" class="form-label fw-semibold small">Nombre de la Rúbrica</label>
                        <input type="text" class="form-control form-control-sm @error('nombreRubrica') is-invalid @enderror"
                            id="nombreRubrica" wire:model.lazy="nombreRubrica"
                            placeholder="Ej: Rúbrica Evaluación Oral TI 2024S1">
                        @error('nombreRubrica')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Niveles de Calificación -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
                        <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-sliders me-2"></i>Niveles de Calificación
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">Nombre del Nivel</th>
                                        <th style="width: 25%;">Valor</th>
                                        <th style="width: 10%;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nivelesCalificacion as $indexNivel => $nivel)
                                        <tr wire:key="nivel-{{ $nivel['id_temporal'] }}">
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('nivelesCalificacion.' . $indexNivel . '.nombre') is-invalid @enderror"
                                                    wire:model.lazy="nivelesCalificacion.{{ $indexNivel }}.nombre"
                                                    placeholder="Ej: Muy Bueno">
                                                @error('nivelesCalificacion.' . $indexNivel . '.nombre')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control form-control-sm @error('nivelesCalificacion.' . $indexNivel . '.valor') is-invalid @enderror"
                                                    wire:model.lazy="nivelesCalificacion.{{ $indexNivel }}.valor"
                                                    placeholder="Ej: 4">
                                                @error('nivelesCalificacion.' . $indexNivel . '.valor')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td class="text-center">
                                                @if (count($nivelesCalificacion) > 1)
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="removeNivelCalificacion({{ $indexNivel }})"
                                                        title="Eliminar nivel">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success"
                            wire:click="addNivelCalificacion()">
                            <i class="bi bi-plus-lg"></i> Añadir Nivel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Componentes y Criterios -->
        <div class="mb-3">
            <h5 class="fw-bold mb-3" style="color: #2d7a5f;">
                <i class="bi bi-grid-3x3 me-2"></i>Componentes y Criterios
            </h5>
        </div>

        @foreach ($componentes as $indexComponente => $componente)
            <div class="card shadow-sm border-0 mb-3" wire:key="componente-{{ $componente['id_temporal'] }}">
                <div class="card-header py-2" style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <span class="badge bg-secondary me-2">{{ $indexComponente + 1 }}</span>
                            Componente
                        </h6>
                        @if (count($componentes) > 1)
                            <button type="button" class="btn btn-sm btn-danger"
                                wire:click="removeComponente({{ $indexComponente }})">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Nombre y Ponderación del Componente -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nombre del Componente</label>
                            <input type="text"
                                class="form-control form-control-sm @error('componentes.' . $indexComponente . '.nombre') is-invalid @enderror"
                                wire:model.lazy="componentes.{{ $indexComponente }}.nombre"
                                placeholder="Ej: Parte Escrita">
                            @error('componentes.' . $indexComponente . '.nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ponderación (%)</label>
                            <input type="number" step="0.01"
                                class="form-control form-control-sm @error('componentes.' . $indexComponente . '.ponderacion') is-invalid @enderror"
                                wire:model.lazy="componentes.{{ $indexComponente }}.ponderacion"
                                placeholder="Ej: 60">
                            @error('componentes.' . $indexComponente . '.ponderacion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Tabla de Criterios Compacta -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-2" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 180px; vertical-align: middle;">Criterio</th>
                                    @foreach ($nivelesCalificacion as $nivelIndex => $nivel)
                                        <th wire:key="header-comp-{{ $componente['id_temporal'] }}-nivel-{{ $nivel['id_temporal'] }}"
                                            class="text-center" style="min-width: 150px; vertical-align: middle;">
                                            <small class="fw-bold">{{ $nivel['nombre'] }}</small><br>
                                            <span class="badge bg-primary">{{ $nivel['valor'] }}</span>
                                        </th>
                                    @endforeach
                                    <th style="width: 60px; vertical-align: middle;" class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($componente['criterios'] as $indexCriterio => $criterio)
                                    <tr wire:key="criterio-{{ $criterio['id_temporal'] }}">
                                        <td style="vertical-align: top;">
                                            <textarea
                                                class="form-control form-control-sm @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.nombre') is-invalid @enderror"
                                                wire:model.lazy="componentes.{{ $indexComponente }}.criterios.{{ $indexCriterio }}.nombre"
                                                rows="2"
                                                placeholder="Descripción del criterio"
                                                style="font-size: 0.85rem; min-height:auto;"></textarea>
                                            @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.nombre')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        @foreach ($nivelesCalificacion as $indexNivelCol => $nivel)
                                            <td wire:key="desc-{{ $criterio['id_temporal'] }}-{{ $nivel['id_temporal'] }}"
                                                style="vertical-align: top;">
                                                <textarea
                                                    class="form-control form-control-sm @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.descripciones_calificacion.' . $nivel['id_temporal']) is-invalid @enderror"
                                                    wire:model.lazy="componentes.{{ $indexComponente }}.criterios.{{ $indexCriterio }}.descripciones_calificacion.{{ $nivel['id_temporal'] }}"
                                                    rows="2"
                                                    placeholder="Descripción..."
                                                    style="font-size: 0.85rem;"></textarea>
                                                @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.descripciones_calificacion.' . $nivel['id_temporal'])
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        @endforeach
                                        <td class="text-center align-middle">
                                            @if (count($componente['criterios']) > 1)
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="removeCriterio({{ $indexComponente }}, {{ $indexCriterio }})"
                                                    title="Eliminar criterio">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info"
                        wire:click="addCriterio({{ $indexComponente }})">
                        <i class="bi bi-plus-lg"></i> Añadir Criterio
                    </button>
                </div>
            </div>
        @endforeach

        <!-- Botón Agregar Componente -->
        <div class="mb-4">
            <button type="button" class="btn btn-success" wire:click="addComponente">
                <i class="bi bi-plus-circle me-2"></i> Añadir Nuevo Componente
            </button>
        </div>

        <!-- Botones de Acción -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn px-4"
                            style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ $modoEdicion ? 'Actualizar Rúbrica' : 'Guardar Rúbrica' }}
                    </button>
                    <a href="{{ route('rubricas.') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
