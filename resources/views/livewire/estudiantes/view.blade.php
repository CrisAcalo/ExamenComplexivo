@section('title', __('Estudiantes'))
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
                            <i class="bi bi-people fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            GESTIÓN DE ESTUDIANTES
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Administración de estudiantes por carrera y período
                        </p>
                    </div>
                </div>
                <div class="btn-group">
                    @if($this->puedeImportarEstudiantes())
                        <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#importModal"
                                style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='transparent'">
                            <i class="bi bi-file-earmark-excel me-2"></i>Importar Estudiantes
                        </button>
                    @endif
                    @if($this->puedeGestionarEstudiantes())
                        <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#createDataModal"
                                style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='transparent'">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Estudiante
                        </button>
                    @endif
                    @if($this->puedeExportarEstudiantes() && !$this->puedeGestionarEstudiantes() && !$this->puedeImportarEstudiantes())
                        <button class="btn btn-lg text-white"
                                style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='transparent'">
                            <i class="bi bi-download me-2"></i>Exportar Estudiantes
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header con Buscador y Filtros -->
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-ul me-2"></i>Listado de Estudiantes
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por nombre, apellido, cédula, correo..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 fw-semibold small">Mostrar:</label>
                                <select wire:model="perPage" class="form-select form-select-sm w-auto">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ms-2 small text-muted">filas</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 fw-semibold small">
                                    <i class="bi bi-mortarboard me-1"></i>Carrera-Período:
                                </label>
                                <select wire:model="carrera_periodo_filter" class="form-select form-select-sm" style="max-width: 400px;">
                                    @if($this->puedeVerTodosEstudiantes())
                                        <option value="">Todos los estudiantes</option>
                                    @else
                                        @foreach($carrerasPeriodosDisponibles as $cp)
                                            <option value="{{ $cp->id }}">
                                                {{ $cp->carrera->nombre ?? 'N/A' }} - {{ $cp->periodo->codigo_periodo ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    @endif                                
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.estudiantes.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-person me-1"></i>Nombres
                                    </th>
                                    <th>
                                        <i class="bi bi-person me-1"></i>Apellidos
                                    </th>
                                    <th style="width: 120px;">
                                        <i class="bi bi-card-text me-1"></i>Cédula
                                    </th>
                                    <th>
                                        <i class="bi bi-envelope me-1"></i>Correo
                                    </th>
                                    <th style="width: 120px;">
                                        <i class="bi bi-telephone me-1"></i>Teléfono
                                    </th>
                                    <th style="width: 130px;">
                                        <i class="bi bi-person-badge me-1"></i>Username
                                    </th>
                                    <th style="width: 120px;">
                                        <i class="bi bi-hash me-1"></i>ID Estudiante
                                    </th>
                                    <th style="width: 120px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estudiantes as $row)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $row->nombres }}</td>
                                        <td class="fw-semibold">{{ $row->apellidos }}</td>
                                        <td>
                                            <span class="badge px-2 py-1" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); font-size: 12px;">
                                                {{ $row->cedula }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope-at me-1"></i>{{ $row->correo }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-phone me-1"></i>{{ $row->telefono }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $row->username }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $row->ID_estudiante }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($this->puedeGestionarEstudiantes())
                                                    <button data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                            class="btn btn-outline-primary"
                                                            wire:click="edit({{ $row->id }})"
                                                            title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                            wire:click="eliminar({{ $row->id }})"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">Solo lectura</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron estudiantes</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($estudiantes->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $estudiantes->firstItem() ?? 0 }} - {{ $estudiantes->lastItem() ?? 0 }}
                                de {{ $estudiantes->total() }} registros
                            </small>
                            <div>
                                {{ $estudiantes->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
