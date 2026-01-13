@can('gestionar periodos')
    <!-- Modal: Crear Nuevo Período -->
    <div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="createDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0"
                     >
                    <div class="w-100">
                        <h5 class="modal-title fw-bold" id="createDataModalLabel">
                            Agregar Nuevo Período
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <form>
                        <!-- Código Período -->
                        <div class="mb-3">
                            <label for="codigo_periodo_create" class="form-label fw-semibold">
                                <i class="bi bi-hash text-primary me-1"></i>Código del Período
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                </span>
                                <input wire:model="codigo_periodo" type="text"
                                       class="form-control border-start-0 @error('codigo_periodo') is-invalid @enderror"
                                       id="codigo_periodo_create"
                                       placeholder="Ej: 2024-S1, 2024-S2">
                            </div>
                            @error('codigo_periodo')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Formato: YYYY-S1 o YYYY-S2</small>
                        </div>

                        <!-- Fecha Inicio -->
                        <div class="mb-3">
                            <label for="fecha_inicio_create" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check text-success me-1"></i>Fecha de Inicio
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar3 text-muted"></i>
                                </span>
                                <input wire:model="fecha_inicio" type="date"
                                       class="form-control border-start-0 @error('fecha_inicio') is-invalid @enderror"
                                       id="fecha_inicio_create">
                            </div>
                            @error('fecha_inicio')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div class="mb-3">
                            <label for="fecha_fin_create" class="form-label fw-semibold">
                                <i class="bi bi-calendar-x text-danger me-1"></i>Fecha de Finalización
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar3 text-muted"></i>
                                </span>
                                <input wire:model="fecha_fin" type="date"
                                       class="form-control border-start-0 @error('fecha_fin') is-invalid @enderror"
                                       id="fecha_fin_create">
                            </div>
                            @error('fecha_fin')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                            wire:click.prevent="cancel()">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" wire:click.prevent="store()"
                            class="btn text-white px-4"
                            style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="bi bi-check-circle me-2"></i>Guardar Período
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Período -->
    <div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0"
                     >
                    <div class="w-100">
                        <h5 class="modal-title fw-bold " id="updateModalLabel">
                            Editar Período
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    @include('partials.alerts')
                    <form>
                        <input type="hidden" wire:model="selected_id">

                        <!-- Código Período -->
                        <div class="mb-3">
                            <label for="codigo_periodo_update" class="form-label fw-semibold">
                                <i class="bi bi-hash text-primary me-1"></i>Código del Período
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                </span>
                                <input wire:model="codigo_periodo" type="text"
                                       class="form-control border-start-0 @error('codigo_periodo') is-invalid @enderror"
                                       id="codigo_periodo_update"
                                       placeholder="Ej: 2024-S1, 2024-S2">
                            </div>
                            @error('codigo_periodo')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Descripción (readonly) -->
                        <div class="mb-3">
                            <label for="descripcion_update" class="form-label fw-semibold">
                                <i class="bi bi-card-text text-info me-1"></i>Descripción
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-file-text text-muted"></i>
                                </span>
                                <input wire:model="descripcion" type="text"
                                       class="form-control border-start-0 bg-light"
                                       id="descripcion_update"
                                       placeholder="Se genera automáticamente"
                                       readonly>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                La descripción se genera automáticamente basada en las fechas
                            </small>
                        </div>

                        <!-- Fecha Inicio -->
                        <div class="mb-3">
                            <label for="fecha_inicio_update" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check text-success me-1"></i>Fecha de Inicio
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar3 text-muted"></i>
                                </span>
                                <input wire:model="fecha_inicio" type="date"
                                       class="form-control border-start-0 @error('fecha_inicio') is-invalid @enderror"
                                       id="fecha_inicio_update">
                            </div>
                            @error('fecha_inicio')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div class="mb-3">
                            <label for="fecha_fin_update" class="form-label fw-semibold">
                                <i class="bi bi-calendar-x text-danger me-1"></i>Fecha de Finalización
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-calendar3 text-muted"></i>
                                </span>
                                <input wire:model="fecha_fin" type="date"
                                       class="form-control border-start-0 @error('fecha_fin') is-invalid @enderror"
                                       id="fecha_fin_update">
                            </div>
                            @error('fecha_fin')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary px-4"
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" wire:click.prevent="update()"
                            class="btn text-white px-4"
                            style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Período
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Período -->
    <div wire:ignore.self class="modal fade" id="deleteDataModal" data-bs-backdrop="static" data-bs-keyboard="false"
         aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            @if ($confirmingPeriodoDeletion)
                <div class="modal-content border-0 shadow-lg">
                    <!-- Header -->
                    <div class="modal-header border-0"
                         style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="w-100 text-center py-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="deleteDataModalLabel">
                                <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                            </h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close" wire:click="resetDeleteConfirmation"
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
                        <h6 class="text-center mb-3">¿Está seguro de eliminar este período?</h6>
                        <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                                <div>
                                    <p class="mb-2">
                                        <strong>Período ID:</strong> {{ $periodoAEliminarId }}
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
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                                wire:click="resetDeleteConfirmation">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                        <button class="btn text-white px-4" wire:click="destroy()"
                                style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-trash me-2"></i>Sí, Eliminar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endcan
