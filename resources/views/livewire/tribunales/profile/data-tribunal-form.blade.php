<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">
                <i class="bi bi-info-circle-fill text-primary"></i> Datos del Tribunal
            </h5>
            @if ($tribunal->estado === 'CERRADO')
                <span class="badge bg-danger">
                    <i class="bi bi-lock-fill"></i> Tribunal Cerrado
                </span>
            @else
                <span class="badge bg-success">
                    <i class="bi bi-unlock-fill"></i> Tribunal Abierto
                </span>
            @endif
        </div>

        <div class="btn-group">
            @if ($usuarioPuedeEditarDatosTribunal)
                {{-- Botón para cerrar/abrir tribunal --}}
                @if ($tribunal->estado === 'ABIERTO')
                    <button class="btn btn-sm btn-outline-danger"
                        wire:click="cerrarTribunal"
                        wire:confirm="¿Está seguro que desea cerrar este tribunal? Al cerrarlo, no se permitirán más modificaciones ni evaluaciones."
                        wire:loading.attr="disabled">
                        <i class="bi bi-lock-fill"></i> Cerrar Tribunal
                    </button>
                @else
                    <button class="btn btn-sm btn-outline-success"
                        wire:click="abrirTribunal"
                        wire:confirm="¿Está seguro que desea abrir este tribunal? Al abrirlo, se permitirán modificaciones y evaluaciones."
                        wire:loading.attr="disabled">
                        <i class="bi bi-unlock-fill"></i> Abrir Tribunal
                    </button>
                @endif

                {{-- Botón para editar datos --}}
                <button class="btn btn-sm {{ $modoEdicionTribunal ? 'btn-secondary' : 'btn-outline-primary' }}"
                    wire:click="toggleModoEdicionTribunal"
                    @if($tribunal->estado === 'CERRADO') disabled title="No se puede editar un tribunal cerrado" @endif>
                    <i class="bi {{ $modoEdicionTribunal ? 'bi-x-circle' : 'bi-pencil-square' }}"></i>
                    {{ $modoEdicionTribunal ? 'Cancelar Edición' : 'Editar Datos' }}
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if ($modoEdicionTribunal && $usuarioPuedeEditarDatosTribunal)
            <form wire:submit.prevent="actualizarDatosTribunal">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="fecha_edit" class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha_edit"
                            wire:model.defer="fecha">
                        @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_inicio_edit" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('hora_inicio') is-invalid @enderror"
                            id="hora_inicio_edit" wire:model.defer="hora_inicio">
                        @error('hora_inicio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_fin_edit" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('hora_fin') is-invalid @enderror"
                            id="hora_fin_edit" wire:model.defer="hora_fin">
                        @error('hora_fin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estudiante</label>
                        <input type="text" class="form-control"
                            value="{{ $tribunal->estudiante->nombres_completos_id }}" readonly disabled>
                    </div>
                </div>
                <h6 class="mt-3">Miembros del Tribunal <span class="text-danger">*</span></h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="presidente_id_edit" class="form-label">Presidente</label>
                        <select wire:model.defer="presidente_id" id="presidente_id_edit"
                            class="form-select @error('presidente_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($integrante1_id == $prof->id && !is_null($integrante1_id)) || ($integrante2_id == $prof->id && !is_null($integrante2_id)) ) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('presidente_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante1_id_edit" class="form-label">Integrante 1</label>
                        <select wire:model.defer="integrante1_id" id="integrante1_id_edit"
                            class="form-select @error('integrante1_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($presidente_id == $prof->id && !is_null($presidente_id)) || ($integrante2_id == $prof->id && !is_null($integrante2_id)) ) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante1_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante2_id_edit" class="form-label">Integrante 2</label>
                        <select wire:model.defer="integrante2_id" id="integrante2_id_edit"
                            class="form-select @error('integrante2_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($presidente_id == $prof->id && !is_null($presidente_id)) || ($integrante1_id == $prof->id && !is_null($integrante1_id)) ) disabled @endif>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante2_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-save"></i> Guardar Cambios</button>
            </form>
        @else
            {{-- Modo Visualización de Datos del Tribunal --}}
            <div class="row">
                <div class="col-md-3"><p class="mb-2"><strong>Estudiante:</strong><br>{{ $tribunal->estudiante->nombres_completos_id }}</p></div>
                <div class="col-md-3"><p class="mb-2"><strong>Fecha:</strong><br>{{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}</p></div>
                <div class="col-md-3"><p class="mb-2"><strong>Hora Inicio:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}</p></div>
                <div class="col-md-3"><p class="mb-2"><strong>Hora Fin:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}</p></div>
            </div>
            <p class="mb-1 mt-2"><strong>Miembros del Tribunal:</strong></p>
            <ul class="list-unstyled ps-0">
                @foreach ($tribunal->miembrosTribunales->sortBy(fn($m) => ['PRESIDENTE' => 0, 'INTEGRANTE1' => 1, 'INTEGRANTE2' => 2][$m->status] ?? 3) as $miembro)
                    <li>
                        <span class="badge
                            @if($miembro->status == 'PRESIDENTE') bg-success
                            @elseif($miembro->status == 'INTEGRANTE1') bg-info text-dark
                            @elseif($miembro->status == 'INTEGRANTE2') bg-secondary
                            @else bg-primary @endif me-2" style="width: 90px; text-align:start;">
                            {{ Str::title(Str::lower(Str_replace('_', ' ', $miembro->status))) }}
                        </span>
                        {{ $miembro->user->name }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
