@section('title', __('Actas Firmadas'))

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
                            <i class="bi bi-file-earmark-check fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            ACTAS FIRMADAS
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Descarga de actas firmadas por los presidentes de tribunales
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="periodoFiltro" class="form-label fw-semibold small">
                        <i class="bi bi-calendar3"></i> Filtrar por Período
                    </label>
                    <select wire:model="periodoSeleccionado" id="periodoFiltro" class="form-select">
                        <option value="">Todos los períodos</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}">{{ $periodo->codigo_periodo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="carreraPeriodoFiltro" class="form-label fw-semibold small">
                        <i class="bi bi-mortarboard"></i> Filtrar por Carrera
                    </label>
                    <select wire:model="carreraPeriodoSeleccionado" id="carreraPeriodoFiltro" class="form-select">
                        <option value="">Todas las carreras</option>
                        @foreach($carrerasPeriodos as $cp)
                            <option value="{{ $cp->id }}">
                                {{ $cp->carrera->nombre }} - {{ $cp->periodo->codigo_periodo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="buscar" class="form-label fw-semibold small">
                        <i class="bi bi-search"></i> Buscar
                    </label>
                    <input wire:model.debounce.500ms="keyWord" type="text" id="buscar"
                           class="form-control" placeholder="Buscar por estudiante o fecha...">
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Actas Firmadas -->
    <div class="card shadow-sm border-0">
        <!-- Header -->
        <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                    <i class="bi bi-file-earmark-check me-2"></i>Actas Firmadas Disponibles
                </h5>
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0 small">Mostrar</label>
                    <select wire:model="perPage" class="form-select form-select-sm w-auto">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th><i class="bi bi-person me-1"></i>Estudiante</th>
                            <th style="width: 120px;"><i class="bi bi-calendar3 me-1"></i>Fecha</th>
                            <th style="width: 130px;"><i class="bi bi-clock me-1"></i>Horario</th>
                            <th><i class="bi bi-mortarboard me-1"></i>Carrera/Período</th>
                            <th><i class="bi bi-people me-1"></i>Presidente</th>
                            <th style="width: 140px;" class="text-center">Subida el</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tribunales as $tribunal)
                            @php
                                $presidente = $tribunal->miembrosTribunales->firstWhere('status', 'PRESIDENTE');
                            @endphp
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
                                    <small>
                                        {{ $tribunal->carrerasPeriodo->carrera->nombre }}<br>
                                        <span class="badge bg-info">{{ $tribunal->carrerasPeriodo->periodo->codigo_periodo }}</span>
                                    </small>
                                </td>
                                <td>
                                    @if($presidente)
                                        <small>
                                            {{ $presidente->user->name }} {{ $presidente->user->lastname }}
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($tribunal->acta_firmada_fecha)->format('d/m/Y') }}<br>
                                        {{ \Carbon\Carbon::parse($tribunal->acta_firmada_fecha)->format('H:i') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-success"
                                                wire:click="descargarActaFirmada({{ $tribunal->id }})"
                                                title="Descargar Acta Firmada">
                                            <i class="bi bi-download"></i> Descargar
                                        </button>
                                        <a href="{{ route('periodos.tribunales.profile', $tribunal->id) }}"
                                           class="btn btn-outline-secondary" title="Ver Tribunal">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    <p class="text-muted mb-0">No se encontraron actas firmadas</p>
                                    <small class="text-muted">
                                        Las actas firmadas aparecerán aquí una vez que los presidentes de tribunales las suban
                                    </small>
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
                        de {{ $tribunales->total() }} actas
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
                <li>Solo se muestran actas de tribunales que ya han sido firmadas y subidas por los presidentes.</li>
                <li>Puede filtrar las actas por período académico o carrera específica.</li>
                <li>Las actas descargadas mantienen el nombre del estudiante para facilitar su identificación.</li>
                <li>Los archivos están almacenados de forma segura en el sistema.</li>
            </ul>
        </div>
    </div>
</div>
