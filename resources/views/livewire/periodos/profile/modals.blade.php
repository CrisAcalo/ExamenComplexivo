<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Asignar Carrera a Periodo</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="carrera_id" class="form-label">Carrera <span class="text-danger">*</span></label>
                        <select wire:model="carrera_id" id="carrera_id" name="carrera_id"
                            class="form-select @error('carrera_id') is-invalid @enderror">
                            <option value="">--Elija Carrera--</option>
                            @foreach ($carreras->sortBy('nombre') as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carrera_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="director_id" class="form-label">Director de Carrera <span class="text-danger">*</span></label>
                        <select wire:model="director_id" id="director_id" name="director_id"
                            class="form-select @error('director_id') is-invalid @enderror">
                            <option value="">--Elija Director de Carrera--</option>
                            @foreach ($users->sortBy('name') as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('director_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="docente_apoyo_id" class="form-label">Docente de Apoyo</label>
                        <select wire:model="docente_apoyo_id" id="docente_apoyo_id" name="docente_apoyo_id"
                            class="form-select @error('docente_apoyo_id') is-invalid @enderror">
                            <option value="">--Elija Docente de Apoyo--</option>
                            @foreach ($users->sortBy('name') as $user)
                                @if ($user->id != $director_id)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('docente_apoyo_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateDataModalLabel">Editar Asignación</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="mb-3">
                        <label for="carrera_id_edit" class="form-label">Carrera <span class="text-danger">*</span></label>
                        <select wire:model="carrera_id" id="carrera_id_edit" name="carrera_id"
                            class="form-select @error('carrera_id') is-invalid @enderror">
                            <option value="">--Elija Carrera--</option>
                            @foreach ($carreras->sortBy('nombre') as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carrera_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="director_id_edit" class="form-label">Director de Carrera <span class="text-danger">*</span></label>
                        <select wire:model="director_id" id="director_id_edit" name="director_id"
                            class="form-select @error('director_id') is-invalid @enderror">
                            <option value="">--Elija Director de Carrera--</option>
                            @foreach ($users->sortBy('name') as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('director_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="docente_apoyo_id_edit" class="form-label">Docente de Apoyo</label>
                        <select wire:model="docente_apoyo_id" id="docente_apoyo_id_edit" name="docente_apoyo_id"
                            class="form-select @error('docente_apoyo_id') is-invalid @enderror">
                            <option value="">--Elija Docente de Apoyo--</option>
                            @foreach ($users->sortBy('name') as $user)
                                @if ($user->id != $director_id)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('docente_apoyo_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary"
                    data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:click.prevent="update()" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($founded)
            <div class="modal-content">
                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">¿Está seguro de eliminar la asignación?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-danger fw-bold">
                        Los datos no se podrán recuperar
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-danger" wire:click="destroy({{ $founded->id }})">Eliminar</button>
                </div>
            </div>
        @endif
    </div>
</div>
