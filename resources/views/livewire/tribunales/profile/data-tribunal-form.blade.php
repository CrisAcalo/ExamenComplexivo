<div class="card mb-4 shadow-sm border-0">
    <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-3 fw-bold" style="color: #2d7a5f;">
                    <i class="bi bi-info-circle-fill me-2"></i>Datos del Tribunal
                </h5>
                @if ($tribunal->estado === 'CERRADO')
                    <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); font-size: 13px;">
                        <i class="bi bi-lock-fill me-1"></i>Tribunal Cerrado
                    </span>
                @else
                    <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 13px;">
                        <i class="bi bi-unlock-fill me-1"></i>Tribunal Abierto
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
                            wire:loading.attr="disabled"
                            style="transition: all 0.3s ease;"
                            onmouseover="this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.transform='translateY(0)'">
                            <i class="bi bi-lock-fill me-1"></i>Cerrar Tribunal
                        </button>
                    @else
                        @php
                            $fechaHoraFin = \Carbon\Carbon::parse($tribunal->fecha . ' ' . $tribunal->hora_fin);
                            $yaFinalizo = now()->greaterThan($fechaHoraFin);
                        @endphp
                        @if($yaFinalizo)
                            <button class="btn btn-sm btn-outline-secondary" disabled
                                title="No se puede abrir. La franja horaria finalizó el {{ $fechaHoraFin->format('d/m/Y H:i') }}">
                                <i class="bi bi-unlock-fill me-1"></i>Abrir Tribunal
                                <small class="ms-1">(Finalizado)</small>
                            </button>
                        @else
                            <button class="btn btn-sm btn-outline-success"
                                wire:click="abrirTribunal"
                                wire:confirm="¿Está seguro que desea abrir este tribunal? Al abrirlo, se permitirán modificaciones y evaluaciones."
                                wire:loading.attr="disabled"
                                style="transition: all 0.3s ease;"
                                onmouseover="this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.transform='translateY(0)'">
                                <i class="bi bi-unlock-fill me-1"></i>Abrir Tribunal
                            </button>
                        @endif
                    @endif

                    {{-- Botón para editar datos --}}
                    <button class="btn btn-sm {{ $modoEdicionTribunal ? 'btn-secondary' : 'text-white' }}"
                        wire:click="toggleModoEdicionTribunal"
                        @if($tribunal->estado === 'CERRADO') disabled title="No se puede editar un tribunal cerrado" @endif
                        @if(!$modoEdicionTribunal) style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); border: none; transition: all 0.3s ease;"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'" @endif>
                        <i class="bi {{ $modoEdicionTribunal ? 'bi-x-circle' : 'bi-pencil-square' }} me-1"></i>
                        {{ $modoEdicionTribunal ? 'Cancelar Edición' : 'Editar Datos' }}
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        @if ($modoEdicionTribunal && $usuarioPuedeEditarDatosTribunal)
            <form wire:submit.prevent="actualizarDatosTribunal">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="fecha_edit" class="form-label fw-semibold">
                            <i class="bi bi-calendar-date me-1"></i>Fecha <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha_edit"
                            wire:model.defer="fecha">
                        @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_inicio_edit" class="form-label fw-semibold">
                            <i class="bi bi-clock me-1"></i>Hora Inicio <span class="text-danger">*</span>
                        </label>
                        <input type="time" class="form-control @error('hora_inicio') is-invalid @enderror"
                            id="hora_inicio_edit" wire:model.defer="hora_inicio">
                        @error('hora_inicio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hora_fin_edit" class="form-label fw-semibold">
                            <i class="bi bi-clock-fill me-1"></i>Hora Fin <span class="text-danger">*</span>
                        </label>
                        <input type="time" class="form-control @error('hora_fin') is-invalid @enderror"
                            id="hora_fin_edit" wire:model.defer="hora_fin">
                        @error('hora_fin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>Estudiante
                        </label>
                        <input type="text" class="form-control"
                            value="{{ $tribunal->estudiante->nombres_completos_id }}" readonly disabled>
                    </div>
                </div>
                <h6 class="mt-3 fw-bold" style="color: #2d7a5f;">
                    <i class="bi bi-people-fill me-2"></i>Miembros del Tribunal <span class="text-danger">*</span>
                </h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="presidente_id_edit" class="form-label fw-semibold">
                            <i class="bi bi-person-badge me-1"></i>Presidente
                        </label>
                        <select wire:model.defer="presidente_id" id="presidente_id_edit"
                            class="form-select @error('presidente_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($integrante1_id == $prof->id && !is_null($integrante1_id)) || ($integrante2_id == $prof->id && !is_null($integrante2_id)) ) disabled @endif>
                                    {{ $prof->name }} {{ $prof->lastname }}
                                </option>
                            @endforeach
                        </select>
                        @error('presidente_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante1_id_edit" class="form-label fw-semibold">
                            <i class="bi bi-person-check me-1"></i>Integrante 1
                        </label>
                        <select wire:model.defer="integrante1_id" id="integrante1_id_edit"
                            class="form-select @error('integrante1_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($presidente_id == $prof->id && !is_null($presidente_id)) || ($integrante2_id == $prof->id && !is_null($integrante2_id)) ) disabled @endif>
                                    {{ $prof->name }} {{ $prof->lastname }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante1_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="integrante2_id_edit" class="form-label fw-semibold">
                            <i class="bi bi-person-check-fill me-1"></i>Integrante 2
                        </label>
                        <select wire:model.defer="integrante2_id" id="integrante2_id_edit"
                            class="form-select @error('integrante2_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($profesoresDisponibles as $prof)
                                <option value="{{ $prof->id }}" @if( ($presidente_id == $prof->id && !is_null($presidente_id)) || ($integrante1_id == $prof->id && !is_null($integrante1_id)) ) disabled @endif>
                                    {{ $prof->name }} {{ $prof->lastname }}
                                </option>
                            @endforeach
                        </select>
                        @error('integrante2_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="submit" class="btn text-white px-4 py-2 mt-2"
                        style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%); border: none; transition: all 0.3s ease; font-weight: 600;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(61,142,114,0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
            </form>
        @else
            {{-- Modo Visualización de Datos del Tribunal --}}
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="p-3 rounded" style="background-color: #f0f8f5; border-left: 4px solid #3d8e72ff;">
                        <p class="mb-1 small text-muted fw-semibold">
                            <i class="bi bi-person me-1"></i>Estudiante
                        </p>
                        <p class="mb-0 fw-bold" style="color: #2d7a5f;">{{ $tribunal->estudiante->nombres_completos_id }}</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="p-3 rounded" style="background-color: #f0f8f5; border-left: 4px solid #3d8e72ff;">
                        <p class="mb-1 small text-muted fw-semibold">
                            <i class="bi bi-calendar-date me-1"></i>Fecha
                        </p>
                        <p class="mb-0 fw-bold" style="color: #2d7a5f;">{{ \Carbon\Carbon::parse($tribunal->fecha)->isoFormat('LL') }}</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="p-3 rounded" style="background-color: #f0f8f5; border-left: 4px solid #3d8e72ff;">
                        <p class="mb-1 small text-muted fw-semibold">
                            <i class="bi bi-clock me-1"></i>Hora Inicio
                        </p>
                        <p class="mb-0 fw-bold" style="color: #2d7a5f;">{{ \Carbon\Carbon::parse($tribunal->hora_inicio)->isoFormat('LT') }}</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="p-3 rounded" style="background-color: #f0f8f5; border-left: 4px solid #3d8e72ff;">
                        <p class="mb-1 small text-muted fw-semibold">
                            <i class="bi bi-clock-fill me-1"></i>Hora Fin
                        </p>
                        <p class="mb-0 fw-bold" style="color: #2d7a5f;">{{ \Carbon\Carbon::parse($tribunal->hora_fin)->isoFormat('LT') }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-3 rounded" style="background-color: #ffffff; border: 1px solid #dee2e6;">
                <p class="mb-3 fw-bold" style="color: #2d7a5f;">
                    <i class="bi bi-people-fill me-2"></i>Miembros del Tribunal:
                </p>
                <ul class="list-unstyled mb-0">
                    @foreach ($tribunal->miembrosTribunales->sortBy(fn($m) => ['PRESIDENTE' => 0, 'INTEGRANTE1' => 1, 'INTEGRANTE2' => 2][$m->status] ?? 3) as $miembro)
                        <li class="mb-2">
                            <span class="badge px-3 py-2 me-2"
                                @if($miembro->status == 'PRESIDENTE') style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 13px; min-width: 100px; text-align: center;"
                                @elseif($miembro->status == 'INTEGRANTE1') style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 13px; min-width: 100px; text-align: center;"
                                @elseif($miembro->status == 'INTEGRANTE2') style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 13px; min-width: 100px; text-align: center;"
                                @else style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); font-size: 13px; min-width: 100px; text-align: center;" @endif>
                                {{ Str::title(Str::lower(Str_replace('_', ' ', $miembro->status))) }}
                            </span>
                            <span class="fw-semibold">{{ $miembro->user->name }} {{ $miembro->user->lastname }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
