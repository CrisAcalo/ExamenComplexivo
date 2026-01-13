@php
    $user = auth()->user();
    $canManageCarrerasPeriodos = $user->hasRole('Super Admin') && $user->hasPermissionTo('asignar carrera a periodo');
@endphp

@if($canManageCarrerasPeriodos)
    <!-- Modal: Asignar Carrera a Período -->
    <div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="createDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div class="w-100">
                        <h5 class="modal-title fw-bold" id="createDataModalLabel">
                            <i class="bi bi-plus-circle me-2 text-success"></i>Asignar Carrera a Período
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <form>
                        <!-- Carrera -->
                        <div class="mb-3">
                            <label for="carrera_id" class="form-label fw-semibold">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>
                                <select wire:model.live="carrera_id" id="carrera_id"
                                        class="form-select border-start-0 @error('carrera_id') is-invalid @enderror">
                                    <option value="">-- Seleccione una carrera --</option>
                                    @foreach ($carreras->sortBy('nombre') as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('carrera_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Checkbox: Mostrar todos los docentes -->
                        @if($carrera_id)
                            <div class="alert alert-info border-0 mb-3" style="background-color: rgba(13, 202, 240, 0.1);">
                                <div class="form-check">
                                    <input wire:model.live="mostrar_todos_docentes" type="checkbox"
                                           class="form-check-input" id="mostrar_todos_docentes">
                                    <label class="form-check-label" for="mostrar_todos_docentes">
                                        <strong>Mostrar docentes de todos los departamentos</strong>
                                        <small class="text-muted d-block">
                                            Por defecto se muestran solo docentes del departamento de la carrera seleccionada
                                        </small>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- Director de Carrera -->
                        <div class="mb-3">
                            <label for="director_id" class="form-label fw-semibold">
                                <i class="bi bi-person-badge text-success me-1"></i>Director de Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person-circle text-muted"></i>
                                </span>
                                <select wire:model="director_id" id="director_id"
                                        class="form-select border-start-0 @error('director_id') is-invalid @enderror"
                                        @if($users_filtrados->isEmpty()) disabled @endif>
                                    <option value="">-- Seleccione un director --</option>
                                    @if($users_filtrados->isNotEmpty())
                                        @php
                                            $usuariosAgrupados = $users_filtrados->sortBy('name')->groupBy(function($user) {
                                                return $user->departamento ? $user->departamento->nombre : 'Sin Departamento';
                                            });
                                        @endphp
                                        @foreach ($usuariosAgrupados as $departamento => $usuarios)
                                            <optgroup label="📁 {{ $departamento }}">
                                                @foreach ($usuarios as $user)
                                                <!-- mostrar nombre apelleido -->
                                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if($carrera_id && $users_filtrados->isEmpty())
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No hay docentes disponibles del departamento de esta carrera
                                </small>
                            @elseif(!$carrera_id)
                                <small class="text-muted">Primero seleccione una carrera</small>
                            @endif
                            @error('director_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Docente de Apoyo -->
                        <div class="mb-3">
                            <label for="docente_apoyo_id" class="form-label fw-semibold">
                                <i class="bi bi-person-check text-info me-1"></i>Docente de Apoyo
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person-plus text-muted"></i>
                                </span>
                                <select wire:model="docente_apoyo_id" id="docente_apoyo_id"
                                        class="form-select border-start-0 @error('docente_apoyo_id') is-invalid @enderror"
                                        @if($users_filtrados->isEmpty()) disabled @endif>
                                    <option value="">-- Seleccione docente de apoyo --</option>
                                    @if($users_filtrados->isNotEmpty())
                                        @php
                                            $usuariosApoyoAgrupados = $users_filtrados->filter(function($user) {
                                                return $user->id != $this->director_id;
                                            })->sortBy('name')->groupBy(function($user) {
                                                return $user->departamento ? $user->departamento->nombre : 'Sin Departamento';
                                            });
                                        @endphp
                                        @foreach ($usuariosApoyoAgrupados as $departamento => $usuarios)
                                            <optgroup label="📁 {{ $departamento }}">
                                                @foreach ($usuarios as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if($carrera_id && $users_filtrados->isEmpty())
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No hay docentes disponibles del departamento de esta carrera
                                </small>
                            @elseif(!$carrera_id)
                                <small class="text-muted">Primero seleccione una carrera</small>
                            @endif
                            @error('docente_apoyo_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
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
                        <i class="bi bi-check-circle me-2"></i>Asignar Carrera
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Asignación -->
    <div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="updateDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div class="w-100">
                        <h5 class="modal-title fw-bold" id="updateDataModalLabel">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Asignación
                        </h5>
                        <hr>
                    </div>
                    <button wire:click.prevent="cancel()" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="position: absolute; right: 20px; top: 20px;"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <form>
                        <input type="hidden" wire:model="selected_id">

                        <!-- Carrera -->
                        <div class="mb-3">
                            <label for="carrera_id_edit" class="form-label fw-semibold">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>
                                <select wire:model.live="carrera_id" id="carrera_id_edit"
                                        class="form-select border-start-0 @error('carrera_id') is-invalid @enderror">
                                    <option value="">-- Seleccione una carrera --</option>
                                    @foreach ($carreras->sortBy('nombre') as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('carrera_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Checkbox: Mostrar todos los docentes -->
                        @if($carrera_id)
                            <div class="alert alert-info border-0 mb-3" style="background-color: rgba(13, 202, 240, 0.1);">
                                <div class="form-check">
                                    <input wire:model.live="mostrar_todos_docentes" type="checkbox"
                                           class="form-check-input" id="mostrar_todos_docentes_edit">
                                    <label class="form-check-label" for="mostrar_todos_docentes_edit">
                                        <strong>Mostrar docentes de todos los departamentos</strong>
                                        <small class="text-muted d-block">
                                            Por defecto se muestran solo docentes del departamento de la carrera seleccionada
                                        </small>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- Director de Carrera -->
                        <div class="mb-3">
                            <label for="director_id_edit" class="form-label fw-semibold">
                                <i class="bi bi-person-badge text-success me-1"></i>Director de Carrera
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person-circle text-muted"></i>
                                </span>
                                <select wire:model="director_id" id="director_id_edit"
                                        class="form-select border-start-0 @error('director_id') is-invalid @enderror"
                                        @if($users_filtrados->isEmpty()) disabled @endif>
                                    <option value="">-- Seleccione un director --</option>
                                    @if($users_filtrados->isNotEmpty())
                                        @php
                                            $usuariosAgrupadosEdit = $users_filtrados->sortBy('name')->groupBy(function($user) {
                                                return $user->departamento ? $user->departamento->nombre : 'Sin Departamento';
                                            });
                                        @endphp
                                        @foreach ($usuariosAgrupadosEdit as $departamento => $usuarios)
                                            <optgroup label="📁 {{ $departamento }}">
                                                @foreach ($usuarios as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if($carrera_id && $users_filtrados->isEmpty())
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No hay docentes disponibles del departamento de esta carrera
                                </small>
                            @elseif(!$carrera_id)
                                <small class="text-muted">Primero seleccione una carrera</small>
                            @endif
                            @error('director_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Docente de Apoyo -->
                        <div class="mb-3">
                            <label for="docente_apoyo_id_edit" class="form-label fw-semibold">
                                <i class="bi bi-person-check text-info me-1"></i>Docente de Apoyo
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person-plus text-muted"></i>
                                </span>
                                <select wire:model="docente_apoyo_id" id="docente_apoyo_id_edit"
                                        class="form-select border-start-0 @error('docente_apoyo_id') is-invalid @enderror"
                                        @if($users_filtrados->isEmpty()) disabled @endif>
                                    <option value="">-- Seleccione docente de apoyo --</option>
                                    @if($users_filtrados->isNotEmpty())
                                        @php
                                            $usuariosApoyoAgrupadosEdit = $users_filtrados->filter(function($user) {
                                                return $user->id != $this->director_id;
                                            })->sortBy('name')->groupBy(function($user) {
                                                return $user->departamento ? $user->departamento->nombre : 'Sin Departamento';
                                            });
                                        @endphp
                                        @foreach ($usuariosApoyoAgrupadosEdit as $departamento => $usuarios)
                                            <optgroup label="📁 {{ $departamento }}">
                                                @foreach ($usuarios as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if($carrera_id && $users_filtrados->isEmpty())
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No hay docentes disponibles del departamento de esta carrera
                                </small>
                            @elseif(!$carrera_id)
                                <small class="text-muted">Primero seleccione una carrera</small>
                            @endif
                            @error('docente_apoyo_id')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
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
                        <i class="bi bi-check-circle me-2"></i>Actualizar Asignación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Asignación -->
    <div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
         data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            @if ($founded)
                <div class="modal-content border-0 shadow-lg">
                    <!-- Header -->
                    <div class="modal-header border-0"
                         style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="w-100 text-center py-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="staticBackdropLabel">
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
                        <h6 class="text-center mb-3">¿Está seguro de eliminar esta asignación?</h6>
                        <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                                <div>
                                    <p class="mb-0 fw-bold text-danger">
                                        Los datos no se podrán recuperar. Esta acción es irreversible.
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
                        <button class="btn text-white px-4" wire:click="destroy({{ $founded->id }})"
                                style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="bi bi-trash me-2"></i>Sí, Eliminar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
