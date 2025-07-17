@section('title', __('Users'))
<div class="container-fluid">
    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Usuarios</h4>
                        </div>
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search" id="search"
                                placeholder="Buscar usuarios">
                        </div>
                        <div class="btn-group">
                            @can('importar profesores')
                                <div class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importProfesoresModal">
                                    <i class="bi bi-file-earmark-excel"></i> Importar Profesores
                                </div>
                            @endcan
                            @can('gestionar usuarios')
                                <div class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#createDataModal">
                                    <i class="bi bi-plus-lg"></i> Añadir Usuario
                                </div>
                            @endcan
                            @if(!auth()->user()->can('gestionar usuarios') && !auth()->user()->can('importar profesores'))
                                <span class="text-muted small">Solo lectura</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center">
                        <label class="me-2 mb-0">Mostrar</label>
                        <select wire:model="perPage" class="form-select form-select-sm w-auto">
                            <option value="5">5</option>
                            <option value="13">13</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-2">filas</span>
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.users.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <td>ACCIONES</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $row)
                                    @php
                                        $roles = $row->getRoleNames();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->email }}</td>
                                        <td>
                                            @foreach ($roles as $rol)
                                                <span class="badge bg-info">{{ $rol }}</span>
                                            @endforeach
                                        </td>
                                        <td width="120">
                                            @can('gestionar usuarios')
                                                <button class="btn btn-primary btn-sm"
                                                    wire:click="edit({{ $row->id }})">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            @endcan
                                            @can('gestionar usuarios')
                                                <button class="btn btn-success btn-sm"
                                                    wire:click="impersonate({{ $row->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            @endcan
                                            @can('gestionar usuarios')
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#deleteDataModal"
                                                    wire:click="eliminar({{ $row->id }})">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            @endcan
                                            @if(!auth()->user()->can('gestionar usuarios'))
                                                <span class="text-muted small">Sin permisos</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">No data Found </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="float-end">{{ $users->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
