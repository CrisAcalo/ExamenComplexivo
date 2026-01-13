@section('title', __('Roles'))
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
                            <i class="bi bi-shield-check fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            GESTIÓN DE ROLES
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Administración de roles y asignación de permisos
                        </p>
                    </div>
                </div>
                @can('gestionar roles y permisos')
                    <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#createDataModal"
                            style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Rol
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
                                <i class="bi bi-list-ul me-2"></i>Listado de Roles
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por nombre de rol..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.roles.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-tag me-1"></i>Nombre del Rol
                                    </th>
                                    <th>
                                        <i class="bi bi-key me-1"></i>Permisos Asignados
                                    </th>
                                    <th style="width: 180px;" class="text-center">
                                        <i class="bi bi-gear me-1"></i>Gestión
                                    </th>
                                    <th style="width: 150px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                                @forelse($roles as $row)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">
                                                @if($row->name === 'Super Admin')
                                                    <i class="bi bi-star-fill text-warning me-2"></i>
                                                @elseif(in_array($row->name, ['Director de Carrera', 'Docente de Apoyo']))
                                                    <i class="bi bi-briefcase-fill text-primary me-2"></i>
                                                @elseif($row->name === 'Docente')
                                                    <i class="bi bi-person-video3 text-success me-2"></i>
                                                @else
                                                    <i class="bi bi-person-badge me-2"></i>
                                                @endif
                                                {{ $row->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($row->permissions as $permiso)
                                                    <span class="badge bg-success" style="font-size: 0.75rem;">
                                                        <i class="bi bi-check-circle me-1"></i>{{ $permiso->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">
                                                        <i class="bi bi-dash-circle me-1"></i>Sin permisos asignados
                                                    </span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @can('gestionar roles y permisos')
                                                <button type="button" class="btn btn-sm px-3"
                                                        style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updatePermisionsModal"
                                                        wire:click="permisosBusqueda({{ $row->id }})"
                                                        title="Asignar permisos">
                                                    <i class="bi bi-key me-1"></i>Asignar Permisos
                                                </button>
                                            @else
                                                <span class="badge bg-secondary">Solo lectura</span>
                                            @endcan
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('gestionar roles y permisos')
                                                    <button data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                            class="btn btn-outline-primary"
                                                            wire:click="edit({{ $row->id }})"
                                                            title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    @if(!in_array($row->name, ['Super Admin']))
                                                        <button type="button" class="btn btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteDataModal"
                                                                wire:click="eliminar({{ $row->id }})"
                                                                title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-outline-secondary" disabled title="Rol protegido">
                                                            <i class="bi bi-shield-lock"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Solo lectura</span>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div wire:ignore.self class="modal fade deleteModal"
                                        id="deleteModal{{ $row->id }}" data-bs-backdrop="static"
                                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-light">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">¿Está seguro
                                                        de eliminar este rol? {{ $row->name }}?
                                                    </h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-danger fw-bold">
                                                        Los datos no se podrán recuperar.

                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cerrar</button>
                                                    <button class="btn btn-danger"
                                                        wire:click="destroy({{ $row->id }})">Eliminar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron roles</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($roles->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $roles->firstItem() ?? 0 }} - {{ $roles->lastItem() ?? 0 }}
                                de {{ $roles->total() }} registros
                            </small>
                            <div>
                                {{ $roles->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
