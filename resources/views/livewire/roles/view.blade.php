@section('title', __('Roles'))
<div class="container-fluid">
    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Listado de Roles </h4>
                        </div>
                        @if (session()->has('message'))
                            <div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;">
                                {{ session('message') }} </div>
                        @endif
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Buscar Roles">
                        </div>
                        <div class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createDataModal">
                            <i class="bi bi-plus-lg"></i> Add Roles
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.roles.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Name</th>
                                    {{-- <th>Guard Name</th> --}}
                                    <td>Permisos asignados</td>
                                    <td>Asignar permisos</td>
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>

                                @forelse($roles as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>
                                            @foreach ($row->permissions as $permiso)
                                                <span class="badge bg-warning-subtle text-body-secondary">{{ $permiso->name }}</span>
                                            @endforeach

                                        </td>
                                        <td width="150">
                                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                                data-bs-target="#updatePermisionsModal"
                                                wire:click="permisosBusqueda({{ $row->id }})">
                                                Asignar Permisos
                                            </button>
                                        </td>
                                        <td width="90">

                                            <a data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                class="btn btn-sm btn-primary" wire:click="edit({{ $row->id }})">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteDataModal"
                                                wire:click="eliminar({{ $row->id }})">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
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
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-danger"
                                                        wire:click="destroy({{ $row->id }})">Eliminar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">No data Found </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="float-end">{{ $roles->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
