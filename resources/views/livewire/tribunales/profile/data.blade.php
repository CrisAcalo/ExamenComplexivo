<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-info-circle-fill text-primary"></i> Datos del Tribunal</h5>
        {{-- El Gate 'editar-datos-basicos-este-tribunal-como-presidente' o un permiso de Admin general --}}
        @if ($usuarioPuedeEditarDatosTribunal)
            <button class="btn btn-sm {{ $modoEdicionTribunal ? 'btn-secondary' : 'btn-outline-primary' }}"
                wire:click="toggleModoEdicionTribunal">
                <i class="bi {{ $modoEdicionTribunal ? 'bi-x-circle' : 'bi-pencil-square' }}"></i>
                {{ $modoEdicionTribunal ? 'Cancelar Edición' : 'Editar Datos' }}
            </button>
        @endif
    </div>
    <div class="card-body">
        @if ($modoEdicionTribunal && $usuarioPuedeEditarDatosTribunal)
            {{-- Formulario de Edición (similar a como lo tenías) --}}
            <form wire:submit.prevent="actualizarDatosTribunal">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="fecha_edit" class="form-label">Fecha</label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha_edit"
                            wire:model.defer="fecha">
                        @error('fecha')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_inicio_edit" class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control @error('hora_inicio') is-invalid @enderror"
                            id="hora_inicio_edit" wire:model.defer="hora_inicio">
                        @error('hora_inicio')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_fin_edit" class="form-label">Hora Fin</label>
                        <input type="time" class="form-control @error('hora_fin') is-invalid @enderror"
                            id="hora_fin_edit" wire:model.defer="hora_fin">
                        @error('hora_fin')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estudiante</label>
                        <input type="text" class="form-control"
                            value="{{ $tribunal->estudiante->nombres_completos_id }}" readonly disabled>
                    </div>
                </div>
                <h6 class="mt-3">Miembros del Tribunal</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="presidente_id_edit" class="form-label">Presidente</label>
                        <select wire:model.defer="presidente_id" id="presidente_id_edit"
                            class="form-select @error('presidente_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if ($prof->id == $integrante1_id || $prof->id == $integrante2_id) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('presidente_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante1_id_edit" class="form-label">Integrante 1</label>
                        <select wire:model.defer="integrante1_id" id="integrante1_id_edit"
                            class="form-select @error('integrante1_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if ($prof->id == $presidente_id || $prof->id == $integrante2_id) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante1_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante2_id_edit" class="form-label">Integrante 2</label>
                        <select wire:model.defer="integrante2_id" id="integrante2_id_edit"
                            class="form-select @error('integrante2_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if ($prof->id == $presidente_id || $prof->id == $integrante1_id) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante2_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-save"></i> Guardar
                    Cambios</button>
            </form>
        @else
            {{-- Modo Visualización de Datos del Tribunal --}}
            <div class="row">
                <div class="col-md-3">
                    <p><strong>Estudiante:</strong><br>{{ $tribunal->estudiante->nombres_completos_id }}
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong>Fecha:</strong><br>{{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong>Hora
                            Inicio:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong>Hora
                            Fin:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}
                    </p>
                </div>
            </div>
            <p class="mb-1"><strong>Miembros del Tribunal:</strong></p>
            <ul class="list-unstyled ps-0">
                @foreach ($tribunal->miembrosTribunales as $miembro)
                    <li><span
                            class="badge bg-secondary me-2">{{ Str::title(Str::lower(Str_replace('_', ' ', $miembro->status))) }}</span>
                        {{ $miembro->user->name }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
