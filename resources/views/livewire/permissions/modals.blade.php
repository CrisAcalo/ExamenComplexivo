<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="createDataModalLabel">
                        Crear Nuevo Permiso
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
                    <!-- Nombre del Permiso -->
                    <div class="mb-3">
                        <label for="name_create" class="form-label fw-semibold">
                            <i class="bi bi-key text-success me-1"></i>Nombre del Permiso
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-unlock-fill text-muted"></i>
                            </span>
                            <input wire:model="name" type="text" 
                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                   id="name_create" 
                                   placeholder="Ej: gestionar usuarios, ver reportes">
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-info-circle me-1"></i>Use nombres descriptivos en minúsculas separados por espacios
                        </small>
                        @error('name')
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
                    <i class="bi bi-check-circle me-2"></i>Guardar Permiso
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="updateModalLabel">
                        Actualizar Permiso
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
                    <input type="hidden" wire:model="selected_id">
                    
                    <!-- Nombre del Permiso -->
                    <div class="mb-3">
                        <label for="name_update" class="form-label fw-semibold">
                            <i class="bi bi-key text-success me-1"></i>Nombre del Permiso
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-unlock-fill text-muted"></i>
                            </span>
                            <input wire:model="name" type="text" 
                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                   id="name_update" 
                                   placeholder="Nombre del permiso">
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-info-circle me-1"></i>Use nombres descriptivos en minúsculas separados por espacios
                        </small>
                        @error('name')
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
                    <i class="bi bi-check-circle me-2"></i>Actualizar Permiso
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            @if ($rolEncontrado)
                <!-- Header -->
                <div class="modal-header border-0"
                     style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <div class="w-100 text-center py-2">
                        <h5 class="modal-title fw-bold text-white mb-0" id="staticBackdropLabel">
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
                    <h6 class="text-center mb-3">¿Está seguro de eliminar el permiso "{{ $rolEncontrado->name }}"?</h6>
                    <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                            <div>
                                <p class="mb-2">
                                    <strong>Esta acción eliminará permanentemente el permiso seleccionado.</strong>
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
                    <button class="btn text-white px-4" wire:click="destroy({{ $rolEncontrado->id }})"
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="bi bi-trash me-2"></i>Sí, Eliminar
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
