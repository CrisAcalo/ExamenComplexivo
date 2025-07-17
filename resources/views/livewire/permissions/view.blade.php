@section('title', __('Permissions'))
<div class="container-fluid">
    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Listado de permisos </h4>
                        </div>
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search" id="search"
                                placeholder="Buscar permisos">
                        </div>
                        @can('gestionar roles y permisos')
                            <div class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createDataModal">
                                <i class="bi bi-plus-lg"></i> Agregar Permiso
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.permissions.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Name</th>
                                    {{-- <th>Guard Name</th> --}}
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->name }}</td>
                                        {{-- <td>{{ $row->guard_name }}</td> --}}
                                        <td width="90">
                                            @can('gestionar roles y permisos')
                                                <button data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                    class="btn btn-primary btn-sm" wire:click="edit({{ $row->id }})">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                @php
                                                    $criticalPermissions = [
                                                        'gestionar roles y permisos',
                                                        'gestionar usuarios',
                                                        'gestionar configuracion sistema'
                                                    ];
                                                @endphp

                                                @if(!in_array($row->name, $criticalPermissions))
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#deleteDataModal"
                                                        wire:click="eliminar({{ $row->id }})">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                @else
                                                    <small class="text-muted">Crítico</small>
                                                @endif
                                            @else
                                                <span class="text-muted small">Sin permisos</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">No data Found </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="float-end">{{ $permissions->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
