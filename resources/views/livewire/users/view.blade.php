@section('title', __('Docentes'))
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
                            <i class="bi bi-person-video3 fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            GESTIÓN DE DOCENTES
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Administración de profesores, roles y asignaciones
                        </p>
                    </div>
                </div>
                <div class="btn-group">
                    @php
                        $user = auth()->user();
                        // Verificar si puede importar: Super Admin con permiso O Director/Apoyo contextual
                        $puedeImportar = (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && $user->can('importar profesores'))
                                        || \App\Helpers\ContextualAuth::hasActiveAssignments($user);
                        // Verificar si puede gestionar: Super Admin con permiso O Director/Apoyo contextual
                        $puedeGestionar = (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && $user->can('gestionar usuarios'))
                                         || \App\Helpers\ContextualAuth::hasActiveAssignments($user);
                    @endphp
                    @if($puedeImportar)
                        <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#importProfesoresModal"
                                style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='transparent'">
                            <i class="bi bi-file-earmark-excel me-2"></i>Importar Profesores
                        </button>
                    @endif
                    @if($puedeGestionar)
                        <button class="btn btn-lg text-white" data-bs-toggle="modal" data-bs-target="#createDataModal"
                                style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='transparent'">
                            <i class="bi bi-plus-circle me-2"></i>Añadir Docente
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header con Buscador y Filtros -->
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-ul me-2"></i>Listado de Docentes
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por nombre, correo, rol..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 fw-semibold small">Mostrar:</label>
                                <select wire:model="perPage" class="form-select form-select-sm w-auto">
                                    <option value="5">5</option>
                                    <option value="13">13</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ms-2 small text-muted">filas</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 fw-semibold small">
                                    <i class="bi bi-building me-1"></i>Departamento:
                                </label>
                                <select wire:model="departamento_filter" class="form-select form-select-sm" style="max-width: 300px;">
                                    <option value="">Todos los departamentos</option>
                                    @foreach($departamentosDisponibles as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.users.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-person me-1"></i>Nombre
                                    </th>
                                    <th>
                                        <i class="bi bi-envelope me-1"></i>Correo
                                    </th>
                                    <th style="width: 180px;">
                                        <i class="bi bi-shield-check me-1"></i>Rol Global
                                    </th>
                                    <th>
                                        <i class="bi bi-diagram-3 me-1"></i>Asignaciones Contextuales
                                    </th>
                                    <th style="width: 150px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $row)
                                    @php
                                        $roles = $row->getRoleNames();

                                        // Recolectar asignaciones contextuales
                                        $asignaciones = [];

                                        // Director de carreras
                                        foreach($row->carrerasComoDirector as $cp) {
                                            $asignaciones[] = [
                                                'tipo' => 'Director',
                                                'carrera' => $cp->carrera->nombre ?? 'N/A',
                                                'periodo' => $cp->periodo->codigo_periodo ?? 'N/A',
                                                'badge_class' => 'bg-primary'
                                            ];
                                        }

                                        // Docente de apoyo
                                        foreach($row->carrerasComoApoyo as $cp) {
                                            $asignaciones[] = [
                                                'tipo' => 'Docente Apoyo',
                                                'carrera' => $cp->carrera->nombre ?? 'N/A',
                                                'periodo' => $cp->periodo->codigo_periodo ?? 'N/A',
                                                'badge_class' => 'bg-success'
                                            ];
                                        }

                                        // Calificador general
                                        foreach($row->asignacionesCalificadorGeneral as $cg) {
                                            $asignaciones[] = [
                                                'tipo' => 'Calificador General',
                                                'carrera' => $cg->carreraPeriodo->carrera->nombre ?? 'N/A',
                                                'periodo' => $cg->carreraPeriodo->periodo->codigo_periodo ?? 'N/A',
                                                'badge_class' => 'bg-warning text-dark'
                                            ];
                                        }

                                        // Miembros de tribunales (agrupar por carrera/periodo únicos)
                                        $tribunalesAgrupados = [];
                                        foreach($row->miembrosTribunales as $mt) {
                                            if($mt->tribunal && $mt->tribunal->carrerasPeriodo) {
                                                $cp = $mt->tribunal->carrerasPeriodo;
                                                $key = $cp->id;
                                                if(!isset($tribunalesAgrupados[$key])) {
                                                    $tribunalesAgrupados[$key] = [
                                                        'carrera' => $cp->carrera->nombre ?? 'N/A',
                                                        'periodo' => $cp->periodo->codigo_periodo ?? 'N/A',
                                                        'count' => 0
                                                    ];
                                                }
                                                $tribunalesAgrupados[$key]['count']++;
                                            }
                                        }

                                        foreach($tribunalesAgrupados as $tb) {
                                            $asignaciones[] = [
                                                'tipo' => 'Miembro Tribunal',
                                                'carrera' => $tb['carrera'],
                                                'periodo' => $tb['periodo'],
                                                'count' => $tb['count'],
                                                'badge_class' => 'bg-secondary'
                                            ];
                                        }
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $row->name }} {{ $row->lastname }}</td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope-at me-1"></i>{{ $row->email }}
                                            </small>
                                        </td>
                                        <td>
                                            @foreach ($roles as $rol)
                                                <span class="badge px-2 py-1" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); font-size: 12px;">
                                                    {{ $rol }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if(count($asignaciones) > 0)
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($asignaciones as $asig)
                                                        <small>
                                                            <span class="badge {{ $asig['badge_class'] }} me-1">{{ $asig['tipo'] }}</span>
                                                            <span class="text-muted" style="font-size: 0.85rem;">
                                                                <i class="bi bi-mortarboard me-1"></i>{{ $asig['carrera'] }}
                                                                <span class="badge bg-light text-dark ms-1">{{ $asig['periodo'] }}</span>
                                                            </span>
                                                            @if(isset($asig['count']))
                                                                <span class="badge bg-dark ms-1">x{{ $asig['count'] }}</span>
                                                            @endif
                                                        </small>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">
                                                    <i class="bi bi-dash-circle me-1"></i>Sin asignaciones
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @php
                                                    // Solo Super Admin puede editar/eliminar
                                                    $esSuperAdmin = \App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user)
                                                                    && $user->can('gestionar usuarios');
                                                @endphp
                                                @if($esSuperAdmin)
                                                    <button class="btn btn-outline-primary"
                                                            wire:click="edit({{ $row->id }})"
                                                            title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteDataModal"
                                                            wire:click="eliminar({{ $row->id }})"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-eye"></i> Solo lectura
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron docentes</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($users->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }}
                                de {{ $users->total() }} registros
                            </small>
                            <div>
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
