<!-- Add Modal -->
@can('gestionar carreras')
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New Carrera</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="codigo_carrera" class="form-label">Código Carrera <span class="text-danger">*</span></label>
                        <input wire:model="codigo_carrera" type="text" class="form-control @error('codigo_carrera') is-invalid @enderror"
                            id="codigo_carrera" placeholder="Código Carrera">
                        @error('codigo_carrera')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
                            id="nombre" placeholder="Nombre">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="departamento_id" class="form-label">Departamento <span class="text-danger">*</span></label>
                        <select wire:model="departamento_id" id="departamento_id" name="departamento_id"
                            class="form-select @error('departamento_id') is-invalid @enderror">
                            <option value="">--Elija Departamento--</option>
                            @foreach($departamentos->sortBy('nombre') as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('departamento_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="modalidad" class="form-label">Modalidad <span class="text-danger">*</span></label>
                        <select wire:model="modalidad" id="modalidad" name="modalidad"
                            class="form-select @error('modalidad') is-invalid @enderror">
                            <option value="">--Elija Modalidad--</option>
                            <option value="Presencial">Presencial</option>
                            <option value="Virtual">Virtual</option>
                        </select>
                        @error('modalidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="sede" class="form-label">Sede <span class="text-danger">*</span></label>
                        <select wire:model="sede" id="sede" name="sede"
                            class="form-select @error('sede') is-invalid @enderror">
                            <option value="">--Elija Sede--</option>
                            <option value="Latacunga">Latacunga</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Sangolquí">Sangolquí</option>
                        </select>
                        @error('sede')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary close-modal">Save</button>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Edit Modal -->
@can('gestionar carreras')
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateDataModalLabel">Update Carrera</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="mb-3">
                        <label for="codigo_carrera_edit" class="form-label">Código Carrera <span class="text-danger">*</span></label>
                        <input wire:model="codigo_carrera" type="text" class="form-control @error('codigo_carrera') is-invalid @enderror"
                            id="codigo_carrera_edit" placeholder="Código Carrera">
                        @error('codigo_carrera')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nombre_edit" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
                            id="nombre_edit" placeholder="Nombre">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="departamento_id_edit" class="form-label">Departamento <span class="text-danger">*</span></label>
                        <select wire:model="departamento_id" id="departamento_id_edit" name="departamento_id"
                            class="form-select @error('departamento_id') is-invalid @enderror">
                            <option value="">--Elija Departamento--</option>
                            @foreach($departamentos->sortBy('nombre') as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('departamento_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="modalidad_edit" class="form-label">Modalidad <span class="text-danger">*</span></label>
                        <select wire:model="modalidad" id="modalidad_edit" name="modalidad"
                            class="form-select @error('modalidad') is-invalid @enderror">
                            <option value="">--Elija Modalidad--</option>
                            <option value="Presencial">Presencial</option>
                            <option value="Virtual">Virtual</option>
                        </select>
                        @error('modalidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="sede_edit" class="form-label">Sede <span class="text-danger">*</span></label>
                        <select wire:model="sede" id="sede_edit" name="sede"
                            class="form-select @error('sede') is-invalid @enderror">
                            <option value="">--Elija Sede--</option>
                            <option value="Latacunga">Latacunga</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Sangolquí">Sangolquí</option>
                        </select>
                        @error('sede')
                            <div class="invalid-feedback">{{ $message }}</div>
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
@endcan

<!-- Delete Modal -->
@can('gestionar carreras')
<div wire:ignore.self class="modal fade" id="deleteDataModal" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Delete Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>¿Está seguro de eliminar los datos?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click.prevent="destroy()" class="btn btn-danger close-modal">Yes!
                    Delete</button>
            </div>
        </div>
    </div>
</div>
@endcan
