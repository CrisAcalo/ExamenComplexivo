@section('title', __('Plantillas de Acta (Word)'))
<div class="container-fluid p-0">
    <!-- Banner Verde ESPE -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                        <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                             style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                    @else
                        <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                            <i class="bi bi-file-earmark-word fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            PLANTILLAS DE ACTA
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de plantillas Word con variables dinámicas
                        </p>
                    </div>
                </div>
                <div>
                    <button data-bs-toggle="modal" data-bs-target="#uploadModal" class="btn btn-lg text-white"
                            style="border: 2px solid white; background: transparent; transition: all 0.3s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-cloud-upload me-2"></i>Subir Plantilla Word
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <!-- Header con Buscador -->
                <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                                <i class="bi bi-list-ul me-2"></i>Listado de Plantillas
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input wire:model.live="keyWord" type="text"
                                       class="form-control border-start-0"
                                       placeholder="Buscar por nombre..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card-body p-0">
                    @include('livewire.plantillas-acta-word.modals')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>
                                        <i class="bi bi-file-earmark-word me-1"></i>Nombre
                                    </th>
                                    <th style="width: 200px;">
                                        <i class="bi bi-file-text me-1"></i>Archivo
                                    </th>
                                    <th style="width: 150px;" class="text-center">
                                        <i class="bi bi-toggle-on me-1"></i>Estado
                                    </th>
                                    <th style="width: 200px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plantillas as $plantilla)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $plantilla->nombre }}</strong>
                                            @if($plantilla->descripcion)
                                                <br><small class="text-muted">{{ Str::limit($plantilla->descripcion, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ basename($plantilla->archivo_path) }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($plantilla->activa)
                                                <span class="badge bg-success px-3 py-2">
                                                    <i class="bi bi-check-circle me-1"></i>Activa
                                                </span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">
                                                    <i class="bi bi-x-circle me-1"></i>Inactiva
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @if(!$plantilla->activa)
                                                    <button wire:click="activar({{ $plantilla->id }})"
                                                            class="btn btn-sm btn-success"
                                                            title="Activar plantilla">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @else
                                                    <button wire:click="desactivar({{ $plantilla->id }})"
                                                            class="btn btn-sm btn-danger"
                                                            title="Desactivar plantilla">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif

                                                @if(!$plantilla->activa)
                                                    <button wire:click="confirmDelete({{ $plantilla->id }})"
                                                            class="btn btn-sm btn-danger"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No hay plantillas registradas. Sube tu primera plantilla Word.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                @if($plantillas->hasPages())
                    <div class="card-footer bg-white border-top-0">
                        {{ $plantillas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
