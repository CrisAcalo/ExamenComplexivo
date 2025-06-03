<!-- Delete Confirmation Modal -->
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($rubricaAEliminar)
            <div class="modal-content">

                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="deleteDataModalLabel">
                        ¿Está seguro de eliminar la Rúbrica: "{{ $rubricaAEliminar->nombre }}"?
                    </h1>
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-danger fw-bold mb-2">
                        Esta acción no se puede deshacer.
                    </div>
                    <p>Se eliminarán todos los componentes, criterios y configuraciones de calificación asociados a esta
                        rúbrica.</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                    {{-- El método destroy ya no necesita el ID como parámetro porque usa $this->rubricaAEliminar --}}
                    <button type="button" class="btn btn-danger" wire:click="destroy()">Eliminar</button>
                </div>
            </div>
        @else
            <div class="modal-content">
                <div class="modal-body">Cargando...</div>
            </div>
        @endif
    </div>
</div>
