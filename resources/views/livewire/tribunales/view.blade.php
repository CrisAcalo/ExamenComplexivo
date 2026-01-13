@section('title', __('Tribunales'))

@push('styles')
    {{-- Estilos para las columnas ordenables --}}
    <style>
        .sortable-header {
            transition: all 0.2s ease;
            position: relative;
            user-select: none;
            cursor: pointer;
        }

        .sortable-header:hover {
            background-color: rgba(45, 122, 79, 0.1) !important;
            transform: translateY(-1px);
        }

        .sortable-header:active {
            transform: translateY(0);
        }

        .sort-icon {
            font-size: 0.8em;
            margin-left: 5px;
            transition: transform 0.2s ease;
        }

        .sortable-header:hover .sort-icon {
            transform: scale(1.1);
        }
    </style>
@endpush

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
                            <i class="bi bi-diagram-3 fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            GESTIÓN DE TRIBUNALES
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            {{ $carrera->nombre }} - {{ $periodo->codigo_periodo }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('periodos.') }}" class="text-decoration-none">
                    <i class="bi bi-calendar3 me-1"></i>Períodos
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('periodos.profile', $periodo->id) }}" class="text-decoration-none">
                    {{ $periodo->codigo_periodo }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $carrera->nombre }}</li>
        </ol>
    </nav>

    @include('partials.alerts')

    <!-- Fila con dos columnas: Plan de Evaluación y Calificadores Generales -->
    <div class="row mb-4">
        <!-- Columna Izquierda: Plan de Evaluación Activo -->
        <div class="col-md-6">
            @if ($planEvaluacionActivo)
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-card-checklist me-2"></i>Plan de Evaluación Activo
                            </h5>
                            @if ($puedeGestionar)
                                <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Gestionar
                                </a>
                            @endif
                        </div>
                    </div>
                    @if ($planEvaluacionActivo->itemsPlanEvaluacion->count() > 0)
                        <div class="card-body p-0">
                            <div class="p-3">
                                <h6 class="fw-semibold mb-3">{{ $planEvaluacionActivo->nombre }}</h6>
                            </div>
                            <ul class="list-group list-group-flush">
                                @php $totalPonderacionPlan = 0; @endphp
                                @foreach ($planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size: 14px;">{{ $itemPlan->nombre_item }}</div>
                                            <small class="text-muted">
                                                {{ $itemPlan->tipo_item == 'NOTA_DIRECTA' ? 'Nota Directa' : 'Rúbrica Tabular' }}
                                                @if ($itemPlan->tipo_item == 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla)
                                                    ({{ $itemPlan->rubricaPlantilla->nombre }})
                                                @endif
                                            </small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">{{ $itemPlan->ponderacion_global }}%</span>
                                    </li>
                                    @php $totalPonderacionPlan += $itemPlan->ponderacion_global; @endphp
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer py-2" style="background-color: #f8f9fa;">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Ponderación total: <strong>{{ $totalPonderacionPlan }}%</strong>
                                @if (round($totalPonderacionPlan, 2) != 100.0)
                                    <span class="text-danger fw-bold ms-2">¡Advertencia! La suma no es 100%.</span>
                                @endif
                            </small>
                        </div>
                    @else
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                            <p class="text-muted mb-0">No hay ítems definidos en el plan de evaluación activo.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #f39c12;">
                        <h5 class="mb-0 fw-bold" style="color: #f39c12;">
                            <i class="bi bi-exclamation-triangle me-2"></i>Plan de Evaluación
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-5">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3"></i>
                        <p class="text-center mb-3">No se ha configurado un Plan de Evaluación para esta carrera y período.</p>
                        @if ($puedeGestionar)
                            <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                                class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Configurar Plan Ahora
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna Derecha: Asignar Calificadores Generales -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
                    <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                        <i class="bi bi-people-fill me-2"></i>Asignar Calificadores Generales
                    </h5>
                </div>
                <div class="card-body">
                    @if ($puedeGestionar)
                        <form wire:submit.prevent="guardarCalificadoresGenerales">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="mb-3">
                                    <label for="calificador_general_{{ $i }}" class="form-label fw-semibold small">
                                        <i class="bi bi-person-badge me-1"></i>Calificador General {{ $i + 1 }}
                                    </label>
                                    <select wire:model.defer="calificadoresGeneralesSeleccionados.{{ $i }}"
                                        id="calificador_general_{{ $i }}"
                                        class="form-select form-select-sm @error('calificadoresGeneralesSeleccionados.' . $i) is-invalid @enderror"
                                        data-search="true" data-placeholder="-- Sin asignar --">
                                        <option value="">-- Sin asignar --</option>
                                        @foreach ($profesoresDisponiblesParaCalificadorGeneral as $profesor)
                                            @php
                                                $isDisabled = false;
                                                for ($j = 0; $j < 3; $j++) {
                                                    if (
                                                        $i != $j &&
                                                        isset($calificadoresGeneralesSeleccionados[$j]) &&
                                                        $calificadoresGeneralesSeleccionados[$j] == $profesor->id
                                                    ) {
                                                        $isDisabled = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <option value="{{ $profesor->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                                {{ $profesor->name }} {{ $profesor->lastname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('calificadoresGeneralesSeleccionados.' . $i)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endfor
                            @error('calificadoresGeneralesSeleccionados')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-sm text-white w-100"
                                    style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                <i class="bi bi-save"></i> Guardar Calificadores Generales
                            </button>
                        </form>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-lock fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted">Solo usuarios con permisos de gestión pueden asignar calificadores generales.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Tribunales -->
    <div class="card shadow-sm border-0 mb-4">
        <!-- Header con Buscador -->
        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                        <i class="bi bi-diagram-3 me-2"></i>Listado de Tribunales
                    </h5>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input wire:model.debounce.500ms="keyWord" type="text"
                               class="form-control border-start-0"
                               placeholder="Buscar por estudiante o fecha..."
                               style="box-shadow: none;">
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if ($puedeGestionar)
                        <div class="d-inline-flex align-items-center">
                            <button type="button" class="btn text-white me-2"
                                    wire:click="abrirModalImportacion"
                                    data-bs-toggle="modal" data-bs-target="#importarTribunalesModal"
                                    style="background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);">
                                <i class="bi bi-file-earmark-excel me-2"></i>Importar desde Excel
                            </button>
                            <button type="button" class="btn text-white"
                                    data-bs-toggle="modal" data-bs-target="#createDataModal"
                                    style="background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);">
                                <i class="bi bi-plus-circle me-2"></i>Añadir Tribunal
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Control de paginación y filtros -->
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0 small">Mostrar</label>
                    <select wire:model="perPage" class="form-select form-select-sm w-auto me-2">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="small">registros</span>
                </div>
                @if ($sortField && $sortDirection)
                    <small class="text-muted">
                        <i class="bi bi-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} me-1"></i>
                        Ordenado por:
                        <strong>{{ $sortField === 'estudiante' ? 'Estudiante' : ($sortField === 'fecha' ? 'Fecha' : ucfirst($sortField)) }}</strong>
                        ({{ $sortDirection === 'asc' ? 'A-Z' : 'Z-A' }})
                    </small>
                @endif
            </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
            @include('livewire.tribunales.modals')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <tr>
                            <th style="width: 100px;">Tribunal</th>
                            <th wire:click="sortBy('caso')" class="sortable-header" style="width: 80px;">
                                <i class="bi bi-file-text me-1"></i>Caso
                                <span class="sort-icon">
                                    <i class="bi {{ $this->getSortIcon('caso') }}"></i>
                                </span>
                            </th>
                            <th wire:click="sortBy('estudiante')" class="sortable-header">
                                <i class="bi bi-person me-1"></i>Estudiante
                                <span class="sort-icon">
                                    <i class="bi {{ $this->getSortIcon('estudiante') }}"></i>
                                </span>
                            </th>
                            <th wire:click="sortBy('fecha')" class="sortable-header" style="width: 120px;">
                                <i class="bi bi-calendar3 me-1"></i>Fecha
                                <span class="sort-icon">
                                    <i class="bi {{ $this->getSortIcon('fecha') }}"></i>
                                </span>
                            </th>
                            <th wire:click="sortBy('hora_inicio')" class="sortable-header" style="width: 130px;">
                                <i class="bi bi-clock me-1"></i>Horario
                                <span class="sort-icon">
                                    <i class="bi {{ $this->getSortIcon('hora_inicio') }}"></i>
                                </span>
                            </th>
                            <th style="width: 80px;" class="text-center">Lab.</th>
                            <th>Miembros del Tribunal</th>
                            <th style="width: 100px;" class="text-center">Estado</th>
                            <th style="width: 180px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tribunales as $row)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td>
                                    @if($row->nombre_tribunal)
                                        <span class="badge bg-primary">{{ $row->nombre_tribunal }}</span>
                                    @else
                                        <span class="badge bg-primary">Tribunal {{ $loop->iteration }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row->caso)
                                        <span class="badge bg-info">{{ $row->caso }}</span>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold" style="color: #333;">
                                        {{ $row->estudiante->nombres }} {{ $row->estudiante->apellidos }}
                                    </div>
                                    <small class="text-muted">{{ $row->estudiante->ID_estudiante }}</small>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ \Carbon\Carbon::parse($row->hora_inicio)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($row->hora_fin)->format('H:i') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($row->laboratorio)
                                        <span class="badge bg-secondary">{{ $row->laboratorio }}</span>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($row->miembrosTribunales as $miembro)
                                        <span class="badge
                                            @if ($miembro->status == 'PRESIDENTE') bg-success
                                            @elseif($miembro->status == 'INTEGRANTE1') bg-success
                                            @elseif($miembro->status == 'INTEGRANTE2') bg-success
                                            @else bg-primary @endif
                                            mb-1 me-1" style="font-size: 10px;">
                                            {{ $miembro->user->name }} {{ $miembro->user->lastname }}
                                            ({{ Str::ucfirst(Str::lower(Str::replaceFirst('INTEGRANTE', 'Int. ', $miembro->status))) }})
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    @if ($row->estado === 'CERRADO')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-lock-fill"></i> Cerrado
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-unlock-fill"></i> Abierto
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('periodos.tribunales.profile', $row->id) }}"
                                            class="btn btn-outline-primary" title="Ver/Calificar Tribunal">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @if ($puedeGestionar)
                                            @if ($row->estado === 'ABIERTO')
                                                <button type="button" class="btn btn-outline-danger"
                                                    wire:click="cerrarTribunal({{ $row->id }})"
                                                    wire:confirm="¿Está seguro que desea cerrar este tribunal?"
                                                    title="Cerrar Tribunal">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            @else
                                                @php
                                                    $fechaHoraFin = \Carbon\Carbon::parse($row->fecha . ' ' . $row->hora_fin);
                                                    $yaFinalizo = now()->greaterThan($fechaHoraFin);
                                                @endphp
                                                @if($yaFinalizo)
                                                    <button type="button" class="btn btn-outline-secondary" disabled
                                                        title="No se puede abrir. La franja horaria finalizó el {{ $fechaHoraFin->format('d/m/Y H:i') }}">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-success"
                                                        wire:click="abrirTribunal({{ $row->id }})"
                                                        wire:confirm="¿Está seguro que desea abrir este tribunal?"
                                                        title="Abrir Tribunal">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            <button type="button" class="btn btn-outline-danger"
                                                wire:click="confirmDelete({{ $row->id }})"
                                                title="Eliminar Tribunal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    <p class="text-muted mb-0">No se encontraron tribunales</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer con Paginación -->
        @if($tribunales->hasPages())
            <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $tribunales->firstItem() ?? 0 }} - {{ $tribunales->lastItem() ?? 0 }}
                        de {{ $tribunales->total() }} tribunales
                    </small>
                    <div>
                        {{ $tribunales->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- SECCIÓN DE PLANTILLAS DE TRIBUNAL --}}
    @if ($puedeGestionar && $plantillas->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <h5 class="mb-0 fw-bold text-white">
                    <i class="bi bi-diagram-2 me-2"></i>Plantillas de Tribunal
                    <small class="ms-2 opacity-75">Configuraciones base para generar múltiples tribunales</small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th wire:click="sortBy('descripcion_plantilla')" class="sortable-header">
                                    <i class="bi bi-card-text me-1"></i>Descripción
                                    <span class="sort-icon">
                                        <i class="bi {{ $this->getSortIcon('descripcion_plantilla') }}"></i>
                                    </span>
                                </th>
                                <th wire:click="sortBy('fecha')" class="sortable-header" style="width: 120px;">
                                    <i class="bi bi-calendar3 me-1"></i>Fecha Base
                                    <span class="sort-icon">
                                        <i class="bi {{ $this->getSortIcon('fecha') }}"></i>
                                    </span>
                                </th>
                                <th wire:click="sortBy('hora_inicio')" class="sortable-header" style="width: 130px;">
                                    <i class="bi bi-clock me-1"></i>Horario Base
                                    <span class="sort-icon">
                                        <i class="bi {{ $this->getSortIcon('hora_inicio') }}"></i>
                                    </span>
                                </th>
                                <th>Miembros del Tribunal</th>
                                <th style="width: 150px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plantillas as $plantilla)
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold" style="color: #333;">{{ $plantilla->descripcion_plantilla }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-diagram-2"></i> Plantilla de tribunal
                                        </small>
                                    </td>
                                    <td><small>{{ \Carbon\Carbon::parse($plantilla->fecha)->format('d/m/Y') }}</small></td>
                                    <td>
                                        <small>
                                            {{ \Carbon\Carbon::parse($plantilla->hora_inicio)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($plantilla->hora_fin)->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @foreach ($plantilla->miembrosTribunales as $miembro)
                                            <span class="badge
                                                @if ($miembro->status == 'PRESIDENTE') bg-success
                                                @elseif($miembro->status == 'INTEGRANTE1') bg-info
                                                @elseif($miembro->status == 'INTEGRANTE2') bg-secondary
                                                @else bg-primary @endif
                                                mb-1 me-1" style="font-size: 10px;">
                                                {{ $miembro->user->name }}
                                                ({{ Str::ucfirst(Str::lower(Str::replaceFirst('INTEGRANTE', 'Int. ', $miembro->status))) }})
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-success"
                                                wire:click="abrirAsignarEstudiantes({{ $plantilla->id }})"
                                                title="Asignar Estudiantes">
                                                <i class="bi bi-people-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                wire:click="confirmDelete({{ $plantilla->id }})"
                                                title="Eliminar Plantilla">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableHeaders = document.querySelectorAll('.sortable-header');

            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    this.style.transform = 'translateY(1px)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-1px)';
                    }, 100);
                });
            });
        });
    </script>
@endpush
