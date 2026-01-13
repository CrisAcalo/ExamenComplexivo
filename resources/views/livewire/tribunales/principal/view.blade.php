{{-- resources/views/livewire/tribunales/principal/view.blade.php --}}
@section('title', 'Tribunales para Evaluación')
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
                            <i class="bi bi-people-fill fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            MIS EVALUACIONES DE TRIBUNALES
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de calificaciones y evaluaciones asignadas
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

        @if (isset($mensajeNoAutorizado))
            <div class="alert alert-warning">{{ $mensajeNoAutorizado }}</div>
        @else
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center gy-3">
                        <div class="col-md-12 mb-2">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-task me-2"></i>Todos Mis Tribunales Asignados
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.debounce.300ms="searchTerm" type="text"
                                    class="form-control border-start-0"
                                    placeholder="Buscar por estudiante, carrera, período..."
                                    style="box-shadow: none;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 fw-semibold text-muted" style="white-space: nowrap;">
                                    <i class="bi bi-funnel me-1"></i>Filtrar:
                                </label>
                                <select wire:model="filtroEstado" class="form-select">
                                    <option value="PENDIENTES">Calificaciones Pendientes</option>
                                    <option value="COMPLETADOS">Calificaciones Completadas</option>
                                    <option value="CERRADOS">Tribunales Cerrados</option>
                                    <option value="TODOS">Todos Mis Tribunales Asignados</option>
                                </select>
                            </div>
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
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <tr>
                                        <th style="width: 50px;" class="text-center fw-semibold">#</th>
                                        <th class="fw-semibold"><i class="bi bi-person me-1"></i>Estudiante</th>
                                        <th class="fw-semibold"><i class="bi bi-building me-1"></i>Carrera</th>
                                        <th class="fw-semibold"><i class="bi bi-calendar-event me-1"></i>Período</th>
                                        <th class="fw-semibold"><i class="bi bi-calendar-date me-1"></i>Fecha</th>
                                        <th class="fw-semibold"><i class="bi bi-clock me-1"></i>Horario</th>
                                        <th class="fw-semibold"><i class="bi bi-flag me-1"></i>Estado</th>
                                        <th class="fw-semibold"><i class="bi bi-person-badge me-1"></i>Mi Rol</th>
                                        <th class="text-center fw-semibold" style="width: 150px;"><i class="bi bi-gear me-1"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tribunalesAsignados as $tribunal)
                                        <tr style="border-bottom: 1px solid #f0f0f0;">
                                            <td class="text-center text-muted">{{ $loop->iteration + $tribunalesAsignados->firstItem() - 1 }}</td>
                                            <td>
                                                @if ($tribunal->estudiante)
                                                    <div class="fw-semibold" style="color: #2d7a5f;">
                                                        {{ $tribunal->estudiante->nombres }} {{ $tribunal->estudiante->apellidos }}
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-credit-card-2-front me-1"></i>{{ $tribunal->estudiante->ID_estudiante }}
                                                    </small>
                                                @else
                                                    <span class="text-danger">Estudiante no asignado</span>
                                                @endif
                                            </td>
                                            <td>{{ $tribunal->carrerasPeriodo?->carrera?->nombre ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); font-size: 13px;">
                                                    {{ $tribunal->carrerasPeriodo?->periodo?->codigo_periodo ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if ($tribunal->estado === 'CERRADO')
                                                    <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); font-size: 13px;">
                                                        <i class="bi bi-lock-fill me-1"></i>Cerrado
                                                    </span>
                                                @elseif($tribunal->estado === 'ABIERTO')
                                                    <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 13px;">
                                                        <i class="bi bi-unlock-fill me-1"></i>Abierto
                                                    </span>
                                                @else
                                                    <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); font-size: 13px;">
                                                        {{ $tribunal->estado ?? 'No definido' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($tribunal->tipoAsignacionUsuario)
                                                    <span class="badge px-3 py-2 {{ $tribunal->tipoAsignacionUsuario['badge_class'] }}" style="font-size: 13px;">
                                                        {{ $tribunal->tipoAsignacionUsuario['descripcion'] }}
                                                    </span>
                                                @else
                                                    <span class="badge px-3 py-2 bg-secondary" style="font-size: 13px;">No Definido</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($tribunal->estado === 'CERRADO')
                                                    <button wire:click="exportarActaTribunal({{ $tribunal->id }})"
                                                        class="btn btn-sm text-white" title="Exportar Acta del Tribunal"
                                                        wire:loading.attr="disabled" wire:target="exportarActaTribunal"
                                                        style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; transition: all 0.3s ease;"
                                                        onmouseover="this.style.transform='scale(1.05)'"
                                                        onmouseout="this.style.transform='scale(1)'">
                                                        <span wire:loading wire:target="exportarActaTribunal"
                                                            class="spinner-border spinner-border-sm me-1" role="status"
                                                            aria-hidden="true"></span>
                                                        <i class="bi bi-file-earmark-pdf-fill me-1" wire:loading.remove
                                                            wire:target="exportarActaTribunal"></i>
                                                        <span wire:loading.remove wire:target="exportarActaTribunal">Exportar Acta</span>
                                                    </button>
                                                @else
                                                    <a href="{{ route('tribunales.calificar', ['tribunalId' => $tribunal->id]) }}"
                                                        class="btn btn-sm text-white"
                                                        title="Ingresar/Ver Calificaciones"
                                                        style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); border: none; transition: all 0.3s ease;"
                                                        onmouseover="this.style.transform='scale(1.05)'"
                                                        onmouseout="this.style.transform='scale(1)'">
                                                        <i class="bi bi-pencil-fill me-1"></i>Calificar
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
