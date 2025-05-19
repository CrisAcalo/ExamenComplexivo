<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New Tribunale</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-floating mb-3">
                        <select wire:model="estudiante_id" id="estudiante_id" name="estudiante_id"
                            class="form-select @error('estudiante_id') is-invalid @enderror">
                            <option selected value="">--Elija Estudiante--</option>
                            @foreach ($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombres }}
                                    {{ $estudiante->apellidos }}</option>
                            @endforeach
                        </select>
                        <label for="estudiante_id">Estudiante</label>
                        @error('estudiante_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input wire:model="fecha" type="date" class="form-control" id="fecha"
                            placeholder="Fecha">
                        @error('fecha')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="hora_inicio">Hora Inicio</label>
                        <input wire:model="hora_inicio" type="time" class="form-control" id="hora_inicio"
                            placeholder="Hora Inicio">
                        @error('hora_inicio')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="hora_fin">Hora Fin</label>
                        <input wire:model="hora_fin" type="time" class="form-control" id="hora_fin"
                            placeholder="Hora Fin">
                        @error('hora_fin')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="card p-3 mt-3">
                        <h5 class="card-title">Tribunal</h5>
                        <p class="card-text">Seleccione los integrantes del tribunal.</p>
                        <div class="form-floating mb-3">
                            <select wire:model="presidente_id" id="presidente_id" name="presidente_id"
                                class="form-select @error('presidente_id') is-invalid @enderror">
                                <option selected value="">--Elija Presidente--</option>
                                @foreach ($profesores as $profesor)
                                    @if ($profesor->id != $integrante1_id && $profesor->id != $integrante2_id)
                                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label for="presidente_id">Presidente</label>
                            @error('presidente_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <select wire:model="integrante1_id" id="integrante1_id" name="integrante1_id"
                                class="form-select @error('integrante1_id') is-invalid @enderror">
                                <option selected value="">--Elija Integrante 1--</option>
                                @foreach ($profesores as $profesor)
                                    @if ($profesor->id != $presidente_id && $profesor->id != $integrante2_id)
                                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label for="integrante1_id">Integrante 1</label>
                            @error('integrante1_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <select wire:model="integrante2_id" id="integrante2_id" name="integrante2_id"
                                class="form-select @error('integrante2_id') is-invalid @enderror">
                                <option selected value="">--Elija Integrante 2--</option>
                                @foreach ($profesores as $profesor)
                                    @if ($profesor->id != $presidente_id && $profesor->id != $integrante1_id)
                                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label for="integrante2_id">Integrante 2</label>
                            @error('integrante2_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
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
                <h5 class="modal-title" id="updateModalLabel">Update Tribunale</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="carrera_periodo_id"></label>
                        <input wire:model="carrera_periodo_id" type="text" class="form-control"
                            id="carrera_periodo_id" placeholder="Carrera Periodo Id">
                        @error('carrera_periodo_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="estudiante_id"></label>
                        <input wire:model="estudiante_id" type="text" class="form-control" id="estudiante_id"
                            placeholder="Estudiante Id">
                        @error('estudiante_id')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha"></label>
                        <input wire:model="fecha" type="text" class="form-control" id="fecha"
                            placeholder="Fecha">
                        @error('fecha')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="hora_inicio"></label>
                        <input wire:model="hora_inicio" type="text" class="form-control" id="hora_inicio"
                            placeholder="Hora Inicio">
                        @error('hora_inicio')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="hora_fin"></label>
                        <input wire:model="hora_fin" type="text" class="form-control" id="hora_fin"
                            placeholder="Hora Fin">
                        @error('hora_fin')
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



<div wire:ignore.self class="modal fade" id="createComponenteModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="createComponenteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createComponenteModal">Create New Componente</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>

                    <div class="form-floating mb-3">
                        <input wire:model="nombre_componente" type="text"
                            class="form-control @error('nombre_componente') is-invalid @enderror"
                            id="nombre_componente" placeholder="Nombre Componente">
                        <label for="nombre_componente">Nombre Componente</label>
                        @error('nombre_componente')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- ponderacion --}}
                    <div class="form-floating mb-3">
                        <input wire:model="ponderacion_componente" type="number"
                            class="form-control @error('ponderacion_componente') is-invalid @enderror"
                            id="ponderacion_componente" placeholder="Ponderacion">
                        <label for="ponderacion_componente">Ponderacion</label>
                        @error('ponderacion_componente')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" wire:click.prevent="storeComponente()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
