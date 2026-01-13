@can('gestionar carreras')
    <!-- Modal: Crear Nueva Carrera -->
    <div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="createDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div class="w-100">
                        <h5 class="modal-title fw-bold" id="createDataModalLabel">
                            Agregar Nueva Carrera
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <form>
                        <!-- Código Carrera -->
                        <div class="mb-3">
                            <label for="codigo_carrera_create" class="form-label fw-semibold">
                                <i class="bi bi-hash text-primary me-1"></i>Código de Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-upc-scan text-muted"></i>
                                </span>
                                <input wire:model="codigo_carrera" type="text"
                                       class="form-control border-start-0 @error('codigo_carrera') is-invalid @enderror"
                                       id="codigo_carrera_create"
                                       placeholder="Ej: ING-001, MED-002">
                            </div>
                            @error('codigo_carrera')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre_create" class="form-label fw-semibold">
                                <i class="bi bi-mortarboard text-success me-1"></i>Nombre de la Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-card-text text-muted"></i>
                                </span>
                                <input wire:model="nombre" type="text"
                                       class="form-control border-start-0 @error('nombre') is-invalid @enderror"
                                       id="nombre_create"
                                       placeholder="Ej: Ingeniería en Sistemas">
                            </div>
                            @error('nombre')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Departamento -->
                        <div class="mb-3">
                            <label for="departamento_id_create" class="form-label fw-semibold">
                                <i class="bi bi-building text-info me-1"></i>Departamento
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-building-check text-muted"></i>
                                </span>
                                <select wire:model="departamento_id" id="departamento_id_create"
                                        class="form-select border-start-0 @error('departamento_id') is-invalid @enderror">
                                    <option value="">--Seleccione un Departamento--</option>
                                    @foreach($departamentos->sortBy('nombre') as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('departamento_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Modalidad -->
                                <div class="mb-3">
                                    <label for="modalidad_create" class="form-label fw-semibold">
                                        <i class="bi bi-laptop text-primary me-1"></i>Modalidad
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person-workspace text-muted"></i>
                                        </span>
                                        <select wire:model="modalidad" id="modalidad_create"
                                                class="form-select border-start-0 @error('modalidad') is-invalid @enderror">
                                            <option value="">--Seleccione Modalidad--</option>
                                            <option value="Presencial">Presencial</option>
                                            <option value="En línea">En línea</option>
                                        </select>
                                    </div>
                                    @error('modalidad')
                                        <div class="text-danger small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Sede -->
                                <div class="mb-3">
                                    <label for="sede_create" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt text-danger me-1"></i>Sede
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-pin-map text-muted"></i>
                                        </span>
                                        <select wire:model="sede" id="sede_create"
                                                class="form-select border-start-0 @error('sede') is-invalid @enderror">
                                            <option value="">--Seleccione Sede--</option>
                                            <option value="Latacunga">Latacunga</option>
                                            <option value="Santo Domingo">Santo Domingo</option>
                                            <option value="Sangolquí">Sangolquí</option>
                                        </select>
                                    </div>
                                    @error('sede')
                                        <div class="text-danger small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                            wire:click.prevent="cancel()">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" wire:click.prevent="store()"
                            class="btn text-white px-4"
                             style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="bi bi-check-circle me-2"></i>Guardar Carrera
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Carrera -->
    <div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div class="w-100">
                        <h5 class="modal-title fw-bold" id="updateModalLabel">
                            Editar Carrera
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    @include('partials.alerts')
                    <form>
                        <input type="hidden" wire:model="selected_id">

                        <!-- Código Carrera -->
                        <div class="mb-3">
                            <label for="codigo_carrera_update" class="form-label fw-semibold">
                                <i class="bi bi-hash text-primary me-1"></i>Código de Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-upc-scan text-muted"></i>
                                </span>
                                <input wire:model="codigo_carrera" type="text"
                                       class="form-control border-start-0 @error('codigo_carrera') is-invalid @enderror"
                                       id="codigo_carrera_update"
                                       placeholder="Ej: ING-001, MED-002">
                            </div>
                            @error('codigo_carrera')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre_update" class="form-label fw-semibold">
                                <i class="bi bi-mortarboard text-success me-1"></i>Nombre de la Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-card-text text-muted"></i>
                                </span>
                                <input wire:model="nombre" type="text"
                                       class="form-control border-start-0 @error('nombre') is-invalid @enderror"
                                       id="nombre_update"
                                       placeholder="Ej: Ingeniería en Sistemas">
                            </div>
                            @error('nombre')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Departamento -->
                        <div class="mb-3">
                            <label for="departamento_id_update" class="form-label fw-semibold">
                                <i class="bi bi-building text-info me-1"></i>Departamento
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-building-check text-muted"></i>
                                </span>
                                <select wire:model="departamento_id" id="departamento_id_update"
                                        class="form-select border-start-0 @error('departamento_id') is-invalid @enderror">
                                    <option value="">--Seleccione un Departamento--</option>
                                    @foreach($departamentos->sortBy('nombre') as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('departamento_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Modalidad -->
                                <div class="mb-3">
                                    <label for="modalidad_update" class="form-label fw-semibold">
                                        <i class="bi bi-laptop text-primary me-1"></i>Modalidad
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person-workspace text-muted"></i>
                                        </span>
                                        <select wire:model="modalidad" id="modalidad_update"
                                                class="form-select border-start-0 @error('modalidad') is-invalid @enderror">
                                            <option value="">--Seleccione Modalidad--</option>
                                            <option value="Presencial">Presencial</option>
                                            <option value="En línea">En línea</option>
                                        </select>
                                    </div>
                                    @error('modalidad')
                                        <div class="text-danger small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Sede -->
                                <div class="mb-3">
                                    <label for="sede_update" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt text-danger me-1"></i>Sede
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-pin-map text-muted"></i>
                                        </span>
                                        <select wire:model="sede" id="sede_update"
                                                class="form-select border-start-0 @error('sede') is-invalid @enderror">
                                            <option value="">--Seleccione Sede--</option>
                                            <option value="Latacunga">Latacunga</option>
                                            <option value="Santo Domingo">Santo Domingo</option>
                                            <option value="Sangolquí">Sangolquí</option>
                                        </select>
                                    </div>
                                    @error('sede')
                                        <div class="text-danger small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary px-4"
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" wire:click.prevent="update()"
                            class="btn text-white px-4"
                             style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Carrera
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Carrera -->
    <div wire:ignore.self class="modal fade" id="deleteDataModal" data-bs-backdrop="static" data-bs-keyboard="false"
         aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0"
                     style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <div class="w-100 text-center py-2">
                        <h5 class="modal-title fw-bold text-white mb-0" id="deleteDataModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; background: rgba(231, 76, 60, 0.1);">
                            <i class="bi bi-trash3 text-danger" style="font-size: 40px;"></i>
                        </div>
                    </div>
                    <h6 class="text-center mb-3">¿Está seguro de eliminar esta carrera?</h6>
                    <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                            <div>
                                <p class="mb-2">
                                    <strong>Esta acción eliminará permanentemente la carrera seleccionada.</strong>
                                </p>
                                <p class="mb-0 fw-bold text-danger">
                                    Esta acción es irreversible. Los datos no se podrán recuperar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button class="btn text-white px-4" wire:click="destroy()"
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="bi bi-trash me-2"></i>Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endcan
