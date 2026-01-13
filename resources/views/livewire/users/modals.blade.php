<!-- Add Modal -->
@php
    $user = auth()->user();
    $puedeGestionar = (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && $user->can('gestionar usuarios'))
                     || \App\Helpers\ContextualAuth::hasActiveAssignments($user);
@endphp
@if($puedeGestionar)
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="createDataModalLabel">
                        Crear Nuevo Docente
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
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Nombres -->
                            <div class="mb-3">
                                <label for="name_create" class="form-label fw-semibold">
                                    <i class="bi bi-person text-primary me-1"></i>Nombres
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-fill text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control border-start-0 @error('name') is-invalid @enderror"
                                           wire:model="name"
                                           id="name_create"
                                           placeholder="Ej: Juan Carlos">
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Apellidos -->
                            <div class="mb-3">
                                <label for="lastname_create" class="form-label fw-semibold">
                                    <i class="bi bi-person-badge text-primary me-1"></i>Apellidos
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-badge-fill text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control border-start-0 @error('lastname') is-invalid @enderror"
                                           wire:model="lastname"
                                           id="lastname_create"
                                           placeholder="Ej: Pérez López">
                                </div>
                                @error('lastname')
                                    <div class="text-danger small mt-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label for="email_create" class="form-label fw-semibold">
                            <i class="bi bi-envelope text-success me-1"></i>Correo Electrónico
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-envelope-at text-muted"></i>
                            </span>
                            <input type="email" 
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   wire:model="email"
                                   id="email_create"
                                   placeholder="Ej: nombre@ejemplo.com">
                        </div>
                        @error('email')
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
                            <select wire:model="departamento_id"
                                    class="form-select border-start-0 @error('departamento_id') is-invalid @enderror"
                                    id="departamento_id_create">
                                <option value="">Seleccione un departamento</option>
                                @foreach($departamentosDisponibles as $depto)
                                    <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
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
                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="password_create" class="form-label fw-semibold">
                                    <i class="bi bi-lock text-warning me-1"></i>Contraseña
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-key-fill text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 @error('password') is-invalid @enderror"
                                           wire:model="password"
                                           id="password_create"
                                           placeholder="***************">
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Confirmar Contraseña -->
                            <div class="mb-3">
                                <label for="password_confirmation_create" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill text-warning me-1"></i>Confirmar Contraseña
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-shield-check text-muted"></i>
                                    </span>
                                    <input id="password_confirmation_create" 
                                           type="password" 
                                           class="form-control border-start-0"
                                           wire:model="password_confirmation" 
                                           placeholder="***************">
                                </div>
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
                    <i class="bi bi-check-circle me-2"></i>Guardar Docente
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Edit Modal - Solo Super Admin -->
@can('gestionar usuarios')
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="updateModalLabel">
                        Actualizar Docente
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
                    <input type="hidden" wire:model="selected_id">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="name_update" class="form-label fw-semibold">
                            <i class="bi bi-person text-primary me-1"></i>Nombre Completo
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person-fill text-muted"></i>
                            </span>
                            <input wire:model="name" type="text"
                                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   id="name_update"
                                   placeholder="Nombre completo">
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label for="email_update" class="form-label fw-semibold">
                            <i class="bi bi-envelope text-success me-1"></i>Correo Electrónico
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-envelope-at text-muted"></i>
                            </span>
                            <input wire:model="email" type="email"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   id="email_update"
                                   placeholder="Correo electrónico">
                        </div>
                        @error('email')
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
                    <i class="bi bi-check-circle me-2"></i>Actualizar Docente
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- Delete Modal --}}
@can('gestionar usuarios')
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            @if ($usuarioFounded)
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
                    <h6 class="text-center mb-3">¿Está seguro de eliminar al docente {{ $usuarioFounded->name }}?</h6>
                    <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                            <div>
                                <p class="mb-2">
                                    <strong>Esta acción eliminará permanentemente el docente seleccionado.</strong>
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
                    <button class="btn text-white px-4" wire:click="destroy({{ $usuarioFounded->id }})"
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="bi bi-trash me-2"></i>Sí, Eliminar
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endcan

<!-- Import Profesores Modal -->
@php
    $puedeImportar = (\App\Helpers\ContextualAuth::isSuperAdminOrAdmin($user) && $user->can('importar profesores'))
                    || \App\Helpers\ContextualAuth::hasActiveAssignments($user);
@endphp
@if($puedeImportar)
<div wire:ignore.self class="modal fade" id="importProfesoresModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="importProfesoresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="importProfesoresModalLabel">
                        Importar Profesores (Docentes) desde Excel
                    </h5>
                    <hr>
                </div>
                <button wire:click.prevent="resetImport()" type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="position: absolute; right: 20px; top: 20px;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <form wire:submit.prevent="importarProfesores">
                    <!-- Departamento -->
                    <div class="mb-3">
                        <label for="departamento_id_import" class="form-label fw-semibold">
                            <i class="bi bi-building text-info me-1"></i>Departamento
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-building-check text-muted"></i>
                            </span>
                            <select wire:model="departamento_id"
                                    class="form-select border-start-0 @error('departamento_id') is-invalid @enderror"
                                    id="departamento_id_import">
                                <option value="">Seleccione un departamento</option>
                                @foreach($departamentosDisponibles as $depto)
                                    <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-info-circle me-1"></i>Todos los profesores importados se asignarán a este departamento
                        </small>
                        @error('departamento_id')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Archivo Excel -->
                    <div class="mb-3">
                        <label for="archivoExcelProfesores" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-excel text-success me-1"></i>Seleccione el archivo Excel
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-paperclip text-muted"></i>
                            </span>
                            <input type="file"
                                   class="form-control border-start-0 @error('archivoExcelProfesores') is-invalid @enderror"
                                   id="archivoExcelProfesores" 
                                   wire:model="archivoExcelProfesores"
                                   accept=".xlsx,.xls">
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-file-earmark me-1"></i>Formatos permitidos: .xlsx, .xls
                        </small>
                        @error('archivoExcelProfesores')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Información del formato -->
                    <div class="alert border-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-primary me-2 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Formato esperado del archivo:
                                </h6>
                                <ul class="mb-0 small">
                                    <li>Los datos deben empezar en la <strong>fila 7</strong>.</li>
                                    <li>La <strong>fila 6</strong> debe contener los encabezados: <code>ID ESPE</code>, <code>CÉDULA</code>, <code>APELLIDOS</code>, <code>NOMBRES</code>, <code>CORREO</code>.</li>
                                    <li>La <strong>CÉDULA</strong> se usará como <strong>contraseña inicial</strong>.</li>
                                    <li>Se asignará el rol <strong>"Docente"</strong> por defecto a los nuevos docentes.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" 
                                class="btn text-white px-4" 
                                style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);"
                                wire:loading.attr="disabled"
                                wire:target="importarProfesores, archivoExcelProfesores">
                            <span wire:loading wire:target="importarProfesores"
                                  class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload me-2" wire:loading.remove wire:target="importarProfesores"></i>
                            <span wire:loading.remove wire:target="importarProfesores">Importar Profesores</span>
                            <span wire:loading wire:target="importarProfesores">Importando...</span>
                        </button>
                    </div>
                </form>

                {{-- Resultados de la importación --}}
                @if ($importFinished)
                    <div class="mt-4">
                        <div class="alert alert-success border-0" style="background: rgba(76, 175, 80, 0.1);">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle text-success me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="fw-bold text-success mb-0">Resultados de la Importación</h6>
                                </div>
                            </div>
                        </div>
                        {{-- Mensaje de éxito/error ya se muestra con @include('partials.alerts') en la vista principal --}}
                        @if (!empty($importErrors))
                            <div class="alert alert-warning border-0" style="background: rgba(255, 152, 0, 0.1);">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-exclamation-triangle text-warning me-2 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-warning mb-2">Errores encontrados durante la importación</h6>
                                        <p class="mb-2 small">La importación finalizó, pero se encontraron los siguientes errores (las filas con errores no se importaron):</p>
                                        <ul class="list-group shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                            @foreach ($importErrors as $error)
                                                <li class="list-group-item list-group-item-danger small">
                                                    <i class="bi bi-x-circle me-1"></i>{{ $error }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 bg-light">
                <button wire:click.prevent="resetImport()" type="button" class="btn btn-secondary px-4"
                        data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
