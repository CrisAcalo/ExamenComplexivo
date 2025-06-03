<div>
    <div class="fs-3 fw-bold mb-4">
        <a href="{{ route('rubricas.') }}">Rúbricas</a> /
        <span>{{ $modoEdicion ? 'Editar Rúbrica: ' . $nombreRubrica : 'Crear Nueva Rúbrica' }}</span>
    </div>

    @include('partials.alerts') {{-- Para session()->flash('success') o 'danger' --}}
    @if ($errors->has('ponderacion_total'))
        <div class="alert alert-danger">{{ $errors->first('ponderacion_total') }}</div>
    @endif

    <form wire:submit.prevent="saveRubrica">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Información General de la Rúbrica</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="nombreRubrica" class="form-label">Nombre de la Rúbrica</label>
                    <input type="text" class="form-control @error('nombreRubrica') is-invalid @enderror"
                        id="nombreRubrica" wire:model.lazy="nombreRubrica"
                        placeholder="Ej: Rúbrica Evaluación Oral TI 2024S1">
                    @error('nombreRubrica')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Niveles de Calificación (Columnas)</h5>
            </div>
            <div class="card-body">
                @foreach ($nivelesCalificacion as $indexNivel => $nivel)
                    <div class="row align-items-center mb-2" wire:key="nivel-{{ $nivel['id_temporal'] }}">
                        <div class="col-md-5">
                            <label for="nivel_nombre_{{ $indexNivel }}" class="form-label visually-hidden">Nombre
                                Nivel</label>
                            <input type="text"
                                class="form-control @error('nivelesCalificacion.' . $indexNivel . '.nombre') is-invalid @enderror"
                                id="nivel_nombre_{{ $indexNivel }}"
                                wire:model.lazy="nivelesCalificacion.{{ $indexNivel }}.nombre"
                                placeholder="Ej: Muy Bueno">
                            @error('nivelesCalificacion.' . $indexNivel . '.nombre')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="nivel_valor_{{ $indexNivel }}"
                                class="form-label visually-hidden">Valor</label>
                            <input type="number" step="0.01"
                                class="form-control @error('nivelesCalificacion.' . $indexNivel . '.valor') is-invalid @enderror"
                                id="nivel_valor_{{ $indexNivel }}"
                                wire:model.lazy="nivelesCalificacion.{{ $indexNivel }}.valor" placeholder="Ej: 4">
                            @error('nivelesCalificacion.' . $indexNivel . '.valor')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-1">
                            @if (count($nivelesCalificacion) > 1)
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    wire:click="removeNivelCalificacion({{ $indexNivel }})"><i
                                        class="bi bi-trash-fill"></i></button>
                            @endif
                        </div>
                    </div>
                @endforeach
                <button type="button" class="btn btn-outline-success btn-sm mt-2"
                    wire:click="addNivelCalificacion()"><i class="bi bi-plus-lg"></i> Añadir Nivel de
                    Calificación</button>
            </div>
        </div>


        <h4>Componentes y Criterios de la Rúbrica</h4>
        @foreach ($componentes as $indexComponente => $componente)
            <div class="card mb-3" wire:key="componente-{{ $componente['id_temporal'] }}">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Componente {{ $indexComponente + 1 }}</h5>
                        @if (count($componentes) > 1)
                            <button type="button" class="btn btn-danger btn-sm"
                                wire:click="removeComponente({{ $indexComponente }})">Eliminar Componente</button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="componente_nombre_{{ $indexComponente }}" class="form-label">Nombre del
                                Componente</label>
                            <input type="text"
                                class="form-control @error('componentes.' . $indexComponente . '.nombre') is-invalid @enderror"
                                id="componente_nombre_{{ $indexComponente }}"
                                wire:model.lazy="componentes.{{ $indexComponente }}.nombre"
                                placeholder="Ej: Parte Escrita">
                            @error('componentes.' . $indexComponente . '.nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="componente_ponderacion_{{ $indexComponente }}" class="form-label">Ponderación
                                (%)</label>
                            <input type="number" step="0.01"
                                class="form-control @error('componentes.' . $indexComponente . '.ponderacion') is-invalid @enderror"
                                id="componente_ponderacion_{{ $indexComponente }}"
                                wire:model.lazy="componentes.{{ $indexComponente }}.ponderacion" placeholder="Ej: 60">
                            @error('componentes.' . $indexComponente . '.ponderacion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Criterio</th>
                                    @foreach ($nivelesCalificacion as $nivelIndex => $nivel)
                                        <th wire:key="header-comp-{{ $componente['id_temporal'] }}-nivel-{{ $nivel['id_temporal'] }}"
                                            class="text-center">
                                            {{ $nivel['nombre'] }} ({{ $nivel['valor'] }})
                                        </th>
                                    @endforeach
                                    <th style="width: 5%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($componente['criterios'] as $indexCriterio => $criterio)
                                    <tr wire:key="criterio-{{ $criterio['id_temporal'] }}">
                                        <td>
                                            <textarea
                                                class="form-control @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.nombre') is-invalid @enderror"
                                                wire:model.lazy="componentes.{{ $indexComponente }}.criterios.{{ $indexCriterio }}.nombre" rows="3"
                                                placeholder="Descripción del criterio"></textarea>
                                            @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio .
                                                '.nombre')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        @foreach ($nivelesCalificacion as $indexNivelCol => $nivel)
                                            <td
                                                wire:key="desc-{{ $criterio['id_temporal'] }}-{{ $nivel['id_temporal'] }}">
                                                <textarea
                                                    class="form-control @error('componentes.' . $indexComponente . '.criterios.' . $indexCriterio . '.descripciones_calificacion.' . $nivel['id_temporal']) is-invalid @enderror"
                                                    wire:model.lazy="componentes.{{ $indexComponente }}.criterios.{{ $indexCriterio }}.descripciones_calificacion.{{ $nivel['id_temporal'] }}"
                                                    rows="3"
                                                    placeholder="Descripción para {{ $criterio['nombre'] ?: 'este criterio' }} en {{ $nivel['nombre'] }}"></textarea>
                                                @error('componentes.' . $indexComponente . '.criterios.' .
                                                    $indexCriterio . '.descripciones_calificacion.' . $nivel['id_temporal'])
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        @endforeach
                                        <td class="text-center align-middle">
                                            @if (count($componente['criterios']) > 1)
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    wire:click="removeCriterio({{ $indexComponente }}, {{ $indexCriterio }})"><i
                                                        class="bi bi-trash-fill"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-info btn-sm mt-2"
                        wire:click="addCriterio({{ $indexComponente }})">
                        <i class="bi bi-plus-lg"></i> Añadir Criterio a este Componente
                    </button>
                </div>
            </div>
        @endforeach

        <button type="button" class="btn btn-success mt-3" wire:click="addComponente">
            <i class="bi bi-plus-lg"></i> Añadir Nuevo Componente a la Rúbrica
        </button>

        <hr>

        <div class="mt-4 mb-5">
            {{-- Cambiamos el texto del botón --}}
            <button type="submit" class="btn btn-primary px-4">
                {{ $modoEdicion ? 'Actualizar Rúbrica' : 'Guardar Rúbrica' }}
            </button>
            <a href="{{ route('rubricas.') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
