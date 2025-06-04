@section('title', __('Tribunales'))
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
                    <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                        class="btn btn-sm btn-outline-primary float-end">
                        <i class="bi bi-pencil-square"></i> Gestionar Plan
                    </a>
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
            <a href="{{ route('planes_evaluacion.manage', ['carreraPeriodoId' => $carreraPeriodo->id]) }}"
                class="btn btn-sm btn-warning">
                <i class="bi bi-pencil-square"></i> Configurar Plan Ahora
            </a>
        </div>
    @endif
    {{-- Fin Sección Plan de Evaluación --}}


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
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createDataModal">
                                <i class="bi bi-plus-lg"></i> Añadir Tribunal
                            </button>
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
                                    <th>Estudiante</th>
                                    <th>Fecha</th>
                                    <th>Horario</th>
                                    <th>Miembros del Tribunal</th>
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
                                        <td width="120" class="text-center"> {{-- Ancho ajustado para dos botones --}}
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('periodos.tribunales.profile', $row->id) }}"
                                                    class="btn btn-primary" title="Ver/Calificar Tribunal">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger"
                                                    wire:click="confirmDelete({{ $row->id }})"
                                                    title="Eliminar Tribunal">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center py-3" colspan="6">
                                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                                            No se encontraron tribunales para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $tribunales->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
