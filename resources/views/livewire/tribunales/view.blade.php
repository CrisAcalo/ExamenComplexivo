@section('title', __('Tribunales'))

@push('styles')
    {{-- Estilos para las columnas ordenables --}}
    <style>
        .sortable-header {
            transition: all 0.2s ease;
            position: relative;
            user-select: none;
        }

        .sortable-header:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
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

        /* Indicador visual adicional para columnas ordenables */
        .sortable-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: #0d6efd;
            transition: width 0.3s ease, left 0.3s ease;
        }

        .sortable-header:hover::after {
            width: 80%;
            left: 10%;
        }

        .table th.sortable-header {
            border-bottom: 2px solid #dee2e6;
        }
    </style>
@endpush

<div class="container-fluid p-0">
    @include('partials.alerts') {{-- Para los mensajes flash --}}

    {{-- Breadcrumbs --}}
    <div class="fs-2 fw-semibold mb-4">
        <a href="{{ route('periodos.') }}">Períodos</a> /
        <a href="{{ route('periodos.profile', $periodo->id) }}">{{ $periodo->codigo_periodo }}</a> /
        <span class="text-muted">{{ $carrera->nombre }}</span>
    </div>
    {{-- Sección para Visualizar el Plan de Evaluación Activo --}}
    @if ($planEvaluacionActivo)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-card-checklist text-success"></i>
                    Plan de Evaluación Activo: <span class="fw-normal">{{ $planEvaluacionActivo->nombre }}</span>
                    @if ($puedeGestionar)
                        <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                            class="btn btn-sm btn-outline-primary float-end">
                            <i class="bi bi-pencil-square"></i> Gestionar Plan
                        </a>
                    @endif
                </h5>
            </div>
            @if ($planEvaluacionActivo->itemsPlanEvaluacion->count() > 0)
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @php $totalPonderacionPlan = 0; @endphp
                        @foreach ($planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $itemPlan->nombre_item }}</strong>
                                    <small class="text-muted d-block">
                                        Tipo:
                                        {{ $itemPlan->tipo_item == 'NOTA_DIRECTA' ? 'Nota Directa' : 'Rúbrica Tabular' }}
                                        @if ($itemPlan->tipo_item == 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla)
                                            (Usando: {{ $itemPlan->rubricaPlantilla->nombre }})
                                        @endif
                                    </small>
                                </div>
                                <span
                                    class="badge bg-primary rounded-pill fs-6">{{ $itemPlan->ponderacion_global }}%</span>
                            </li>
                            @php $totalPonderacionPlan += $itemPlan->ponderacion_global; @endphp
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer text-muted small">
                    Ponderación total del plan: {{ $totalPonderacionPlan }}%
                    @if (round($totalPonderacionPlan, 2) != 100.0 && $planEvaluacionActivo->itemsPlanEvaluacion->count() > 0)
                        <span class="text-danger fw-bold ms-2">¡Advertencia! La suma de ponderaciones no es 100%.</span>
                    @endif
                </div>
            @else
                <div class="card-body text-center">
                    <p class="text-muted mb-0">No hay ítems definidos en el plan de evaluación activo.</p>
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm" role="alert">
            <div>
                <i class="bi bi-exclamation-triangle-fill"></i>
                No se ha configurado un Plan de Evaluación para esta carrera y período.
            </div>
            @if ($puedeGestionar)
                <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                    class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil-square"></i> Configurar Plan Ahora
                </a>
            @endif
        </div>
    @endif
    {{-- Fin Sección Plan de Evaluación --}}


    {{-- SECCIÓN: ASIGNACIÓN DE CALIFICADORES GENERALES --}}
    @if ($puedeGestionar)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-people-fill text-info"></i> Asignar Calificadores Generales</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="guardarCalificadoresGenerales">
                    <div class="row">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="col-md-4 mb-3">
                                <label for="calificador_general_{{ $i }}" class="form-label">Calificador
                                    General {{ $i + 1 }}</label>
                                <select wire:model.defer="calificadoresGeneralesSeleccionados.{{ $i }}"
                                    id="calificador_general_{{ $i }}"
                                    class="form-select form-select-sm @error('calificadoresGeneralesSeleccionados.' . $i) is-invalid @enderror"
                                    data-search="true" data-placeholder="-- Sin asignar --">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach ($profesoresDisponiblesParaCalificadorGeneral as $profesor)
                                        {{-- Lógica para deshabilitar si ya está seleccionado en otro select --}}
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
                                            {{ $profesor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('calificadoresGeneralesSeleccionados.' . $i)
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endfor
                    </div>
                    @error('calificadoresGeneralesSeleccionados')
                        {{-- Error general para el array, si es necesario --}}
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-save"></i> Guardar Calificadores Generales
                    </button>
                </form>
            </div>
        </div>
    @endif
    {{-- FIN NUEVA SECCIÓN --}}


    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 class="mb-0"><i class="bi bi-diagram-3 text-info"></i>
                            Listado de Tribunales</h4>
                        <div class="ms-auto me-2"> {{-- Buscador --}}
                            <input wire:model.debounce.500ms='keyWord' type="text"
                                class="form-control form-control-sm" name="search" id="search"
                                placeholder="Buscar por estudiante o fecha...">
                        </div>
                        <div> {{-- Botón Añadir Tribunal --}}
                            @if ($puedeGestionar)
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#createDataModal">
                                    <i class="bi bi-plus-lg"></i> Añadir Tribunal
                                </button>
                            @endif
                        </div>
                    </div>
                    {{-- Control de paginación y filtros --}}
                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center">
                            <label class="me-2 mb-0">Mostrar</label>
                            <select wire:model="perPage" class="form-select form-select-sm w-auto me-2">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="me-3">registros por página</span>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            Haz clic en las columnas para ordenar ascendente/descendente
                            @if ($sortField && $sortDirection)
                                <span class="ms-2 text-primary">
                                    <i class="bi bi-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    Ordenado por:
                                    {{ $sortField === 'estudiante'
                                        ? 'Estudiante'
                                        : ($sortField === 'fecha'
                                            ? 'Fecha'
                                            : ($sortField === 'hora_inicio'
                                                ? 'Horario'
                                                : ($sortField === 'descripcion_plantilla'
                                                    ? 'Descripción'
                                                    : ucfirst($sortField)))) }}
                                    ({{ $sortDirection === 'asc' ? 'A-Z' : 'Z-A' }})
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.tribunales.modals') {{-- Contiene createDataModal y el nuevo deleteTribunalModal --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle"> {{-- table-hover y align-middle para mejor estética --}}
                            <thead class="table-light"> {{-- thead-light o table-light --}}
                                <tr>
                                    <th>#</th>
                                    <th wire:click="sortBy('estudiante')" class="sortable-header user-select-none"
                                        style="cursor: pointer;">
                                        Estudiante
                                        <span class="sort-icon">
                                            <i class="bi {{ $this->getSortIcon('estudiante') }}"></i>
                                        </span>
                                    </th>
                                    <th wire:click="sortBy('fecha')" class="sortable-header user-select-none"
                                        style="cursor: pointer;">
                                        Fecha
                                        <span class="sort-icon">
                                            <i class="bi {{ $this->getSortIcon('fecha') }}"></i>
                                        </span>
                                    </th>
                                    <th wire:click="sortBy('hora_inicio')" class="sortable-header user-select-none"
                                        style="cursor: pointer;">
                                        Horario
                                        <span class="sort-icon">
                                            <i class="bi {{ $this->getSortIcon('hora_inicio') }}"></i>
                                        </span>
                                    </th>
                                    <th>Miembros del Tribunal</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tribunales as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $row->estudiante->nombres }} {{ $row->estudiante->apellidos }}
                                            <small
                                                class="d-block text-muted">{{ $row->estudiante->ID_estudiante }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->hora_inicio)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($row->hora_fin)->format('H:i') }}</td>
                                        <td>
                                            @foreach ($row->miembrosTribunales as $miembro)
                                                <span
                                                    class="badge
                                                    @if ($miembro->status == 'PRESIDENTE') bg-success
                                                    @elseif($miembro->status == 'INTEGRANTE1') bg-info text-dark
                                                    @elseif($miembro->status == 'INTEGRANTE2') bg-secondary
                                                    @else bg-primary @endif
                                                    mb-1 me-1">
                                                    {{-- {{ Str::title(Str::lower(Str::replaceFirst('INTEGRANTE', 'Int. ', $miembro->status))) }}:  --}}
                                                    {{ $miembro->user->name }}
                                                    ({{ Str::ucfirst(Str::lower(Str::replaceFirst('INTEGRANTE', 'Int. ', $miembro->status))) }})
                                                </span><br>
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
                                        <td width="200" class="text-center"> {{-- Ancho ajustado para más botones --}}
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('periodos.tribunales.profile', $row->id) }}"
                                                    class="btn btn-primary" title="Ver/Calificar Tribunal">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                @if ($puedeGestionar)
                                                    @if ($row->estado === 'ABIERTO')
                                                        <button type="button" class="btn btn-outline-danger"
                                                            wire:click="cerrarTribunal({{ $row->id }})"
                                                            wire:confirm="¿Está seguro que desea cerrar este tribunal?"
                                                            title="Cerrar Tribunal">
                                                            <i class="bi bi-lock-fill"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-success"
                                                            wire:click="abrirTribunal({{ $row->id }})"
                                                            wire:confirm="¿Está seguro que desea abrir este tribunal?"
                                                            title="Abrir Tribunal">
                                                            <i class="bi bi-unlock-fill"></i>
                                                        </button>
                                                    @endif

                                                    <button type="button" class="btn btn-danger"
                                                        wire:click="confirmDelete({{ $row->id }})"
                                                        title="Eliminar Tribunal">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center py-3" colspan="7">
                                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                                            No se encontraron tribunales para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Mostrando {{ $tribunales->firstItem() ?? 0 }} - {{ $tribunales->lastItem() ?? 0 }}
                                de {{ $tribunales->total() }} tribunales
                            </div>
                            <div>
                                {{ $tribunales->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN DE PLANTILLAS DE TRIBUNAL --}}
            @if ($puedeGestionar && $plantillas->count() > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-diagram-2"></i> Plantillas de Tribunal
                            <small class="ms-2 opacity-75">Configuraciones base para generar múltiples
                                tribunales</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th wire:click="sortBy('descripcion_plantilla')"
                                            class="sortable-header user-select-none" style="cursor: pointer;">
                                            Descripción
                                            <span class="sort-icon">
                                                <i class="bi {{ $this->getSortIcon('descripcion_plantilla') }}"></i>
                                            </span>
                                        </th>
                                        <th wire:click="sortBy('fecha')" class="sortable-header user-select-none"
                                            style="cursor: pointer;">
                                            Fecha Base
                                            <span class="sort-icon">
                                                <i class="bi {{ $this->getSortIcon('fecha') }}"></i>
                                            </span>
                                        </th>
                                        <th wire:click="sortBy('hora_inicio')"
                                            class="sortable-header user-select-none" style="cursor: pointer;">
                                            Horario Base
                                            <span class="sort-icon">
                                                <i class="bi {{ $this->getSortIcon('hora_inicio') }}"></i>
                                            </span>
                                        </th>
                                        <th>Miembros del Tribunal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plantillas as $plantilla)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $plantilla->descripcion_plantilla }}</strong>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-diagram-2"></i> Plantilla de tribunal
                                                </small>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($plantilla->fecha)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($plantilla->hora_inicio)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($plantilla->hora_fin)->format('H:i') }}</td>
                                            <td>
                                                @foreach ($plantilla->miembrosTribunales as $miembro)
                                                    <span
                                                        class="badge
                                                    @if ($miembro->status == 'PRESIDENTE') bg-success
                                                    @elseif($miembro->status == 'INTEGRANTE1') bg-info text-dark
                                                    @elseif($miembro->status == 'INTEGRANTE2') bg-secondary
                                                    @else bg-primary @endif
                                                    mb-1 me-1">
                                                        {{ $miembro->user->name }}
                                                        ({{ Str::ucfirst(Str::lower(Str::replaceFirst('INTEGRANTE', 'Int. ', $miembro->status))) }})
                                                    </span><br>
                                                @endforeach
                                            </td>
                                            <td width="150" class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-success"
                                                        wire:click="abrirAsignarEstudiantes({{ $plantilla->id }})"
                                                        title="Asignar Estudiantes">
                                                        <i class="bi bi-people-fill"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger"
                                                        wire:click="confirmDelete({{ $plantilla->id }})"
                                                        title="Eliminar Plantilla">
                                                        <i class="bi bi-trash-fill"></i>
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
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir efectos visuales a las columnas ordenables
            const sortableHeaders = document.querySelectorAll('.sortable-header');

            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    // Añadir efecto de "clic" visual
                    this.style.transform = 'translateY(1px)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-1px)';
                    }, 100);

                    // Mostrar indicador de carga temporal
                    const icon = this.querySelector('.sort-icon i');
                    if (icon) {
                        const originalClass = icon.className;
                        icon.className = 'bi bi-arrow-clockwise';
                        icon.style.animation = 'spin 0.5s linear';

                        setTimeout(() => {
                            icon.className = originalClass;
                            icon.style.animation = '';
                        }, 500);
                    }
                });
            });
        });

        // Animación para el spinner
        const style = document.createElement('style');
        style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
        document.head.appendChild(style);
    </script>
@endpush
