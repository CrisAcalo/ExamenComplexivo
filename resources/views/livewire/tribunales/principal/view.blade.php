{{-- resources/views/livewire/tribunales/principal/view.blade.php --}}
<div>
    @section('title', 'Tribunales para Evaluación') {{-- Título más específico --}}

    <div class="container-fluid p-0">
        <div class="fs-2 fw-semibold mb-4">
            Tribunales Asignados para Evaluación
        </div>

        @include('partials.alerts')

        @if (isset($mensajeNoAutorizado))
            <div class="alert alert-warning">{{ $mensajeNoAutorizado }}</div>
        @else
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="row align-items-center gy-2">
                        <div class="col-md-auto">
                            <h5 class="mb-0"><i class="bi bi-list-task"></i> Todos Mis Tribunales Asignados</h5>
                        </div>
                        <div class="col-md">
                            <input wire:model.debounce.300ms="searchTerm" type="text"
                                class="form-control form-control-sm"
                                placeholder="Buscar por estudiante, carrera, período...">
                        </div>
                        <div class="col-md-auto">
                            <select wire:model="filtroEstado" class="form-select form-select-sm">
                                <option value="PENDIENTES">Calificaciones Pendientes</option>
                                <option value="COMPLETADOS">Calificaciones Completadas</option>
                                <option value="CERRADOS">Tribunales Cerrados</option>
                                <option value="TODOS">Todos Mis Tribunales Asignados</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($tribunalesAsignados->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle-fill"></i>
                            @if ($filtroEstado === 'PENDIENTES')
                                No tiene calificaciones pendientes.
                            @elseif($filtroEstado === 'COMPLETADOS')
                                Aún no ha completado la calificación de ningún tribunal asignado.
                            @elseif($filtroEstado === 'CERRADOS')
                                No tiene tribunales cerrados.
                            @else
                                No tiene tribunales asignados que cumplan con los criterios actuales.
                            @endif
                        </div>
                    @else
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
                                        <th>Estado del Tribunal</th>
                                        <th>Mi Rol Principal en el Tribunal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tribunalesAsignados as $tribunal)
                                        <tr>
                                            <td>{{ $loop->iteration + $tribunalesAsignados->firstItem() - 1 }}</td>
                                            <td>
                                                @if ($tribunal->estudiante)
                                                    {{ $tribunal->estudiante->nombres }}
                                                    {{ $tribunal->estudiante->apellidos }}
                                                    <br><small
                                                        class="text-muted">{{ $tribunal->estudiante->ID_estudiante }}</small>
                                                @else
                                                    <span class="text-danger">Estudiante no asignado</span>
                                                @endif
                                            </td>
                                            <td>{{ $tribunal->carrerasPeriodo?->carrera?->nombre ?? 'N/A' }}</td>
                                            <td>{{ $tribunal->carrerasPeriodo?->periodo?->codigo_periodo ?? 'N/A' }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}
                                                -
                                                {{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}
                                            </td>
                                            <td>
                                                @if ($tribunal->estado === 'CERRADO')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-lock-fill"></i> Cerrado
                                                    </span>
                                                @elseif($tribunal->estado === 'ABIERTO')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-unlock-fill"></i> Abierto
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        {{ $tribunal->estado ?? 'No definido' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($tribunal->tipoAsignacionUsuario)
                                                    <span
                                                        class="badge {{ $tribunal->tipoAsignacionUsuario['badge_class'] }}">
                                                        {{ $tribunal->tipoAsignacionUsuario['descripcion'] }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No Definido</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($tribunal->estado === 'CERRADO')
                                                    {{-- Tribunal cerrado: Solo mostrar botón para exportar acta --}}
                                                    <button wire:click="exportarActaTribunal({{ $tribunal->id }})"
                                                        class="btn btn-sm btn-danger" title="Exportar Acta del Tribunal"
                                                        wire:loading.attr="disabled" wire:target="exportarActaTribunal">
                                                        <span wire:loading wire:target="exportarActaTribunal"
                                                            class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                        <i class="bi bi-file-earmark-pdf-fill" wire:loading.remove
                                                            wire:target="exportarActaTribunal"></i>
                                                        <span wire:loading.remove
                                                            wire:target="exportarActaTribunal">Exportar Acta</span>
                                                    </button>
                                                @else
                                                    {{-- Tribunal abierto: Mostrar botón de calificar --}}
                                                    <a href="{{ route('tribunales.calificar', ['tribunalId' => $tribunal->id]) }}"
                                                        class="btn btn-sm btn-success"
                                                        title="Ingresar/Ver Calificaciones">
                                                        <i class="bi bi-pencil-fill"></i> Calificar
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($tribunalesAsignados->hasPages())
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

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        // Manejar descarga de archivos PDF
        window.addEventListener('downloadFile', event => {
            const filename = event.detail.path;
            const downloadUrl = "{{ route('download.temp.pdf', ':filename') }}".replace(':filename', filename);

            // Crear un enlace temporal para descargar
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endpush
