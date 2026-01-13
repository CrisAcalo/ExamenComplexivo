<!-- Add Modal -->
@if($this->puedeGestionarEstudiantes())
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); color: white;">
                <h5 class="modal-title fw-bold" id="createDataModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Crear nuevo Estudiante
                </h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
           <div class="modal-body p-4">
				<form>
                    <div class="form-group mb-3">
                        <label for="carrera_periodo_id">Carrera y Periodo <span class="text-danger">*</span></label>
                        <select wire:model="carrera_periodo_id" class="form-select @error('carrera_periodo_id') is-invalid @enderror" id="carrera_periodo_id">
                            <option value="">Seleccione carrera y periodo</option>
                            @foreach($carrerasPeriodosDisponibles as $cp)
                                <option value="{{ $cp->id }}">
                                    {{ $cp->carrera->nombre ?? 'N/A' }} - {{ $cp->periodo->codigo_periodo ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('carrera_periodo_id') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="nombres">Nombres</label>
                        <input wire:model="nombres" type="text" class="form-control" id="nombres" placeholder="Nombres">@error('nombres') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
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
                        <label for="telefono">Teléfono</label>
                        <input wire:model="telefono" type="text" class="form-control" id="telefono" placeholder="Teléfono">@error('telefono') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input wire:model="username" type="text" class="form-control" id="username" placeholder="Nombre de usuario">@error('username') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="ID_estudiante">ID Estudiante</label>
                        <input wire:model="ID_estudiante" type="text" class="form-control" id="ID_estudiante" placeholder="ID Estudiante">@error('ID_estudiante') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cerrar</button>
                <button type="button" wire:click.prevent="store()" class="btn px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;"><i class="bi bi-check-circle me-2"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Edit Modal -->
@if($this->puedeGestionarEstudiantes())
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); color: white;">
                <h5 class="modal-title fw-bold" id="updateModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Actualizar Estudiante
                </h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form>
					<input type="hidden" wire:model="selected_id">
                    <div class="form-group mb-3">
                        <label for="carrera_periodo_id_edit" class="fw-semibold"><i class="bi bi-mortarboard-fill text-success me-2"></i>Carrera y Periodo <span class="text-danger">*</span></label>
                        <select wire:model="carrera_periodo_id" class="form-select @error('carrera_periodo_id') is-invalid @enderror" id="carrera_periodo_id_edit">
                            <option value="">Seleccione carrera y periodo</option>
                            @foreach($carrerasPeriodosDisponibles as $cp)
                                <option value="{{ $cp->id }}">
                                    {{ $cp->carrera->nombre ?? 'N/A' }} - {{ $cp->periodo->codigo_periodo ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('carrera_periodo_id') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nombres" class="fw-semibold">Nombres</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill text-success"></i></span>
                                    <input wire:model="nombres" type="text" class="form-control" id="nombres" placeholder="Nombres">
                                </div>
                                @error('nombres') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="apellidos" class="fw-semibold">Apellidos</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill text-success"></i></span>
                                    <input wire:model="apellidos" type="text" class="form-control" id="apellidos" placeholder="Apellidos">
                                </div>
                                @error('apellidos') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cedula" class="fw-semibold">Cédula</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-card-text text-success"></i></span>
                                    <input wire:model="cedula" type="text" class="form-control" id="cedula" placeholder="Cédula">
                                </div>
                                @error('cedula') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ID_estudiante" class="fw-semibold">ID Estudiante</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-hash text-success"></i></span>
                                    <input wire:model="ID_estudiante" type="text" class="form-control" id="ID_estudiante" placeholder="ID Estudiante">
                                </div>
                                @error('ID_estudiante') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="correo" class="fw-semibold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope-fill text-success"></i></span>
                            <input wire:model="correo" type="email" class="form-control" id="correo" placeholder="correo@espe.edu.ec">
                        </div>
                        @error('correo') <span class="error text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telefono" class="fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone-fill text-success"></i></span>
                                    <input wire:model="telefono" type="text" class="form-control" id="telefono" placeholder="Teléfono">
                                </div>
                                @error('telefono') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="username" class="fw-semibold">Nombre de usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-at text-success"></i></span>
                                    <input wire:model="username" type="text" class="form-control" id="username" placeholder="Nombre de usuario">
                                </div>
                                @error('username') <span class="error text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cerrar</button>
                <button type="button" wire:click.prevent="update()" class="btn px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;"><i class="bi bi-check-circle me-2"></i>Actualizar</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delete Modal -->
@if($this->puedeGestionarEstudiantes())
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($founded)
            <div class="modal-content border-0 shadow-lg">
                @include('partials.alerts')

                <div class="modal-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white;">
                    <h1 class="modal-title fs-5 fw-bold" id="staticBackdropLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>¿Está seguro de eliminar al Estudiante?
                    </h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(231, 76, 60, 0.1); border-radius: 50%;">
                            <i class="bi bi-person-x-fill" style="font-size: 2.5rem; color: #e74c3c;"></i>
                        </div>
                    </div>
                    <div class="alert alert-danger" style="background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.3);">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Los datos no se podrán recuperar</strong>
                        <p class="mb-0 mt-2 small">Esta acción es permanente y no se puede deshacer</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cancelar</button>
                    <button class="btn px-4" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none;" wire:click="destroy({{ $founded->id }})"><i class="bi bi-trash-fill me-2"></i>Eliminar</button>
                </div>

            </div>
        @endif
    </div>
</div>
@endif

<!-- Import Modal -->
@if($this->puedeImportarEstudiantes())
<div wire:ignore.self class="modal fade" id="importModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); color: white;">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar Estudiantes desde Excel
                </h5>
                <button wire:click.prevent="resetImport()" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form wire:submit.prevent="importarEstudiantes">
                    <div class="form-group mb-3">
                        <label for="carrera_periodo_id_import" class="form-label fw-semibold">
                            <i class="bi bi-mortarboard-fill text-success me-2"></i>Carrera y Periodo <span class="text-danger">*</span>
                        </label>
                        <select wire:model="carrera_periodo_id" class="form-select @error('carrera_periodo_id') is-invalid @enderror" id="carrera_periodo_id_import">
                            <option value="">Seleccione carrera y periodo</option>
                            @foreach($carrerasPeriodosDisponibles as $cp)
                                <option value="{{ $cp->id }}">
                                    {{ $cp->carrera->nombre ?? 'N/A' }} - {{ $cp->periodo->codigo_periodo ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Todos los estudiantes importados se asignarán a esta carrera y periodo</small>
                        @error('carrera_periodo_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="archivoExcel" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-arrow-up text-success me-2"></i>Seleccione el archivo Excel (.xlsx, .xls)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-paperclip text-success"></i></span>
                            <input type="file" class="form-control @error('archivoExcel') is-invalid @enderror" id="archivoExcel" wire:model="archivoExcel">
                        </div>
                        @error('archivoExcel') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert" style="background: linear-gradient(135deg, rgba(61, 142, 114, 0.1) 0%, rgba(61, 166, 106, 0.1) 100%); border-left: 4px solid #3d8e72ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                            <div class="small">
                                <strong class="d-block mb-2">Formato esperado del archivo:</strong>
                                <ul class="mb-0">
                                    <li>El archivo debe ser de tipo Excel (.xlsx o .xls)</li>
                                    <li>Los datos deben empezar en la <strong>fila 7</strong></li>
                                    <li>La <strong>fila 6</strong> debe contener los encabezados: <code>ID ESPE</code>, <code>CÉDULA</code>, <code>APELLIDOS</code>, <code>NOMBRES</code>, <code>CORREO</code></li>
                                    <li>Las columnas se mapearán automáticamente. El <code>username</code> se generará a partir del correo</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;" wire:loading.attr="disabled" wire:target="importarEstudiantes, archivoExcel">
                            <span wire:loading wire:target="importarEstudiantes" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload" wire:loading.remove wire:target="importarEstudiantes"></i>
                            Importar
                        </button>
                    </div>
                </form>

                {{-- Resultados de la importación --}}
                @if ($importFinished)
                    <div class="mt-4">
                        <h6 class="fw-bold"><i class="bi bi-clipboard-check text-success me-2"></i>Resultados de la Importación:</h6>
                        @if (empty($importErrors))
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-3" style="font-size: 1.5rem;"></i>
                                <div><strong>¡Todas las filas se importaron exitosamente!</strong></div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
                                    <p class="mb-0"><strong>La importación finalizó, pero se encontraron errores:</strong><br>
                                    <small>Las filas con errores no se importaron</small></p>
                                </div>
                                <ul class="list-group" style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($importErrors as $error)
                                        <li class="list-group-item list-group-item-danger small"><i class="bi bi-x-circle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light">
                <button wire:click.prevent="resetImport()" type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif
