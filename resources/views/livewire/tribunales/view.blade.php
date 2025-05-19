@section('title', __('Tribunales'))
<div class="container-fluid p-0">
    <div class="fs-2 fw-semibold mb-4">
        <a href="{{ route('periodos.') }}">Períodos</a> /
        <a href="{{ route('periodos.profile', $periodo->id) }}">{{ $periodo->codigo_periodo }}</a> /
        {{ $carrera->nombre }}
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h3><i class="fab fa-laravel text-info"></i>
                                Listado de Tribunales</h3>
                        </div>
                        @if (session()->has('message'))
                            <div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;">
                                {{ session('message') }} </div>
                        @endif
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Search Tribunales">
                        </div>
                        <div class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#createDataModal">
                            <i class="fa fa-plus"></i> Add Tribunales
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.tribunales.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <td>#</td>
                                    <th>Estudiante</th>
                                    <th>Fecha</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Miembros</th>
                                    <td>ACTIONS</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tribunales as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->estudiante->nombres }} {{ $row->estudiante->apellidos }}</td>
                                        <td>{{ $row->fecha }}</td>
                                        <td>{{ $row->hora_inicio }}</td>
                                        <td>{{ $row->hora_fin }}</td>
                                        <td>
                                            @foreach ($row->miembrosTribunales as $miembro)
                                                <span class="badge bg-primary mb-1"
                                                    style="display: block;width:max-content">{{ $miembro->status }} -
                                                    {{ $miembro->user->name }}</span>
                                            @endforeach
                                        </td>
                                        <td width="90">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-secondary dropdown-toggle" href="#"
                                                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li><a data-bs-toggle="modal" data-bs-target="#updateDataModal"
                                                            class="dropdown-item"
                                                            wire:click="edit({{ $row->id }})"><i
                                                                class="fa fa-edit"></i> Edit </a></li>
                                                    <li><a class="dropdown-item"
                                                            onclick="confirm('Confirm Delete Tribunale id {{ $row->id }}? \nDeleted Tribunales cannot be recovered!')||event.stopImmediatePropagation()"
                                                            wire:click="destroy({{ $row->id }})"><i
                                                                class="fa fa-trash"></i> Delete </a></li>
                                                </ul>
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
                        <div class="float-end">{{ $tribunales->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
