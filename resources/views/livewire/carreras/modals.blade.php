<!-- Add Modal -->
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
                    <div class="form-group">
                        <label for="codigo_carrera"></label>
                        <input wire:model="codigo_carrera" type="text" class="form-control" id="codigo_carrera"
                            placeholder="Codigo Carrera">
                        @error('codigo_carrera')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="nombre"></label>
                        <input wire:model="nombre" type="text" class="form-control" id="nombre"
                            placeholder="Nombre">
                        @error('nombre')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <select wire:model="departamento" id="departamento" name="departamento"
                            class="form-select @error('departamento') is-invalid @enderror">
                            <option selected value="">--Elija--</option>
                            <option value="Departamento de Ciencias de la Energía y Mecánica">Departamento de Ciencias
                                de la Energía y Mecánica</option>
                            <option value="Departamento de Ciencias de la Computación">Departamento de Ciencias de la
                                Computación</option>
                            <option value="Departamento de Eléctrica , Electrónica y Telecomunicaciones">Departamento de
                                Eléctrica , Electrónica y Telecomunicaciones</option>
                            <option value="Departamento de Ciencias de la Vida y de la Agricultura">Departamento de
                                Ciencias de la Vida y de la Agricultura</option>
                            <option value="Departamento de Ciencias Administrativas, Económicas y de Comercio">
                                Departamento de Ciencias Administrativas, Económicas y de Comercio</option>
                            <option value="Departamento de Ciencias de la Tierra y Construcción">Departamento de
                                Ciencias de la Tierra y Construcción</option>
                            <option value="Departamento de Ciencias Humanas y Sociales">Departamento de Ciencias Humanas
                                y Sociales</option>
                        </select>
                        <label for="departamento">Departamento</label>
                        @error('departamento')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <select wire:model="sede" id="sede" name="sede"
                            class="form-select @error('sede') is-invalid @enderror">
                            <option selected value="">--Elija--</option>
                            <option value="Latacunga">Latacunga</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Sangolquí">Sangolquí</option>
                        </select>
                        <label for="departamento">Departamento</label>
                        @error('sede')
                            <span class="invalid-feedback">{{ $message }}</span>
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
                <h5 class="modal-title" id="updateModalLabel">Update Carrera</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="codigo_carrera"></label>
                        <input wire:model="codigo_carrera" type="text" class="form-control" id="codigo_carrera"
                            placeholder="Codigo Carrera">
                        @error('codigo_carrera')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="nombre"></label>
                        <input wire:model="nombre" type="text" class="form-control" id="nombre"
                            placeholder="Nombre">
                        @error('nombre')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <select wire:model="departamento" id="departamento" name="departamento"
                            class="form-select @error('departamento') is-invalid @enderror">
                            <option selected value="">--Elija--</option>
                            <option value="Departamento de Ciencias de la Energía y Mecánica">Departamento de Ciencias
                                de la Energía y Mecánica</option>
                            <option value="Departamento de Ciencias de la Computación">Departamento de Ciencias de la
                                Computación</option>
                            <option value="Departamento de Eléctrica , Electrónica y Telecomunicaciones">Departamento de
                                Eléctrica , Electrónica y Telecomunicaciones</option>
                            <option value="Departamento de Ciencias de la Vida y de la Agricultura">Departamento de
                                Ciencias de la Vida y de la Agricultura</option>
                            <option value="Departamento de Ciencias Administrativas, Económicas y de Comercio">
                                Departamento de Ciencias Administrativas, Económicas y de Comercio</option>
                            <option value="Departamento de Ciencias de la Tierra y Construcción">Departamento de
                                Ciencias de la Tierra y Construcción</option>
                            <option value="Departamento de Ciencias Humanas y Sociales">Departamento de Ciencias Humanas
                                y Sociales</option>
                        </select>
                        <label for="departamento">Departamento</label>
                        @error('departamento')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <select wire:model="sede" id="sede" name="sede"
                            class="form-select @error('sede') is-invalid @enderror">
                            <option selected value="">--Elija--</option>
                            <option value="Latacunga">Latacunga</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="Sangolquí">Sangolquí</option>
                        </select>
                        <label for="sede">Sede</label>
                        @error('sede')
                            <span class="invalid-feedback">{{ $message }}</span>
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

<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($founded)
            <div class="modal-content">
                @include('partials.alerts')

                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">¿Está seguro
                        de eliminar la Carrera?
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-danger fw-bold">
                        Los datos no se podrán recuperar
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-danger" wire:click="destroy({{ $founded->id }})">Eliminar</button>
                </div>

            </div>
        @endif
    </div>
</div>
