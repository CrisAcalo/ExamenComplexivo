<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New Carreras Periodo</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="carrera_id"></label>
                        <input wire:model="carrera_id" type="text" class="form-control" id="carrera_id"
                            placeholder="Carrera Id">
                        @error('carrera_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="periodo_id"></label>
                        <input wire:model="periodo_id" type="text" class="form-control" id="periodo_id"
                            placeholder="Periodo Id">
                        @error('periodo_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="docente_apoyo_id"></label>
                        <input wire:model="docente_apoyo_id" type="text" class="form-control" id="docente_apoyo_id"
                            placeholder="Docente Apoyo Id">
                        @error('docente_apoyo_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="director_id"></label>
                        <input wire:model="director_id" type="text" class="form-control" id="director_id"
                            placeholder="Director Id">
                        @error('director_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Carreras Periodo</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="carrera_id"></label>
                        <input wire:model="carrera_id" type="text" class="form-control" id="carrera_id"
                            placeholder="Carrera Id">
                        @error('carrera_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="periodo_id"></label>
                        <input wire:model="periodo_id" type="text" class="form-control" id="periodo_id"
                            placeholder="Periodo Id">
                        @error('periodo_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="docente_apoyo_id"></label>
                        <input wire:model="docente_apoyo_id" type="text" class="form-control" id="docente_apoyo_id"
                            placeholder="Docente Apoyo Id">
                        @error('docente_apoyo_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="director_id"></label>
                        <input wire:model="director_id" type="text" class="form-control" id="director_id"
                            placeholder="Director Id">
                        @error('director_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click.prevent="update()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>


