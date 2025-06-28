{{-- resources/views/livewire/tribunales/principal/view.blade.php --}}
<div>
    @section('title', 'Mis Evaluaciones Pendientes') {{-- Título más específico --}}

    <div class="container-fluid p-0">
        <div class="fs-2 fw-semibold mb-4">
            Mis Evaluaciones Asignadas
        </div>

        @include('partials.alerts')

        @if (isset($mensajeNoAutorizado))
            <div class="alert alert-warning">{{ $mensajeNoAutorizado }}</div>
        @else
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="row align-items-center gy-2">
                        <div class="col-md-auto">
                            <h5 class="mb-0"><i class="bi bi-list-task"></i> Listado de Tribunales</h5>
                        </div>
                        <div class="col-md">
                            <input wire:model.debounce.300ms="searchTerm" type="text" class="form-control form-control-sm"
                                placeholder="Buscar por estudiante, carrera, período...">
                        </div>
                        <div class="col-md-auto">
                            <select wire:model="filtroEstado" class="form-select form-select-sm">
                                <option value="PENDIENTES">Calificaciones Pendientes</option>
                                <option value="COMPLETADOS">Calificaciones Completadas</option>
                                <option value="TODOS">Todos Mis Tribunales Asignados</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($tribunalesAsignados->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle-fill"></i>
                            @if($filtroEstado === 'PENDIENTES')
                                No tiene calificaciones pendientes.
                            @elseif($filtroEstado === 'COMPLETADOS')
                                Aún no ha completado la calificación de ningún tribunal asignado.
                            @else
                                No tiene tribunales asignados que cumplan con los criterios actuales.
                            @endif
                        </div>
                    @else
                        {{-- ... (Tabla de tribunales como la tenías, sin cambios significativos aquí) ... --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Estudiante</th>
                                        <th>Carrera</th>
                                        <th>Período</th>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th>Mi Rol Principal en Tribunal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tribunalesAsignados as $tribunal)
                                        <tr>
                                            <td>{{ $loop->iteration + $tribunalesAsignados->firstItem() - 1 }}</td>
                                            <td>
                                                @if($tribunal->estudiante)
                                                    {{ $tribunal->estudiante->nombres }} {{ $tribunal->estudiante->apellidos }}
                                                    <br><small class="text-muted">{{ $tribunal->estudiante->ID_estudiante }}</small>
                                                @else
                                                    <span class="text-danger">Estudiante no asignado</span>
                                                @endif
                                            </td>
                                            <td>{{ $tribunal->carrerasPeriodo?->carrera?->nombre ?? 'N/A' }}</td>
                                            <td>{{ $tribunal->carrerasPeriodo?->periodo?->codigo_periodo ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}
                                                -
                                                {{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}
                                            </td>
                                            <td>
                                                @php
                                                    $miembroInfo = $tribunal->miembrosTribunales->first(); // Ya filtrado para el usuario actual
                                                @endphp
                                                @if($miembroInfo)
                                                    <span class="badge bg-primary">{{ Str::title(Str::lower(Str_replace('_', ' ', $miembroInfo->status))) }}</span>
                                                @else
                                                    {{-- Podría ser Director, Apoyo o Calificador General --}}
                                                    @if($tribunal->carrerasPeriodo?->director_id == Auth::id())
                                                        <span class="badge bg-success">Director de Carrera</span>
                                                    @elseif($tribunal->carrerasPeriodo?->docente_apoyo_id == Auth::id())
                                                        <span class="badge bg-info text-dark">Docente de Apoyo</span>
                                                    @elseif(App\Models\CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $tribunal->carrera_periodo_id)->where('user_id', Auth::id())->exists())
                                                        <span class="badge bg-warning text-dark">Calificador General</span>
                                                    @else
                                                         <span class="badge bg-secondary">N/D</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('tribunales.calificar', ['tribunalId' => $tribunal->id]) }}"
                                                    class="btn btn-sm btn-success" title="Ingresar/Ver Calificaciones">
                                                    <i class="bi bi-pencil-fill"></i> Calificar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($tribunalesAsignados->hasPages())
                            <div class="mt-3">
                                {{ $tribunalesAsignados->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
