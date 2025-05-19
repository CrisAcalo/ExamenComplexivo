<!-- Add Modal -->
<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">Crear nuevo Rol</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="name"></label>
                        <input wire:model="name" type="text" class="form-control" id="name" placeholder="Name">
                        @error('name')
                            <span class="error text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">Guardar</button>
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
                <h5 class="modal-title" id="updateModalLabel">Actualizar Rol</h5>
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
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @if ($rolEncontrado)
                <div class="modal-header bg-danger text-light">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">¿Está seguro
                        de eliminar el rol {{ $rolEncontrado->name }}?
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
                    <button class="btn btn-danger" wire:click="destroy({{ $rolEncontrado->id }})">Eliminar</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal Permision -->
<div wire:ignore.self class="modal fade" id="updatePermisionsModal" data-bs-backdrop="static" tabindex="-1"
    role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Asignar Permisos</h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                                    <div class="card mb-3">
                                        <div class="card-header bg-dark text-light fw-semibold">{{ $section }}</div>
                                        <ul class="list-group list-group-flush">
                                            @foreach ($permissions as $permission)
                                                <li class="list-group-item list-group-item-action list-group-item-light">
                                                    <input @if (in_array($permission['id'], $permisosSeleccionados)) checked @endif
                                                        name="permisos[]" value="{{ $permission['name'] }}"
                                                        class="form-check-input me-1" type="checkbox"
                                                        id="permiso{{ $permission['id'] }}">
                                                    <label class="form-check-label stretched-link"
                                                        for="permiso{{ $permission['id'] }}">
                                                        {{ $permission['name'] }}
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                @endfor

                            </div>
                        @endfor
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click.prevent="cancel()" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-info">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



