@section('title', __('Mis Actas Firmadas'))

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
                            <i class="bi bi-file-earmark-pdf fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            MIS ACTAS FIRMADAS
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de actas firmadas de tribunales donde soy presidente
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Listado de Tribunales -->
    <div class="card shadow-sm border-0">
        <!-- Header con Buscador -->
        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Tribunales Cerrados
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input wire:model.debounce.500ms="keyWord" type="text"
                               class="form-control border-start-0"
                               placeholder="Buscar por estudiante o fecha..."
                               style="box-shadow: none;">
                    </div>
                </div>
            </div>

            <!-- Control de paginación -->
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0 small">Mostrar</label>
                    <select wire:model="perPage" class="form-select form-select-sm w-auto me-2">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span class="small">registros</span>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
            @include('livewire.actas-firmadas.modals')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th><i class="bi bi-person me-1"></i>Estudiante</th>
                            <th style="width: 120px;"><i class="bi bi-calendar3 me-1"></i>Fecha</th>
                            <th style="width: 130px;"><i class="bi bi-clock me-1"></i>Horario</th>
                            <th><i class="bi bi-mortarboard me-1"></i>Carrera</th>
                            <th style="width: 120px;" class="text-center">Estado Acta</th>
                            <th style="width: 180px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tribunales as $tribunal)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold" style="color: #333;">
                                        {{ $tribunal->estudiante->apellidos }}, {{ $tribunal->estudiante->nombres }}
                                    </div>
                                    <small class="text-muted">{{ $tribunal->estudiante->ID_estudiante }}</small>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($tribunal->fecha)->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ \Carbon\Carbon::parse($tribunal->hora_inicio)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($tribunal->hora_fin)->format('H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $tribunal->carrerasPeriodo->carrera->nombre }}<br>
                                        <span class="badge bg-info">{{ $tribunal->carrerasPeriodo->periodo->codigo_periodo }}</span>
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($tribunal->acta_firmada_path)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill"></i> Subida
                                        </span>
                                        <br>
                                        <small class="text-muted" style="font-size: 10px;">
                                            {{ \Carbon\Carbon::parse($tribunal->acta_firmada_fecha)->format('d/m/Y H:i') }}
                                        </small>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if($tribunal->acta_firmada_path)
                                            <button type="button" class="btn btn-outline-success"
                                                    wire:click="descargarActaFirmada({{ $tribunal->id }})"
                                                    title="Descargar Acta Firmada">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary"
                                                wire:click="abrirModalSubir({{ $tribunal->id }})"
                                                title="{{ $tribunal->acta_firmada_path ? 'Reemplazar Acta Firmada' : 'Subir Acta Firmada' }}">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                        <a href="{{ route('periodos.tribunales.profile', $tribunal->id) }}"
                                           class="btn btn-outline-info btn-sm"
                                           title="Ver perfil del tribunal para exportar acta">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    <p class="text-muted mb-0">No se encontraron tribunales cerrados donde seas presidente</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer con Paginación -->
        @if($tribunales->hasPages())
            <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $tribunales->firstItem() ?? 0 }} - {{ $tribunales->lastItem() ?? 0 }}
                        de {{ $tribunales->total() }} tribunales
                    </small>
                    <div>
                        {{ $tribunales->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Información Adicional -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: #2d7a5f;">
                <i class="bi bi-info-circle me-2"></i>Información
            </h5>
            <ul class="mb-0">
                <li>Solo se muestran tribunales donde usted es presidente y están en estado <strong>CERRADO</strong>.</li>
                <li>El botón <i class="bi bi-file-pdf"></i> le llevará al perfil del tribunal donde puede <strong>exportar el acta PDF completa</strong> con todas las calificaciones antes de firmarla.</li>
                <li>El archivo que suba debe estar en formato <strong>PDF</strong> y no debe superar los <strong>10MB</strong>.</li>
                <li>Puede reemplazar un acta subida anteriormente subiéndola nuevamente.</li>
                <li>El archivo se almacena de forma segura en el sistema.</li>
            </ul>
        </div>
    </div>
</div>
