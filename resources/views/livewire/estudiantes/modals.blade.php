<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New Estudiante</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
           <div class="modal-body">
				<form>
                    <div class="form-group">
                        <label for="nombres"></label>
                        <input wire:model="nombres" type="text" class="form-control" id="nombres" placeholder="Nombres">@error('nombres') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="apellidos"></label>
                        <input wire:model="apellidos" type="text" class="form-control" id="apellidos" placeholder="Apellidos">@error('apellidos') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cedula"></label>
                        <input wire:model="cedula" type="text" class="form-control" id="cedula" placeholder="Cédula">@error('cedula') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="correo"></label>
                        <input wire:model="correo" type="email" class="form-control" id="correo" placeholder="Correo">@error('correo') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="telefono"></label>
                        <input wire:model="telefono" type="text" class="form-control" id="telefono" placeholder="Teléfono">@error('telefono') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="username"></label>
                        <input wire:model="username" type="text" class="form-control" id="username" placeholder="Username">@error('username') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="ID_estudiante"></label>
                        <input wire:model="ID_estudiante" type="text" class="form-control" id="ID_estudiante" placeholder="Id Estudiante">@error('ID_estudiante') <span class="error text-danger">{{ $message }}</span> @enderror
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
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Estudiante</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
					<input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="nombres"></label>
                        <input wire:model="nombres" type="text" class="form-control" id="nombres" placeholder="Nombres">@error('nombres') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="apellidos"></label>
                        <input wire:model="apellidos" type="text" class="form-control" id="apellidos" placeholder="Apellidos">@error('apellidos') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cedula"></label>
                        <input wire:model="cedula" type="text" class="form-control" id="cedula" placeholder="Cédula">@error('cedula') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="correo"></label>
                        <input wire:model="correo" type="email" class="form-control" id="correo" placeholder="Correo">@error('correo') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="telefono"></label>
                        <input wire:model="telefono" type="text" class="form-control" id="telefono" placeholder="Teléfono">@error('telefono') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="username"></label>
                        <input wire:model="username" type="text" class="form-control" id="username" placeholder="Username">@error('username') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="ID_estudiante"></label>
                        <input wire:model="ID_estudiante" type="text" class="form-control" id="ID_estudiante" placeholder="Id Estudiante">@error('ID_estudiante') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        de eliminar al Estudiante?
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

<!-- Import Modal -->
<div wire:ignore.self class="modal fade" id="importModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importar Estudiantes desde Excel</h5>
                <button wire:click.prevent="resetImport()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="importarEstudiantes">
                    <div class="form-group mb-3">
                        <label for="archivoExcel" class="form-label">Seleccione el archivo Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-control @error('archivoExcel') is-invalid @enderror" id="archivoExcel" wire:model="archivoExcel">
                        @error('archivoExcel') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert alert-info small">
                        <strong>Formato esperado:</strong>
                        <ul>
                            <li>El archivo debe ser de tipo Excel (.xlsx o .xls).</li>
                            <li>Los datos deben empezar en la **fila 7**.</li>
                            <li>La **fila 6** debe contener los encabezados: `ID ESPE`, `CÉDULA`, `APELLIDOS`, `NOMBRES`, `CORREO`.</li>
                            <li>Las columnas se mapearán automáticamente. El `username` se generará a partir del correo.</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="importarEstudiantes, archivoExcel">
                            <span wire:loading wire:target="importarEstudiantes" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload" wire:loading.remove wire:target="importarEstudiantes"></i>
                            Importar
                        </button>
                    </div>
                </form>

                {{-- Resultados de la importación --}}
                @if ($importFinished)
                    <div class="mt-4">
                        <h6>Resultados de la Importación:</h6>
                        @if (empty($importErrors))
                            <div class="alert alert-success">¡Todas las filas se importaron exitosamente!</div>
                        @else
                            <div class="alert alert-warning">
                                <p>La importación finalizó, pero se encontraron los siguientes errores (las filas con errores no se importaron):</p>
                                <ul class="list-group" style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($importErrors as $error)
                                        <li class="list-group-item list-group-item-danger small">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click.prevent="resetImport()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
