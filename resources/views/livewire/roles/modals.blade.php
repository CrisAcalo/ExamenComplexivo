<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="createDataModalLabel">
                        Crear Nuevo Rol
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
                    <!-- Nombre del Rol -->
                    <div class="mb-3">
                        <label for="name_create" class="form-label fw-semibold">
                            <i class="bi bi-shield-check text-primary me-1"></i>Nombre del Rol
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-tag text-muted"></i>
                            </span>
                            <input wire:model="name" type="text" 
                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                   id="name_create" 
                                   placeholder="Ej: Administrador, Supervisor, Docente">
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-info-circle me-1"></i>Use nombres descriptivos que identifiquen claramente el rol
                        </small>
                        @error('name')
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
                    <i class="bi bi-check-circle me-2"></i>Guardar Rol
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div wire:ignore.self class="modal fade" id="updateDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="updateModalLabel">
                        Actualizar Rol
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
                    
                    <!-- Nombre del Rol -->
                    <div class="mb-3">
                        <label for="name_update" class="form-label fw-semibold">
                            <i class="bi bi-shield-check text-primary me-1"></i>Nombre del Rol
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-tag text-muted"></i>
                            </span>
                            <input wire:model="name" type="text" 
                                   class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                   id="name_update" 
                                   placeholder="Nombre del rol">
                        </div>
                        <small class="text-muted ms-1">
                            <i class="bi bi-info-circle me-1"></i>Use nombres descriptivos que identifiquen claramente el rol
                        </small>
                        @error('name')
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
                    <i class="bi bi-check-circle me-2"></i>Actualizar Rol
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div wire:ignore.self class="modal fade deleteModal" id="deleteDataModal" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            @if ($rolEncontrado)
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
                    <h6 class="text-center mb-3">¿Está seguro de eliminar el rol "{{ $rolEncontrado->name }}"?</h6>
                    <div class="alert alert-danger border-0" style="background: rgba(231, 76, 60, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-danger me-2 mt-1"></i>
                            <div>
                                <p class="mb-2">
                                    <strong>Esta acción eliminará permanentemente el rol seleccionado.</strong>
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
                    <button class="btn text-white px-4" wire:click="destroy({{ $rolEncontrado->id }})"
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="bi bi-trash me-2"></i>Sí, Eliminar
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal Permision -->
<div wire:ignore.self class="modal fade" id="updatePermisionsModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100">
                    <h5 class="modal-title fw-bold" id="updateModalLabel">
                        <i class="bi bi-key-fill text-warning me-2"></i>Asignar Permisos al Rol
                    </h5>
                    <hr>
                </div>
                <button wire:click.prevent="cancel()" type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="position: absolute; right: 20px; top: 20px;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <form action="{{ route('roles.updatePermisos', encrypt($selected_id)) }}" id="update_product_info"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        @php
                            $totalSections = count($sections);
                            $columns = 3;
                            $sectionsPerColumn = ceil($totalSections / $columns);
                            $sectionsCounter = 0;
                        @endphp
                        @for ($i = 0; $i < $columns; $i++)
                            <div class="col">
                                @for ($j = 0; $j < $sectionsPerColumn && $sectionsCounter < $totalSections; $j++)
                                    @php
                                        $section = key($sections);
                                        $permissions = current($sections);
                                        next($sections);
                                        $sectionsCounter++;
                                    @endphp
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header border-0 py-2" 
                                             style="background: linear-gradient(135deg, #2d7a5f 0%, #3d8e72ff 100%);">
                                            <h6 class="mb-0 fw-bold text-white">
                                                <i class="bi bi-folder-fill me-2"></i>{{ $section }}
                                            </h6>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            @foreach ($permissions as $permission)
                                                <li class="list-group-item list-group-item-action" 
                                                    style="border-left: 3px solid #3d8e72ff; transition: all 0.3s;"
                                                    onmouseover="this.style.background='#f8f9fa'"
                                                    onmouseout="this.style.background='white'">
                                                    <div class="form-check">
                                                        <input @if (in_array($permission['id'], $permisosSeleccionados)) checked @endif
                                                            name="permisos[]" value="{{ $permission['name'] }}"
                                                            class="form-check-input" type="checkbox"
                                                            id="permiso{{ $permission['id'] }}">
                                                        <label class="form-check-label w-100" 
                                                            for="permiso{{ $permission['id'] }}">
                                                            <i class="bi bi-unlock-fill text-success me-2"></i>{{ $permission['name'] }}
                                                        </label>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                @endfor

                            </div>
                        @endfor
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 bg-light">
                <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary px-4"
                        data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="submit" 
                        class="btn text-white px-4"
                        style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);"
                        form="update_product_info">
                    <i class="bi bi-check-circle me-2"></i>Guardar Permisos
                </button>
            </div>
        </div>
    </div>
</div>



