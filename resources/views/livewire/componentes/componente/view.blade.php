<div>
    @include('partials.alerts')

    <!-- Formulario para añadir criterios -->
    <div class="card p-4 mb-4">
        <form wire:submit.prevent="storeCriterio">
            <div class="d-flex align-items-start w-100">
                <div class="flex-grow-1 me-2">
                    <input type="text" class="form-control @error('nombreCriterio') is-invalid @enderror"
                        wire:model="nombreCriterio" placeholder="Nombre del criterio...">
                    @error('nombreCriterio')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary" style="flex-shrink: 0;">
                    Añadir Criterio
                </button>
            </div>
        </form>
    </div>

    <!-- Controles para columnas -->
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <button wire:click="agregarColumna" class="btn btn-sm btn-success me-2">
                <i class="bi bi-plus-lg"></i> Añadir Columna
            </button>
            <button wire:click="eliminarColumna" class="btn btn-sm btn-danger">
                <i class="bi bi-dash-lg"></i> Quitar Columna
            </button>
        </div>
        <div>
            <span class="badge bg-primary">Columnas: {{ $columnas }}</span>
        </div>
    </div>

    <!-- Tabla de criterios y calificaciones -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th style="width: 30%">Criterio</th>
                    @for ($i = 0; $i < $columnas; $i++)
                        <th>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control form-control-sm mb-1"
                                    wire:model="nuevasCalificaciones.{{ $i }}.nombre"
                                    placeholder="Nombre calificacion">
                                <input type="number" class="form-control form-control-sm"
                                    wire:model="nuevasCalificaciones.{{ $i }}.valor" placeholder="Valor">
                            </div>
                        </th>
                    @endfor
                    <th style="width: 100px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($criterios as $criterio)
                    <tr>
                        <td>{{ $criterio->criterio }}</td>

                        @for ($i = 0; $i < $columnas; $i++)
                            <td>
                                <input type="text"
                                    wire:model="calificaciones.{{ $criterio->id }}.{{ $i }}.nombre"
                                    class="form-control form-control-sm mb-1 @error('calificaciones.' . $criterio->id . '.' . $i . '.nombre') is-invalid @enderror"
                                    placeholder="Nombre">

                                <input type="number"
                                    wire:model="calificaciones.{{ $criterio->id }}.{{ $i }}.valor"
                                    class="form-control form-control-sm mb-1 @error('calificaciones.' . $criterio->id . '.' . $i . '.valor') is-invalid @enderror"
                                    placeholder="Valor">

                                <textarea wire:model="calificaciones.{{ $criterio->id }}.{{ $i }}.descripcion"
                                    class="form-control form-control-sm" rows="2" placeholder="Descripcion"></textarea>

                                @error('calificaciones.' . $criterio->id . '.' . $i . '.nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('calificaciones.' . $criterio->id . '.' . $i . '.valor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        @endfor

                        <td>
                            <button wire:click="guardarCalificaciones({{ $criterio->id }})"
                                class="btn btn-sm btn-success">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
