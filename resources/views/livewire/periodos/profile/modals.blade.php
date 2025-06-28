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
                    <div class="form-floating mb-3">
                        <select wire:model="carrera_id" id="carrera_id" name="carrera_id"
                            class="form-select @error('carrera_id') is-invalid @enderror">
                            <option selected value="">--Elija Carrera--</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                        <label for="carrera_id">Carrera</label>
                        @error('carrera_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select wire:model="director_id" id="director_id" name="director_id"
                            class="form-select @error('director_id') is-invalid @enderror">
                            <option selected value="">--Elija Director de Carrera--</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <label for="director_id">Director de Carrera</label>
                        @error('director_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select wire:model="docente_apoyo_id" id="docente_apoyo_id" name="docente_apoyo_id"
                            class="form-select @error('docente_apoyo_id') is-invalid @enderror">
                            <option selected value="">--Elija Docente de Apoyo--</option>
                            @foreach ($users as $user)
                                @if ($user->id != $director_id)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <label for="docente_apoyo_id">Docente de Apoyo</label>
                        @error('docente_apoyo_id')
                            <span class="invalid-feedback">{{ $message }}</span>
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
                    <div class="form-floating mb-3">
                        <select wire:model="carrera_id" id="carrera_id_edit" name="carrera_id"
                            class="form-select @error('carrera_id') is-invalid @enderror">
                            <option selected value="">--Elija Carrera--</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                        <label for="carrera_id_edit">Carrera</label>
                        @error('carrera_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select wire:model="director_id" id="director_id_edit" name="director_id"
                            class="form-select @error('director_id') is-invalid @enderror">
                            <option selected value="">--Elija Director de Carrera--</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <label for="director_id_edit">Director de Carrera</label>
                        @error('director_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select wire:model="docente_apoyo_id" id="docente_apoyo_id_edit" name="docente_apoyo_id"
                            class="form-select @error('docente_apoyo_id') is-invalid @enderror">
                            <option selected value="">--Elija Docente de Apoyo--</option>
                            @foreach ($users as $user)
                                @if ($user->id != $director_id)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <label for="docente_apoyo_id_edit">Docente de Apoyo</label>
                        @error('docente_apoyo_id')
                            <span class="invalid-feedback">{{ $message }}</span>
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
