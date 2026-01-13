@section('title', __('Rubricas'))
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
                            <i class="bi bi-clipboard-check fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            RÚBRICAS DE EVALUACIÓN
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de plantillas y criterios de evaluación
                        </p>
                    </div>
                </div>
                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                    <button class="btn btn-lg text-white" wire:click="create()"
                            style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Rúbrica
                    </button>
                @endif
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header con Buscador -->
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-ul me-2"></i>Listado de Rúbricas
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar rúbricas..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.rubricas.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-card-text me-1"></i>Nombre de la Rúbrica
                                    </th>
                                    <th style="width: 220px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rubricas as $row)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold" style="color: #333;">
                                                {{ $row->nombre }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                {{-- BOTÓN PREVISUALIZAR --}}
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                    data-bs-placement="left" data-bs-html="true"
                                                    title="Previsualizar: {{ $row->nombre }}"
                                                    data-bs-content="{{ $this->generarHtmlPrevisualizacion($row->id) }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                {{-- BOTÓN COPIAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <button type="button" class="btn btn-outline-info"
                                                        wire:click="confirmCopy({{ $row->id }})"
                                                        title="Copiar Rúbrica">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                @endif

                                                {{-- BOTÓN EDITAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <a href="{{ route('rubricas.edit', $row->id) }}" class="btn btn-outline-primary"
                                                        title="Editar Rúbrica">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endif

                                                {{-- BOTÓN ELIMINAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <button type="button" class="btn btn-outline-danger"
                                                        wire:click="confirmDelete({{ $row->id }})"
                                                        title="Eliminar Rúbrica">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">Solo lectura</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No se encontraron rúbricas</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con Paginación -->
                @if($rubricas->hasPages())
                    <div class="card-footer border-0 py-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando {{ $rubricas->firstItem() ?? 0 }} - {{ $rubricas->lastItem() ?? 0 }}
                                de {{ $rubricas->total() }} registros
                            </small>
                            <div>
                                {{ $rubricas->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
        <!-- Modal: Eliminar Rúbrica -->
        <div wire:ignore.self class="modal fade" id="eliminarRubricaModal" data-bs-backdrop="static" data-bs-keyboard="false"
             aria-labelledby="eliminarRubricaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <!-- Header -->
                    <div class="modal-header border-0"
                         style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="w-100 text-center py-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="eliminarRubricaModalLabel">
                                <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                            </h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"
                                style="position: absolute; right: 20px; top: 20px;"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px; background: rgba(231, 76, 60, 0.1);">
                                <i class="bi bi-trash3 text-danger" style="font-size: 40px;"></i>
                            </div>
                        </div>
                        <h6 class="text-center mb-3">¿Está seguro de eliminar esta rúbrica?</h6>
                        <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                                <div>
                                    <p class="mb-2">
                                        <strong>Nombre:</strong> {{ $rubricaSeleccionada->nombre ?? '' }}
                                    </p>
                                    <p class="mb-0 fw-bold text-danger">
                                        Esta acción es irreversible. Los datos no se podrán recuperar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                        <button class="btn text-white px-4" wire:click="destroy"
                                style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-trash me-2"></i>Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Copiar Rúbrica -->
        <div wire:ignore.self class="modal fade" id="copiarRubricaModal" data-bs-backdrop="static"
             aria-labelledby="copiarRubricaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <!-- Header -->
                    <div class="modal-header border-0 pb-0">
                        <div class="w-100">
                            <h5 class="modal-title fw-bold" id="copiarRubricaModalLabel">
                                <i class="bi bi-copy me-2 text-info"></i>Copiar Rúbrica
                            </h5>
                            <hr>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"
                                style="position: absolute; right: 20px; top: 20px;"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0" style="background-color: rgba(13, 202, 240, 0.1);">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-info me-2 mt-1"></i>
                                <div>
                                    <p class="mb-2">
                                        <strong>Rúbrica a copiar:</strong> {{ $rubricaSeleccionada->nombre ?? '' }}
                                    </p>
                                    <p class="mb-0">
                                        Se creará una copia con el nombre <strong>"Copia de {{ $rubricaSeleccionada->nombre ?? '' }}"</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                        <button type="button" class="btn text-white px-4" wire:click="copyRubrica"
                                style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                            <i class="bi bi-copy me-2"></i>Copiar Rúbrica
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
