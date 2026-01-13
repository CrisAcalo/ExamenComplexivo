@section('title', __('Carreras'))
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
                            <i class="bi bi-mortarboard fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            CARRERAS ACADÉMICAS
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de carreras, modalidades y sedes
                        </p>
                    </div>
                </div>
                @can('gestionar carreras')
                    <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#createDataModal"
                            style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-plus-circle me-2"></i>Agregar Nueva Carrera
                    </button>
                @endcan
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header con Buscador -->
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-ul me-2"></i>Listado de Carreras
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por código, nombre, departamento..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.carreras.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 150px;">
                                        <i class="bi bi-hash me-1"></i>Código
                                    </th>
                                    <th>
                                        <i class="bi bi-mortarboard me-1"></i>Nombre de Carrera
                                    </th>
                                    <th style="width: 200px;">
                                        <i class="bi bi-building me-1"></i>Departamento
                                    </th>
                                    <th style="width: 130px;">
                                        <i class="bi bi-laptop me-1"></i>Modalidad
                                    </th>
                                    <th style="width: 130px;">
                                        <i class="bi bi-geo-alt me-1"></i>Sede
                                    </th>
                                    <th style="width: 180px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($carreras as $row)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge px-3 py-2"
                                                   style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); font-size: 13px;">
                                                {{ $row->codigo_carrera }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">{{ $row->nombre }}</td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-building-check me-1"></i>
                                                {{ $row->departamento ? $row->departamento->nombre : 'Sin asignar' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($row->modalidad == 'Presencial')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-person-workspace me-1"></i>{{ $row->modalidad }}
                                                </span>
                                            @else
                                                <span class="badge bg-info">
                                                    <i class="bi bi-wifi me-1"></i>{{ $row->modalidad }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-pin-map me-1"></i>{{ $row->sede }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('gestionar carreras')
                                                    <button class="btn btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#updateDataModal"
                                                            wire:click="edit({{ $row->id }})"
                                                            title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteDataModal"
                                                            wire:click="eliminar({{ $row->id }})"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">Solo lectura</span>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron carreras</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($carreras->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $carreras->firstItem() ?? 0 }} - {{ $carreras->lastItem() ?? 0 }}
                                de {{ $carreras->total() }} registros
                            </small>
                            <div>
                                {{ $carreras->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
