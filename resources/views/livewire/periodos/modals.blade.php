@can('gestionar periodos')
    <!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New Periodo</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="codigo_periodo">Código Período</label>
                        <input wire:model="codigo_periodo" type="text" class="form-control" id="codigo_periodo"
                            placeholder="Código Período">
                        @error('codigo_periodo')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input wire:model="fecha_inicio" type="date" class="form-control" id="fecha_inicio"
                            placeholder="Fecha Inicio">
                        @error('fecha_inicio')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input wire:model="fecha_fin" type="date" class="form-control" id="fecha_fin"
                            placeholder="Fecha Fin">
                        @error('fecha_fin')
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
    @include('partials.alerts')
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Periodo</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="codigo_periodo">Código Período</label>
                        <input wire:model="codigo_periodo" type="text" class="form-control" id="codigo_periodo"
                            placeholder="Código Período">
                        @error('codigo_periodo')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input wire:model="descripcion" type="text" class="form-control" id="descripcion"
                            placeholder="Descripción" readonly>
                        <small class="form-text text-muted">La descripción se genera automáticamente basada en las fechas.</small>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input wire:model="fecha_inicio" type="date" class="form-control" id="fecha_inicio"
                            placeholder="Fecha Inicio">
                        @error('fecha_inicio')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input wire:model="fecha_fin" type="date" class="form-control" id="fecha_fin"
                            placeholder="Fecha Fin">
                        @error('fecha_fin')
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

{{-- Delete Modal --}}
<div wire:ignore.self class="modal fade" id="deleteDataModal" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($confirmingPeriodoDeletion)
            <div class="modal-content">
                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="deleteDataModalLabel">¿Está seguro de eliminar el Periodo?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetDeleteConfirmation"></button>
                </div>
                <div class="modal-body">
                    <p>Está a punto de eliminar el período con ID <strong>{{ $periodoAEliminarId }}</strong>.</p>
                    <p class="text-danger fw-bold">Los datos no se podrán recuperar.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="resetDeleteConfirmation">Cancelar</button>
                    <button class="btn btn-danger" wire:click="destroy()">Sí, Eliminar</button>
                </div>
            </div>
        @endif
    </div>
</div>
@endcan
