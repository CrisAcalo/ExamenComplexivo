{{-- livewire.tribunales.modals.blade.php --}}

<!-- Add Modal (Crear Tribunal) -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    {{-- ... (contenido del modal de creación sin cambios, asegúrate que los wire:model sean correctos) ... --}}
    <div class="modal-dialog modal-lg" role="document"> {{-- modal-lg para más espacio --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Añadir Nuevo Tribunal</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    {{-- Estudiante --}}
                    <div class="mb-3">
                        <label for="estudiante_id_create" class="form-label">Estudiante <span class="text-danger">*</span></label>
                        <select wire:model.defer="estudiante_id" id="estudiante_id_create" name="estudiante_id"
                            class="form-select @error('estudiante_id') is-invalid @enderror">
                            <option value="">-- Elija un Estudiante --</option>
                            @forelse ($estudiantesDisponibles as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->apellidos }} {{ $estudiante->nombres }} ({{ $estudiante->ID_estudiante }})</option>
                            @empty
                                <option value="" disabled>No hay estudiantes disponibles sin tribunal asignado.</option>
                            @endforelse
                        </select>
                        @error('estudiante_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        {{-- Fecha --}}
                        <div class="col-md-4 mb-3">
                            <label for="fecha_create" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input wire:model.defer="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha_create">
                            @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        {{-- Hora Inicio --}}
                        <div class="col-md-4 mb-3">
                            <label for="hora_inicio_create" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                            <input wire:model.defer="hora_inicio" type="time" class="form-control @error('hora_inicio') is-invalid @enderror" id="hora_inicio_create">
                            @error('hora_inicio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        {{-- Hora Fin --}}
                        <div class="col-md-4 mb-3">
                            <label for="hora_fin_create" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                            <input wire:model.defer="hora_fin" type="time" class="form-control @error('hora_fin') is-invalid @enderror" id="hora_fin_create">
                            @error('hora_fin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>


                    <div class="card p-3 mt-3">
                        <h6 class="card-title">Miembros del Tribunal <span class="text-danger">*</span></h6>
                        <div class="row">
                            {{-- Presidente --}}
                            <div class="col-md-4 mb-3">
                                <label for="presidente_id_create" class="form-label">Presidente</label>
                                <select wire:model.defer="presidente_id" id="presidente_id_create" name="presidente_id"
                                    class="form-select @error('presidente_id') is-invalid @enderror">
                                    <option value="">-- Elija Presidente --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                        @if ( (empty($integrante1_id) || $profesor->id != $integrante1_id) && (empty($integrante2_id) || $profesor->id != $integrante2_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('presidente_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            {{-- Integrante 1 --}}
                            <div class="col-md-4 mb-3">
                                <label for="integrante1_id_create" class="form-label">Integrante 1</label>
                                <select wire:model.defer="integrante1_id" id="integrante1_id_create" name="integrante1_id"
                                    class="form-select @error('integrante1_id') is-invalid @enderror">
                                    <option value="">-- Elija Integrante 1 --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                    @if ( (empty($presidente_id) || $profesor->id != $presidente_id) && (empty($integrante2_id) || $profesor->id != $integrante2_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('integrante1_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            {{-- Integrante 2 --}}
                            <div class="col-md-4 mb-3">
                                <label for="integrante2_id_create" class="form-label">Integrante 2</label>
                                <select wire:model.defer="integrante2_id" id="integrante2_id_create" name="integrante2_id"
                                    class="form-select @error('integrante2_id') is-invalid @enderror">
                                    <option value="">-- Elija Integrante 2 --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                    @if ( (empty($presidente_id) || $profesor->id != $presidente_id) && (empty($integrante1_id) || $profesor->id != $integrante1_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('integrante2_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click.prevent="cancel()" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Tribunal
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Tribunal Confirmation Modal -->
<div wire:ignore.self class="modal fade" id="deleteTribunalModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="deleteTribunalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($tribunalAEliminar)
            <div class="modal-content">
                <div class="modal-header bg-danger text-light">
                    <h5 class="modal-title" id="deleteTribunalModalLabel">Confirmar Eliminación</h5>
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar el tribunal para el estudiante
                        <strong>{{ $tribunalAEliminar->estudiante->nombres }} {{ $tribunalAEliminar->estudiante->apellidos }}</strong>
                        programado para el <strong>{{ \Carbon\Carbon::parse($tribunalAEliminar->fecha)->format('d/m/Y') }}</strong>?
                    </p>
                    <p class="text-danger fw-bold">Esta acción no se puede deshacer y se eliminarán los miembros asociados.</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="destroy()">
                        <i class="bi bi-trash-fill"></i> Sí, Eliminar
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
