<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Create New User</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name"
                            placeholder="Ej: Juan Vasquez">
                        @error('name')
                            <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            wire:model="email" placeholder="Ej: nombre@example.com">
                        @error('email')
                            <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            wire:model="password" placeholder="***************">
                        @error('password')
                            <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password-confirm" class="form-label">Confirmar Contraseña</label>
                        <input id="password-confirm" type="password" class="form-control"
                            wire:model="password_confirmation" placeholder="***************">
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
                <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" wire:model="selected_id">
                    <div class="form-group">
                        <label for="name"></label>
                        <input wire:model="name" type="text" class="form-control" id="name" placeholder="Name">
                        @error('name')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="email"></label>
                        <input wire:model="email" type="text" class="form-control" id="email"
                            placeholder="Email">
                        @error('email')
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
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @if ($usuarioFounded)
                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">¿Está seguro
                        de eliminar al cliente {{ $usuarioFounded->name }}?
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
                    <button class="btn btn-danger" wire:click="destroy({{ $usuarioFounded->id }})">Eliminar</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Import Profesores Modal -->
<div wire:ignore.self class="modal fade" id="importProfesoresModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="importProfesoresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importProfesoresModalLabel">Importar Profesores (Usuarios) desde Excel
                </h5>
                <button wire:click.prevent="resetImport()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="importarProfesores">
                    <div class="form-group mb-3">
                        <label for="archivoExcelProfesores" class="form-label">Seleccione el archivo Excel (.xlsx,
                            .xls)</label>
                        <input type="file"
                            class="form-control @error('archivoExcelProfesores') is-invalid @enderror"
                            id="archivoExcelProfesores" wire:model="archivoExcelProfesores">
                        @error('archivoExcelProfesores')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="alert alert-info small">
                        <strong>Formato esperado:</strong>
                        <ul>
                            <li>Los datos deben empezar en la **fila 7**.</li>
                            <li>La **fila 6** debe contener los encabezados: `ID ESPE`, `CÉDULA`, `APELLIDOS`,
                                `NOMBRES`, `CORREO`.</li>
                            <li>La **CÉDULA** se usará como **contraseña inicial**.</li>
                            <li>Se asignará el rol "Docente" por defecto a los nuevos usuarios.</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="importarProfesores, archivoExcelProfesores">
                            <span wire:loading wire:target="importarProfesores"
                                class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload" wire:loading.remove wire:target="importarProfesores"></i>
                            Importar
                        </button>
                    </div>
                </form>

                {{-- Resultados de la importación --}}
                @if ($importFinished)
                    <div class="mt-4">
                        <h6>Resultados de la Importación:</h6>
                        {{-- Mensaje de éxito/error ya se muestra con @include('partials.alerts') en la vista principal --}}
                        @if (!empty($importErrors))
                            <div class="alert alert-warning">
                                <p>La importación finalizó, pero se encontraron los siguientes errores (las filas con
                                    errores no se importaron):</p>
                                <ul class="list-group" style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($importErrors as $error)
                                        <li class="list-group-item list-group-item-danger small">{{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click.prevent="resetImport()" type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
