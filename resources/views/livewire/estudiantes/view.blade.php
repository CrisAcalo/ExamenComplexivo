@section('title', __('Estudiantes'))
<div class="container-fluid">
    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Estudiante Listing </h4>
                        </div>
                        @if (session()->has('message'))
                            <div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;">
                                {{ session('message') }} </div>
                        @endif
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Search Estudiantes">
                        </div>
                        <div class="btn-group">
                            <div class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="bi bi-file-earmark-excel"></i> Importar Estudiantes
                            </div>
                            <div class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#createDataModal">
                                <i class="bi bi-plus-lg"></i> Añadir Estudiante
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center">
                        <label class="me-2 mb-0">Mostrar</label>
                        <select wire:model="perPage" class="form-select form-select-sm w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-2">filas</span>
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.estudiantes.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Cédula</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Username</th>
                                    <th>Id Estudiante</th>
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estudiantes as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->nombres }}</td>
                                        <td>{{ $row->apellidos }}</td>
                                        <td>{{ $row->cedula }}</td>
                                        <td>{{ $row->correo }}</td>
                                        <td>{{ $row->telefono }}</td>
                                        <td>{{ $row->username }}</td>
                                        <td>{{ $row->ID_estudiante }}</td>
                                        <td width="90">
                                            <div class="dropdown">
                                                <a data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                    class="btn btn-sm btn-primary"
                                                    wire:click="edit({{ $row->id }})"><i class="fa fa-edit"></i>
                                                    Edit </a>
                                                <a class="btn btn-sm btn-danger"
                                                    wire:click="eliminar({{ $row->id }})">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">No data Found </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="float-end">{{ $estudiantes->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
