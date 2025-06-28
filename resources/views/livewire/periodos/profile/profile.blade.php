<div>
    @include('partials.alerts')
    @include('livewire.periodos.profile.modals')

    <div class="fs-2 fw-semibold mb-4">
        <a href="{{route('periodos.')}}">Períodos</a> /
        {{$periodo->codigo_periodo}}
    </div>

    <div class="mt-4 d-flex flex-row align-items-center">
        <h3 class="me-3">Carreras</h3>
        <button data-bs-toggle="modal" data-bs-target="#createDataModal" class="btn btn-sm btn-info me-3"
            style="height: max-content">
            Añadir Carrera
        </button>
    </div>

    <div class="row mt-3">
        @forelse ($periodos_carreras as $periodoCarrera)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2 text-primary">{{ $periodoCarrera->carrera->nombre }}</h5>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item px-0 py-1 border-0">
                                <strong>Director:</strong>
                                <span class="text-dark">{{ $periodoCarrera->director->name }}</span>
                            </li>
                            <li class="list-group-item px-0 py-1 border-0">
                                <strong>Docente Apoyo:</strong>
                                <span class="text-dark">{{ $periodoCarrera->docenteApoyo->name }}</span>
                            </li>
                        </ul>
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('periodos.tribunales.index', ['carreraPeriodoId' => $periodoCarrera->id]) }}"
                                class="btn btn-outline-info btn-sm flex-fill">
                                <i class="bi bi-people"></i> Tribunales
                            </a>
                            <button class="btn btn-outline-primary btn-sm flex-fill"
                                wire:click="edit({{ $periodoCarrera->id }})"
                                data-bs-toggle="modal" data-bs-target="#updateDataModal">
                                <i class="fa fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-outline-danger btn-sm flex-fill"
                                wire:click="eliminar({{ $periodoCarrera->id }})"
                                data-bs-toggle="modal" data-bs-target="#deleteDataModal">
                                <i class="fa fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <p class="mb-0">No hay carreras disponibles para este periodo</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
