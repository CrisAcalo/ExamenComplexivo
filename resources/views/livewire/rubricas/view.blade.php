@section('title', __('Rubricas'))
<div class="container-fluid p-0">
    <div class="fs-3 fw-bold mb-4">
        Rúbricas
    </div>

    @include('partials.alerts')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <h3><i class="fab fa-laravel text-info"></i>
                                Listado de Rubricas</h3>
                        </div>
                        <div>
                            <input wire:model='keyWord' type="text" class="form-control" name="search"
                                id="search" placeholder="Buscar Rúbricas">
                        </div>
                        @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                            <div class="btn btn-sm btn-info" wire:click="create()">
                                <i class="fa fa-plus"></i> Nueva Rubrica
                            </div>
                        @else
                            <span class="text-muted small">Solo lectura</span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @include('livewire.rubricas.modals')
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <td class="text-center">ACCIONES</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rubricas as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->nombre }}</td>
                                        <td width="180" class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                {{-- BOTÓN PREVISUALIZAR --}}
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                    data-bs-placement="left" data-bs-html="true"
                                                    title="Previsualizar: {{ $row->nombre }}"
                                                    data-bs-content="{{ $this->generarHtmlPrevisualizacion($row->id) }}">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>

                                                {{-- BOTÓN COPIAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <button type="button" class="btn btn-info"
                                                        wire:click="confirmCopy({{ $row->id }})"
                                                        title="Copiar Rúbrica">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                @endif

                                                {{-- BOTÓN EDITAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <a href="{{ route('rubricas.edit', $row->id) }}" class="btn btn-primary"
                                                        title="Editar Rúbrica">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endif

                                                {{-- BOTÓN ELIMINAR --}}
                                                @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
                                                    <button type="button" class="btn btn-danger"
                                                        wire:click="confirmDelete({{ $row->id }})"
                                                        title="Eliminar Rúbrica">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                @endif

                                                @if(!auth()->user()->can('gestionar rubricas') && !auth()->user()->can('gestionar plantillas rubricas'))
                                                    <span class="text-muted small">Sin permisos</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="3">No se encontraron rúbricas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="float-end">{{ $rubricas->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Si tu layout tiene @stack('scripts') --}}
    {{-- <script>
        function initializeSpecificPopovers(container) {
            const popoverTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.forEach(function(popoverTriggerEl) {
                // Solo inicializar si no tiene ya una instancia de popover
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    new bootstrap.Popover(popoverTriggerEl, {
                        sanitize: false, // Ya discutimos la seguridad de esto
                        // container: 'body' // Opcional: A veces ayuda con problemas de z-index o clipping
                    });
                }
            });
        }

        document.addEventListener('livewire:load', function() {
            initializeSpecificPopovers(document); // Inicializar en la carga inicial para todo el documento
        });

        Livewire.hook('message.processed', (message, component) => {
            // Después de que Livewire actualice el DOM, buscar nuevos popovers o re-evaluar
            // El contenedor 'component.el' es el elemento raíz del componente Livewire que se actualizó
            if (component && component.el) {
                initializeSpecificPopovers(component.el);
            } else {
                initializeSpecificPopovers(document); // Fallback por si acaso
            }
        });
    </script> --}}

    @if(auth()->user()->can('gestionar rubricas') || auth()->user()->can('gestionar plantillas rubricas'))
        <!-- Modal para confirmar eliminar -->
        <div wire:ignore.self class="modal fade" id="eliminarRubricaModal" tabindex="-1" aria-labelledby="eliminarRubricaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="eliminarRubricaModalLabel">Confirmar Eliminación</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que quieres eliminar esta rúbrica?</p>
                        <p><strong>Nombre:</strong> {{ $rubricaSeleccionada->nombre ?? '' }}</p>
                        <p><strong>Descripción:</strong> {{ $rubricaSeleccionada->descripcion ?? '' }}</p>
                        <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" wire:click="destroy">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para confirmar copiar -->
        <div wire:ignore.self class="modal fade" id="copiarRubricaModal" tabindex="-1" aria-labelledby="copiarRubricaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="copiarRubricaModalLabel">Confirmar Copiar</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que quieres copiar esta rúbrica?</p>
                        <p><strong>Nombre:</strong> {{ $rubricaSeleccionada->nombre ?? '' }}</p>
                        <p><strong>Descripción:</strong> {{ $rubricaSeleccionada->descripcion ?? '' }}</p>
                        <p class="text-info"><strong>Se creará una copia con el nombre "Copia de [nombre original]".</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" wire:click="copyRubrica">Copiar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
