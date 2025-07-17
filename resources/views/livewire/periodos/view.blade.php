@section('title', __('Periodos'))
<div class="container-fluid p-0">
    @include('partials.alerts')
    <div class="row justify-content-center p-0">
        <div class="fs-2 fw-semibold mb-4">
            Períodos
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h4><i class="fab fa-laravel text-info"></i>
                                Lista de Períodos </h4>
                        </div>
                        @if (session()->has('message'))
                            <div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;">
                                {{ session('message') }} </div>
                        @endif
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Buscar Períodos">
                        </div>
                        @can('gestionar periodos')
                            <div class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#createDataModal">
                                <i class="fa fa-plus"></i> Add Periodos
                            </div>
                        @else
                            <span class="text-muted">Solo lectura</span>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.periodos.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Codigo Periodo</th>
                                    <th>Descripción</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodos as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->codigo_periodo }}</td>
                                        <td>{{ $row->descripcion }}</td>
                                        <td>{{ $row->fecha_inicio }}</td>
                                        <td>{{ $row->fecha_fin }}</td>
                                        <td width="200">
                                            <a class="btn btn-sm btn-info" wire:click="open({{ $row->id }})">
                                                <i class="fa fa-edit"></i> Ver
                                            </a>
                                            @can('gestionar periodos')
                                                <a data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                    class="btn btn-sm btn-primary" wire:click="edit({{ $row->id }})">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <a class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteDataModal"
                                                    wire:click="eliminar({{ $row->id }})">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
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
                        <div class="float-end">{{ $periodos->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
