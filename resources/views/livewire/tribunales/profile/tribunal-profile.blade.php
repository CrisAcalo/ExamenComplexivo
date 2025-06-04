<div>
    @section('title', $tribunal ? 'Perfil del Tribunal: ' . $tribunal->estudiante->nombres_completos_id : 'Perfil del Tribunal')

    <div class="container-fluid p-0">
        {{-- Breadcrumbs --}}
        {{-- ... (sin cambios) ... --}}

        @include('partials.alerts')

        @if ($tribunal)
            {{-- SECCIÓN 1: DATOS DEL TRIBUNAL --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle-fill text-primary"></i> Datos del Tribunal</h5>
                    @can('editar-datos-basicos-este-tribunal-como-presidente', $tribunal) {{-- O tu lógica de permiso para admin --}}
                        @if($usuarioPuedeEditarDatosTribunal)
                            <button class="btn btn-sm {{ $modoEdicionTribunal ? 'btn-secondary' : 'btn-outline-primary' }}"
                                wire:click="toggleModoEdicionTribunal">
                                <i class="bi {{ $modoEdicionTribunal ? 'bi-x-circle' : 'bi-pencil-square' }}"></i>
                                {{ $modoEdicionTribunal ? 'Cancelar Edición' : 'Editar Datos' }}
                            </button>
                        @endif
                    @endcan
                </div>
                {{-- ... (Cuerpo de la tarjeta de datos del tribunal sin cambios estructurales, pero los selects de miembros ahora usan profesoresDisponibles) ... --}}
                 <div class="card-body">
                    @if ($modoEdicionTribunal && $usuarioPuedeEditarDatosTribunal)
                        {{-- Formulario de Edición --}}
                        <form wire:submit.prevent="actualizarDatosTribunal">
                            {{-- ... (campos de fecha, hora, estudiante readonly) ... --}}
                             <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_edit" class="form-label">Fecha</label>
                                    <input type="date" class="form-control @error('fecha') is-invalid @enderror"
                                        id="fecha_edit" wire:model.defer="fecha">
                                    @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="hora_inicio_edit" class="form-label">Hora Inicio</label>
                                    <input type="time"
                                        class="form-control @error('hora_inicio') is-invalid @enderror"
                                        id="hora_inicio_edit" wire:model.defer="hora_inicio">
                                    @error('hora_inicio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="hora_fin_edit" class="form-label">Hora Fin</label>
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
                            <h6 class="mt-3">Miembros del Tribunal</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="presidente_id_edit" class="form-label">Presidente</label>
                                    <select wire:model.defer="presidente_id" id="presidente_id_edit"
                                        class="form-select @error('presidente_id') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach ($profesoresDisponibles as $prof)
                                            <option value="{{ $prof->id }}"
                                                @if ( (isset($integrante1_id) && $prof->id == $integrante1_id) || (isset($integrante2_id) && $prof->id == $integrante2_id) ) disabled @endif>
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
                                            <option value="{{ $prof->id }}"
                                                @if ( (isset($presidente_id) && $prof->id == $presidente_id) || (isset($integrante2_id) && $prof->id == $integrante2_id) ) disabled @endif>
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
                                            <option value="{{ $prof->id }}"
                                                @if ( (isset($presidente_id) && $prof->id == $presidente_id) || (isset($integrante1_id) && $prof->id == $integrante1_id) ) disabled @endif>
                                                {{ $prof->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('integrante2_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-save"></i> Guardar Cambios Tribunal</button>
                        </form>
                    @else
                        {{-- Modo Visualización --}}
                        {{-- ... (sin cambios) ... --}}
                         <div class="row">
                            <div class="col-md-3"><p><strong>Estudiante:</strong><br>{{ $tribunal->estudiante->nombres_completos_id }}</p></div>
                            <div class="col-md-3"><p><strong>Fecha:</strong><br>{{ \Carbon\Carbon::parse($tribunal->fecha)->format('d/m/Y') }}</p></div>
                            <div class="col-md-3"><p><strong>Hora Inicio:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_inicio)->format('H:i A') }}</p></div>
                            <div class="col-md-3"><p><strong>Hora Fin:</strong><br>{{ \Carbon\Carbon::parse($tribunal->hora_fin)->format('H:i A') }}</p></div>
                        </div>
                        <p><strong>Miembros del Tribunal:</strong></p>
                        <ul>
                            @foreach ($tribunal->miembrosTribunales as $miembro)
                                <li>{{ Str::title(Str::lower(Str_replace('_',' ',$miembro->status))) }}: {{ $miembro->user->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN 2: FORMULARIO DE CALIFICACIÓN (SOLO PARA MIEMBROS QUE PUEDEN CALIFICAR) --}}
            @if ($planEvaluacionActivo && $usuarioPuedeCalificar && !$usuarioPuedeVerTodasLasCalificaciones)
                {{-- ... (Formulario de calificación del usuario actual, como lo tenías, usando $calificaciones) ... --}}
                 <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clipboard2-data-fill text-info"></i> Mi Formulario de Calificación (Plan: {{ $planEvaluacionActivo->nombre }})</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="guardarCalificaciones">
                            @foreach ($planEvaluacionActivo->itemsPlanEvaluacion as $itemPlan)
                                {{-- ... (Lógica de renderizado de ítem de nota directa y rúbrica tabular) ... --}}
                                 <div class="mb-4 p-3 border rounded item-evaluacion-block">
                                    <h5>{{ $loop->iteration }}. {{ $itemPlan->nombre_item }} <span
                                            class="badge bg-secondary">{{ $itemPlan->ponderacion_global }}%</span>
                                    </h5>

                                    @if ($itemPlan->tipo_item === 'NOTA_DIRECTA')
                                        {{-- ... (campos para nota directa) ... --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="nota_directa_{{ $itemPlan->id }}"
                                                    class="form-label">Nota (sobre 20)</label>
                                                <input type="number" step="0.01" min="0" max="20"
                                                    class="form-control @error('calificaciones.'.$itemPlan->id.'.nota_directa') is-invalid @enderror"
                                                    id="nota_directa_{{ $itemPlan->id }}"
                                                    wire:model.defer="calificaciones.{{ $itemPlan->id }}.nota_directa">
                                                @error('calificaciones.'.$itemPlan->id.'.nota_directa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-9">
                                                <label for="obs_general_nota_{{ $itemPlan->id }}" class="form-label">Observación General (Opcional)</label>
                                                <textarea class="form-control @error('calificaciones.'.$itemPlan->id.'.observacion_general') is-invalid @enderror"
                                                          id="obs_general_nota_{{ $itemPlan->id }}" rows="2"
                                                          wire:model.defer="calificaciones.{{ $itemPlan->id }}.observacion_general"></textarea>
                                                @error('calificaciones.'.$itemPlan->id.'.observacion_general') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    @elseif ($itemPlan->tipo_item === 'RUBRICA_TABULAR' && $itemPlan->rubricaPlantilla)
                                        {{-- ... (tabla para rúbrica tabular) ... --}}
                                        @php $rubricaParaCalificar = $itemPlan->rubricaPlantilla; @endphp
                                        <p class="text-muted small">Usando plantilla: {{ $rubricaParaCalificar->nombre }}</p>
                                        @foreach ($rubricaParaCalificar->componentesRubrica as $componenteR)
                                            <div class="mb-3 p-2 border-start border-3 border-primary">
                                                <h6>{{ $componenteR->nombre }} <small class="text-muted">({{ $componenteR->ponderacion }}% de esta rúbrica)</small></h6>
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width:30%">Criterio</th>
                                                            <th style="width:40%">Calificación</th>
                                                            <th style="width:30%">Observación (Opcional)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($componenteR->criteriosComponente as $criterioR)
                                                            <tr>
                                                                <td>
                                                                    {{ $criterioR->nombre }}
                                                                    @error('calificaciones.'.$itemPlan->id.'.componentes_evaluados.'.$componenteR->id.'.criterios_evaluados.'.$criterioR->id.'.calificacion_criterio_id') <br><span class="text-danger small">{{ $message }}</span> @enderror
                                                                </td>
                                                                <td>
                                                                    @if(isset($calificaciones[$itemPlan->id]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'][$criterioR->id]['opciones_calificacion']))
                                                                        @foreach ($calificaciones[$itemPlan->id]['componentes_evaluados'][$componenteR->id]['criterios_evaluados'][$criterioR->id]['opciones_calificacion'] as $opcionCalif)
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio"
                                                                                   wire:model.defer="calificaciones.{{ $itemPlan->id }}.componentes_evaluados.{{ $componenteR->id }}.criterios_evaluados.{{ $criterioR->id }}.calificacion_criterio_id"
                                                                                   id="calif_{{ $itemPlan->id }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalif->id }}"
                                                                                   value="{{ $opcionCalif->id }}">
                                                                            <label class="form-check-label small" for="calif_{{ $itemPlan->id }}_{{ $componenteR->id }}_{{ $criterioR->id }}_{{ $opcionCalif->id }}">
                                                                                <strong>{{ $opcionCalif->nombre }} ({{ $opcionCalif->valor }})</strong>: {{ $opcionCalif->descripcion }}
                                                                            </label>
                                                                        </div>
                                                                        @endforeach
                                                                    @else
                                                                        <small class="text-muted">Cargando opciones...</small>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <textarea class="form-control form-control-sm @error('calificaciones.'.$itemPlan->id.'.componentes_evaluados.'.$componenteR->id.'.criterios_evaluados.'.$criterioR->id.'.observacion') is-invalid @enderror"
                                                                              rows="2" placeholder="Observación específica del criterio"
                                                                              wire:model.defer="calificaciones.{{ $itemPlan->id }}.componentes_evaluados.{{ $componenteR->id }}.criterios_evaluados.{{ $criterioR->id }}.observacion"></textarea>
                                                                    @error('calificaciones.'.$itemPlan->id.'.componentes_evaluados.'.$componenteR->id.'.criterios_evaluados.'.$criterioR->id.'.observacion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach
                                        <div class="mt-2">
                                            <label for="obs_general_rubrica_{{ $itemPlan->id }}" class="form-label small">Observación General para este Ítem de Rúbrica (Opcional)</label>
                                            <textarea class="form-control form-control-sm @error('calificaciones.'.$itemPlan->id.'.observacion_general') is-invalid @enderror"
                                                      id="obs_general_rubrica_{{ $itemPlan->id }}" rows="2"
                                                      wire:model.defer="calificaciones.{{ $itemPlan->id }}.observacion_general"></textarea>
                                            @error('calificaciones.'.$itemPlan->id.'.observacion_general') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    @else
                                        <p class="text-warning">No se encontró la plantilla de rúbrica asociada a este ítem o el tipo no es 'Rúbrica Tabular'.</p>
                                    @endif
                                </div>
                            @endforeach
                            <div class="mt-4">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-check-circle-fill"></i> Guardar Mis Calificaciones
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- SECCIÓN 3: VISUALIZACIÓN DE TODAS LAS CALIFICACIONES (PARA ADMIN/DIRECTOR/APOYO AUTORIZADO) --}}
            @if ($usuarioPuedeVerTodasLasCalificaciones && !$usuarioPuedeCalificar) {{-- O alguna otra condición para no duplicar --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people-fill text-warning"></i> Calificaciones Ingresadas por Miembros del Tribunal</h5>
                    </div>
                    <div class="card-body">
                        @if (empty($todasLasCalificacionesDelTribunal))
                            <p class="text-muted">Aún no hay calificaciones registradas por los miembros de este tribunal.</p>
                        @else
                            @foreach ($todasLasCalificacionesDelTribunal as $userId => $datosMiembro)
                                <div class="mb-4 p-3 border rounded">
                                    <h6>Calificaciones de: <strong>{{ $datosMiembro['nombre_miembro'] }}</strong> ({{ Str::title(Str::lower(Str_replace('_',' ',$datosMiembro['rol_miembro']))) }})</h6>
                                    @if (empty($datosMiembro['calificaciones_ingresadas']))
                                        <p class="small text-muted">Este miembro aún no ha registrado calificaciones.</p>
                                    @else
                                        @foreach ($datosMiembro['calificaciones_ingresadas'] as $itemPlanId => $califItem)
                                            <div class="mb-3 p-2 border-start">
                                                <p class="mb-1"><strong>{{ $loop->parent->iteration }}. {{ $califItem['nombre_item_plan'] }}:</strong></p>
                                                @if ($califItem['tipo'] === 'NOTA_DIRECTA')
                                                    <p class="ms-3 mb-1">Nota Directa: <strong class="text-primary">{{ $califItem['nota_directa'] }}</strong></p>
                                                    @if($califItem['observacion_general']) <p class="ms-3 mb-0 small text-muted"><em>Obs. General: {{ $califItem['observacion_general'] }}</em></p> @endif
                                                @elseif ($califItem['tipo'] === 'RUBRICA_TABULAR')
                                                    <p class="ms-3 mb-1 small text-muted">Rúbrica: {{ $califItem['rubrica_plantilla_nombre'] }}</p>
                                                     @if($califItem['observacion_general']) <p class="ms-3 mb-1 small text-muted"><em>Obs. General del Ítem: {{ $califItem['observacion_general'] }}</em></p> @endif
                                                    @foreach ($califItem['componentes_evaluados'] as $datosComp)
                                                        <div class="ms-3 mb-2">
                                                            <p class="mb-0 small"><em>{{ $datosComp['nombre_componente_rubrica'] }}:</em></p>
                                                            <ul class="list-unstyled ps-3 small">
                                                            @foreach ($datosComp['criterios_evaluados'] as $datosCrit)
                                                                <li>
                                                                    {{ $datosCrit['nombre_criterio_rubrica'] }}:
                                                                    <strong class="text-info">{{ $datosCrit['calificacion_elegida_nombre'] }} ({{ $datosCrit['calificacion_elegida_valor'] }})</strong>
                                                                    @if($datosCrit['observacion']) <em class="d-block text-muted">- Obs: {{ $datosCrit['observacion'] }}</em> @endif
                                                                </li>
                                                            @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif


            {{-- SECCIÓN 4: RESUMEN FINAL Y ACTA (Futuro, para Presidente/Admin) --}}
            @can('exportar-acta-este-tribunal-como-presidente', $tribunal) {{-- O permiso para admin --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text-fill text-danger"></i> Acta y Cierre</h5>
                    </div>
                    <div class="card-body">
                        <p>Aquí se podría mostrar un resumen de las notas finales (promedios si aplica) y el botón para generar/exportar el acta.</p>
                        {{-- <button class="btn btn-danger"><i class="bi bi-file-pdf-fill"></i> Exportar Acta</button> --}}
                        {{-- <button class="btn btn-warning"><i class="bi bi-lock-fill"></i> Cerrar Calificaciones</button> --}}
                    </div>
                </div>
            @endcan

        @else
            {{-- Este div se mostrará si $tribunal es null después de mount (y render se llama) --}}
            {{-- El mensaje flash de session() ya se maneja con @include('partials.alerts') --}}
            @if(session()->has('danger') || session()->has('message_tribunal_profile'))
                {{-- No hacer nada aquí si la alerta ya se muestra --}}
            @else
                <div class="alert alert-warning">Cargando datos del tribunal o tribunal no encontrado...</div>
            @endif
        @endif
    </div> <!-- container-fluid -->
</div>
