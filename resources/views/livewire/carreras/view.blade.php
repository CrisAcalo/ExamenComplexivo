@section('title', __('Carreras'))
<div class="container-fluid">
    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Listado de Carreras </h4>
                        </div>
                        @if (session()->has('message'))
                            <div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;">
                                {{ session('message') }} </div>
                        @endif
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Buscar Carreras">
                        </div>
                        @can('gestionar carreras')
                            <div class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#createDataModal">
                                <i class="fa fa-plus"></i> Añadir Carreras
                            </div>
                        @else
                            <span class="text-muted small">Solo lectura</span>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.carreras.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Codigo Carrera</th>
                                    <th>Nombre</th>
                                    <th>Departamento</th>
                                    <th>Modalidad</th>
                                    <th>Sede</th>
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($carreras as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->codigo_carrera }}</td>
                                        <td>{{ $row->nombre }}</td>
                                        <td>{{ $row->departamento ? $row->departamento->nombre : '' }}</td>
                                        <td>{{ $row->modalidad }}</td>
                                        <td>{{ $row->sede }}</td>
                                        <td width="90">
                                            <div class="dropdown">
                                                @can('gestionar carreras')
                                                    <a data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                        class="btn btn-sm btn-primary"
                                                        wire:click="edit({{ $row->id }})"><i class="fa fa-edit"></i>
                                                        Edit </a>
                                                    <a class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteDataModal"
                                                        wire:click="eliminar({{ $row->id }})">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">Sin permisos</span>
                                                @endcan
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
                        <div class="float-end">{{ $carreras->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
