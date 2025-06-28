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
                                id="search" placeholder="Search Tribunales">
                        </div>
                        <div class="btn btn-sm btn-info" wire:click="create()">
                            <i class="fa fa-plus"></i> Nueva Rubrica
                        </div>
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
                                        <td width="180" class="text-center"> {{-- Ajusta el width si es necesario para los 4 iconos --}}
                                            <div class="btn-group btn-group-sm" role="group"> {{-- btn-group-sm para botones más pequeños --}}
                                                {{-- BOTÓN PREVISUALIZAR --}}
                                                <button type="button" class="btn btn-secondary" {{-- Cambiado de btn-outline-secondary --}}
                                                    data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                    data-bs-placement="left" data-bs-html="true"
                                                    title="Previsualizar: {{ $row->nombre }}"
                                                    data-bs-content="{{ $this->generarHtmlPrevisualizacion($row->id) }}">
                                                    <i class="bi bi-eye-fill"></i> {{-- Usando -fill para un ícono más sólido si se prefiere --}}
                                                </button>

                                                {{-- BOTÓN COPIAR --}}
                                                <button type="button" class="btn btn-info" {{-- Cambiado de btn-outline-info --}}
                                                    wire:click="confirmCopy({{ $row->id }})"
                                                    title="Copiar Rúbrica">
                                                    <i class="bi bi-copy"></i>
                                                </button>

                                                {{-- BOTÓN EDITAR --}}
                                                <a href="{{ route('rubricas.edit', $row->id) }}" class="btn btn-primary"
                                                    {{-- Cambiado de btn-outline-primary --}} title="Editar Rúbrica">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                {{-- BOTÓN ELIMINAR --}}
                                                <button type="button" class="btn btn-danger" {{-- Cambiado de btn-outline-danger --}}
                                                    wire:click="confirmDelete({{ $row->id }})"
                                                    title="Eliminar Rúbrica">
                                                    <i class="bi bi-trash-fill"></i> {{-- Usando -fill --}}
                                                </button>
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
</div>
