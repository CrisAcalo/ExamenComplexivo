@section('title', __('Permissions'))
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
                            <i class="bi bi-key fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            GESTIÓN DE PERMISOS
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Administración de permisos y privilegios del sistema
                        </p>
                    </div>
                </div>
                @can('gestionar roles y permisos')
                    <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#createDataModal"
                            style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Permiso
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
                                <i class="bi bi-list-ul me-2"></i>Listado de Permisos
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por nombre de permiso..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.permissions.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-key me-1"></i>Nombre del Permiso
                                    </th>
                                    <th style="width: 150px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $row)
                                    @php
                                        $criticalPermissions = [
                                            'gestionar roles y permisos',
                                            'gestionar usuarios',
                                            'gestionar configuracion sistema'
                                        ];
                                        $isCritical = in_array($row->name, $criticalPermissions);
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">
                                                <i class="bi bi-unlock-fill text-success me-2"></i>
                                                {{ $row->name }}
                                            </span>
                                            @if($isCritical)
                                                <span class="badge bg-danger ms-2" style="font-size: 0.7rem;">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>CRÍTICO
                                                </span>
                                            @endif
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

                                                    @if(!$isCritical)
                                                        <button type="button" class="btn btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteDataModal"
                                                                wire:click="eliminar({{ $row->id }})"
                                                                title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-outline-secondary" disabled title="Permiso crítico - No se puede eliminar">
                                                            <i class="bi bi-shield-lock"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Solo lectura</span>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron permisos</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($permissions->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $permissions->firstItem() ?? 0 }} - {{ $permissions->lastItem() ?? 0 }}
                                de {{ $permissions->total() }} registros
                            </small>
                            <div>
                                {{ $permissions->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
