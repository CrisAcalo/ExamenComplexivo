<div>
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
                            <i class="bi bi-calendar-event fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            PERÍODO: {{ $periodo->codigo_periodo }}
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de carreras asignadas al período académico
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
            <li class="breadcrumb-item active" aria-current="page">{{ $periodo->codigo_periodo }}</li>
        </ol>
    </nav>

    @include('partials.alerts')
    @include('livewire.periodos.profile.modals')

    <!-- Header Sección Carreras -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                        <i class="bi bi-mortarboard me-2"></i>Carreras Asignadas
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    @php
                        $user = auth()->user();
                        $canManageCarrerasPeriodos = $user->hasRole('Super Admin') && $user->hasPermissionTo('asignar carrera a periodo');
                    @endphp
                    @if($canManageCarrerasPeriodos)
                        <button data-bs-toggle="modal" data-bs-target="#createDataModal"
                                class="btn text-white"
                                style="background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);">
                            <i class="bi bi-plus-circle me-2"></i>Añadir Carrera
                        </button>
                    @else
                        <span class="badge bg-secondary">
                            <i class="bi bi-lock me-1"></i>Solo lectura
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Carreras -->
    <div class="row">
        @forelse ($periodos_carreras as $periodoCarrera)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100 border-0 position-relative overflow-hidden">
                    <!-- Banda Superior con Color -->
                    <div style="height: 6px; background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);"></div>

                    <div class="card-body d-flex flex-column p-4">
                        <!-- Nombre de la Carrera -->
                        <h5 class="card-title mb-3 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-mortarboard-fill me-2"></i>
                            {{ $periodoCarrera->carrera->nombre }}
                        </h5>

                        <!-- Información del Personal -->
                        <div class="mb-3">
                            <div class="mb-2 p-2 rounded" style="background-color: #f8f9fa;">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-person-badge text-success me-1"></i>
                                    <strong>Director:</strong>
                                </small>
                                <div class="ms-3">{{ $periodoCarrera->director->name }} {{ $periodoCarrera->director->lastname }} </div>
                            </div>

                            <div class="p-2 rounded" style="background-color: #f8f9fa;">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-person-check text-info me-1"></i>
                                    <strong>Docente Apoyo:</strong>
                                </small>
                                <class class="ms-3">{{ $periodoCarrera->docenteApoyo->name }} {{ $periodoCarrera->docenteApoyo->lastname }} </class>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                @php
                                    // El Super Admin no debe ver la opción de tribunales ya que no se encarga de calificaciones
                                    $canViewTribunales = !$user->hasRole('Super Admin');
                                @endphp

                                @if($canViewTribunales)
                                    <a href="{{ route('periodos.tribunales.index', ['carreraPeriodoId' => $periodoCarrera->id]) }}"
                                       class="btn btn-outline-info">
                                        <i class="bi bi-people me-2"></i>Ver Tribunales
                                    </a>
                                @endif

                                @if($canManageCarrerasPeriodos)
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-primary"
                                                wire:click="edit({{ $periodoCarrera->id }})"
                                                data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                title="Editar asignación">
                                            <i class="bi bi-pencil-square me-1"></i>Editar
                                        </button>
                                        <button class="btn btn-outline-danger"
                                                wire:click="eliminar({{ $periodoCarrera->id }})"
                                                data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                                title="Eliminar asignación">
                                            <i class="bi bi-trash me-1"></i>Eliminar
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <h5>No hay carreras asignadas</h5>
                            <p class="mb-3">Este período aún no tiene carreras configuradas</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
