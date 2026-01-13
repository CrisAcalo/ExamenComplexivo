{{-- Modal Subir Plantilla --}}
<div wire:ignore.self class="modal fade" id="uploadModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="uploadModalLabel">
                        <i class="bi bi-cloud-upload me-2"></i>Subir Plantilla Word
                    </h5>
                    <hr>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="position: absolute; right: 20px; top: 20px;"></button>
            </div>

            <div class="modal-body p-4">
                <form wire:submit.prevent="subirPlantilla">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la Plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                               wire:model="nombre" placeholder="Ej: Acta Tribunal 2025">
                        @error('nombre')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción (Opcional)</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                  wire:model="descripcion" rows="2"></textarea>
                        @error('descripcion')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-word text-primary me-1"></i>Archivo Word
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control @error('archivoWord') is-invalid @enderror"
                               wire:model="archivoWord" accept=".doc,.docx" id="archivoWord">
                        @error('archivoWord')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div wire:loading wire:target="archivoWord" class="alert alert-secondary">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Cargando archivo...
                    </div>

                    @if ($archivoWord && !$errors->has('archivoWord'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Archivo cargado: <strong>{{ $archivoWord->getClientOriginalName() }}</strong>
                            ({{ number_format($archivoWord->getSize() / 1024, 2) }} KB)
                        </div>
                    @endif

                    <div class="d-flex justify-content-end">
                        <button type="submit"
                                class="btn btn-primary px-4"
                                wire:loading.attr="disabled"
                                wire:target="subirPlantilla, archivoWord">
                            <span wire:loading wire:target="subirPlantilla"
                                  class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload me-2" wire:loading.remove wire:target="subirPlantilla"></i>
                            <span wire:loading.remove wire:target="subirPlantilla">Subir Plantilla</span>
                            <span wire:loading wire:target="subirPlantilla">Subiendo...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta plantilla?</p>
                <p class="text-muted"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" wire:click="delete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('closeModal', event => {
        let modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
        if (modal) modal.hide();
    });

    window.addEventListener('openDeleteModal', event => {
        let modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });

    window.addEventListener('closeDeleteModal', event => {
        let modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        if (modal) modal.hide();
    });
</script>
@endpush
