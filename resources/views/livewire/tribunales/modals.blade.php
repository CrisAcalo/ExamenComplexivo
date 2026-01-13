<div wire:ignore.self class="modal fade" id="createDataModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDataModalLabel">
                    @if($modoPlantilla)
                        <i class="bi bi-diagram-2 text-info"></i> Crear Plantilla de Tribunal
                    @else
                        <i class="bi bi-diagram-3 text-primary"></i> Añadir Nuevo Tribunal
                    @endif
                </h5>
                <button wire:click.prevent="cancel()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    {{-- Toggle de Modo Plantilla --}}
                    <div class="mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="form-check form-switch">
                                    <input wire:model.lazy="modoPlantilla" class="form-check-input" type="checkbox" id="modoPlantillaSwitch">
                                    <label class="form-check-label fw-bold" for="modoPlantillaSwitch">
                                        <i class="bi bi-diagram-2 text-info"></i>
                                        @if($modoPlantilla)
                                            Modo Plantilla: Crear plantilla para asignar múltiples estudiantes
                                        @else
                                            Modo Individual: Crear tribunal para un estudiante específico
                                        @endif
                                    </label>
                                </div>
                                @if($modoPlantilla)
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle"></i>
                                        En modo plantilla, podrá crear un tribunal base y luego asignar múltiples estudiantes con horarios automáticos.
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Descripción de Plantilla (solo en modo plantilla) --}}
                    @if($modoPlantilla)
                        <div class="mb-3">
                            <label for="descripcion_plantilla_create" class="form-label">Descripción de la Plantilla <span class="text-danger">*</span></label>
                            <input wire:model.defer="descripcion_plantilla" type="text" class="form-control @error('descripcion_plantilla') is-invalid @enderror"
                                   id="descripcion_plantilla_create" placeholder="Ej: Tribunales de Titulación - Modalidad X">
                            @error('descripcion_plantilla') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">Esta descripción ayudará a identificar la plantilla al asignar estudiantes.</small>
                        </div>
                    @endif

                    {{-- Estudiante (solo en modo individual) --}}
                    @if(!$modoPlantilla)
                        <div class="mb-3">
                            <label for="estudiante_id_create" class="form-label">Estudiante <span class="text-danger">*</span></label>
                            <select wire:model.defer="estudiante_id" id="estudiante_id_create" name="estudiante_id"
                                class="form-select @error('estudiante_id') is-invalid @enderror">
                                <option value="">-- Elija un Estudiante --</option>
                                @forelse ($estudiantesDisponibles as $estudiante)
                                    <option value="{{ $estudiante->id }}">{{ $estudiante->apellidos }} {{ $estudiante->nombres }} ({{ $estudiante->ID_estudiante }})</option>
                                @empty
                                    <option value="" disabled>No hay estudiantes disponibles sin tribunal asignado.</option>
                                @endforelse
                            </select>
                            @error('estudiante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Caso --}}
                        <div class="mb-3">
                            <label for="caso_create" class="form-label">Caso</label>
                            <input wire:model.defer="caso" type="text" class="form-control @error('caso') is-invalid @enderror"
                                   id="caso_create" placeholder="Ej: 6">
                            @error('caso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Número o identificador del caso (opcional).
                            </small>
                        </div>
                    @endif

                    <div class="row">
                        {{-- Fecha --}}
                        <div class="col-md-3 mb-3">
                            <label for="fecha_create" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input wire:model.lazy="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha_create">
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        {{-- Hora Inicio --}}
                        <div class="col-md-3 mb-3">
                            <label for="hora_inicio_create" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                            <input wire:model.lazy="hora_inicio" type="time" class="form-control @error('hora_inicio') is-invalid @enderror" id="hora_inicio_create">
                            @error('hora_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        {{-- Hora Fin --}}
                        <div class="col-md-3 mb-3">
                            <label for="hora_fin_create" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                            <input wire:model.lazy="hora_fin" type="time" class="form-control @error('hora_fin') is-invalid @enderror" id="hora_fin_create">
                            @error('hora_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        {{-- Laboratorio --}}
                        <div class="col-md-3 mb-3">
                            <label for="laboratorio_create" class="form-label">
                                Laboratorio
                                @if(!$modoPlantilla)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <select wire:model.defer="laboratorio" id="laboratorio_create" class="form-select @error('laboratorio') is-invalid @enderror">
                                <option value="">-- Seleccione --</option>
                                @foreach($laboratoriosDisponibles as $lab)
                                    <option value="{{ $lab }}">{{ $lab }}</option>
                                @endforeach
                            </select>
                            @error('laboratorio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if($modoPlantilla)
                                <small class="form-text text-muted">Opcional para plantillas</small>
                            @endif
                        </div>
                    </div>

                    {{-- Mensaje informativo sobre horarios --}}
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong> Los horarios de tribunales no pueden solaparse en el mismo laboratorio y fecha.
                        Puede haber múltiples tribunales a la misma hora si están en laboratorios diferentes.
                    </div>


                    <div class="card p-3 mt-3">
                        <h6 class="card-title">Miembros del Tribunal <span class="text-danger">*</span></h6>
                        <div class="row">
                            {{-- Presidente --}}
                            <div class="col-md-4 mb-3">
                                <label for="presidente_id_create" class="form-label">Presidente</label>
                                <select wire:model.defer="presidente_id" id="presidente_id_create" name="presidente_id"
                                    class="form-select @error('presidente_id') is-invalid @enderror">
                                    <option value="">-- Elija Presidente --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                        @if ( (empty($integrante1_id) || $profesor->id != $integrante1_id) && (empty($integrante2_id) || $profesor->id != $integrante2_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }} {{ $profesor->lastname }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('presidente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- Integrante 1 --}}
                            <div class="col-md-4 mb-3">
                                <label for="integrante1_id_create" class="form-label">Integrante 1</label>
                                <select wire:model.defer="integrante1_id" id="integrante1_id_create" name="integrante1_id"
                                    class="form-select @error('integrante1_id') is-invalid @enderror">
                                    <option value="">-- Elija Integrante 1 --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                    @if ( (empty($presidente_id) || $profesor->id != $presidente_id) && (empty($integrante2_id) || $profesor->id != $integrante2_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }} {{ $profesor->lastname }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('integrante1_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- Integrante 2 --}}
                            <div class="col-md-4 mb-3">
                                <label for="integrante2_id_create" class="form-label">Integrante 2</label>
                                <select wire:model.defer="integrante2_id" id="integrante2_id_create" name="integrante2_id"
                                    class="form-select @error('integrante2_id') is-invalid @enderror">
                                    <option value="">-- Elija Integrante 2 --</option>
                                    @foreach ($profesoresParaTribunal as $profesor)
                                    @if ( (empty($presidente_id) || $profesor->id != $presidente_id) && (empty($integrante1_id) || $profesor->id != $integrante1_id) )
                                            <option value="{{ $profesor->id }}">{{ $profesor->name }} {{ $profesor->lastname }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('integrante2_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cancel()" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">
                    @if($modoPlantilla)
                        <i class="bi bi-diagram-2"></i> Crear Plantilla
                    @else
                        <i class="bi bi-save"></i> Guardar Tribunal
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Tribunal Confirmation Modal -->
<div wire:ignore.self class="modal fade" id="deleteTribunalModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="deleteTribunalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @if ($tribunalAEliminar)
            <div class="modal-content">
                <div class="modal-header bg-danger text-light">
                    <h5 class="modal-title" id="deleteTribunalModalLabel">Confirmar Eliminación</h5>
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($tribunalAEliminar->es_plantilla)
                        {{-- Mensaje para plantilla --}}
                        <p>¿Está seguro de que desea eliminar la <strong>plantilla "{{ $tribunalAEliminar->descripcion_plantilla }}"</strong>
                            programada para el <strong>{{ \Carbon\Carbon::parse($tribunalAEliminar->fecha)->format('d/m/Y') }}</strong>?
                        </p>
                        <p class="text-warning"><i class="bi bi-exclamation-triangle"></i> Esta plantilla aún no tiene estudiantes asignados.</p>
                    @else
                        {{-- Mensaje para tribunal individual --}}
                        <p>¿Está seguro de que desea eliminar el tribunal para el estudiante
                            <strong>{{ $tribunalAEliminar->estudiante->nombres }} {{ $tribunalAEliminar->estudiante->apellidos }}</strong>
                            programado para el <strong>{{ \Carbon\Carbon::parse($tribunalAEliminar->fecha)->format('d/m/Y') }}</strong>?
                        </p>
                    @endif
                    <p class="text-danger fw-bold">Esta acción no se puede deshacer y se eliminarán los miembros asociados.</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="resetDeleteConfirmation" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="destroy()">
                        <i class="bi bi-trash-fill"></i> Sí, Eliminar
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>


<!-- Modal para Asignar Estudiantes a Plantilla -->
<div wire:ignore.self class="modal fade" id="asignarEstudiantesModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="asignarEstudiantesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Cambiado de modal-lg a modal-xl para más espacio -->
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="asignarEstudiantesModalLabel">
                    <i class="bi bi-people-fill"></i> Asignar Estudiantes a Plantilla
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="cerrarAsignarEstudiantes"></button>
            </div>
            @if($tribunalPlantilla)
                <div class="modal-body">
                    {{-- Información de la plantilla --}}
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-diagram-2"></i>
                                {{ $tribunalPlantilla->descripcion_plantilla ?? 'Plantilla de Tribunal' }}
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar3 text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Fecha</small>
                                            <strong>{{ \Carbon\Carbon::parse($tribunalPlantilla->fecha)->format('d/m/Y') }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-clock text-success me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Horario Base</small>
                                            <strong>{{ \Carbon\Carbon::parse($tribunalPlantilla->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($tribunalPlantilla->hora_fin)->format('H:i') }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-people text-warning me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Miembros</small>
                                            <div>
                                                @foreach($tribunalPlantilla->miembrosTribunales as $miembro)
                                                    <span class="badge bg-secondary me-1 mb-1">{{ $miembro->user->name }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Layout de dos columnas --}}
                    <div class="row">
                        {{-- Columna izquierda: Selección de estudiantes --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Seleccionar Estudiantes para Asignar:</label>

                                {{-- Buscador de estudiantes --}}
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input wire:model.debounce.300ms="buscarEstudiante" type="text" class="form-control"
                                               placeholder="Buscar por nombre, apellido o ID...">
                                    </div>
                                    @if(!empty($buscarEstudiante))
                                        <small class="text-muted">
                                            Mostrando resultados para: <strong>"{{ $buscarEstudiante }}"</strong>
                                            <button wire:click="$set('buscarEstudiante', '')" class="btn btn-link btn-sm p-0 ms-1">
                                                <i class="bi bi-x-circle"></i> Limpiar
                                            </button>
                                        </small>
                                    @endif
                                </div>

                                {{-- Lista de estudiantes con scroll --}}
                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    @forelse($this->estudiantesFiltrados as $estudiante)
                                        <div class="form-check mb-2">
                                            <input wire:model="estudiantesSeleccionados" class="form-check-input" type="checkbox"
                                                   value="{{ $estudiante->id }}" id="estudiante_{{ $estudiante->id }}">
                                            <label class="form-check-label" for="estudiante_{{ $estudiante->id }}">
                                                <strong>{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</strong>
                                                <small class="text-muted d-block">ID: {{ $estudiante->ID_estudiante }}</small>
                                            </label>
                                        </div>
                                    @empty
                                        @if(!empty($buscarEstudiante))
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-search fs-4"></i>
                                                <p class="mb-0">No se encontraron estudiantes que coincidan con "{{ $buscarEstudiante }}".</p>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-info-circle fs-4"></i>
                                                <p class="mb-0">No hay estudiantes disponibles para asignar.</p>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>

                                {{-- Contador de estudiantes seleccionados --}}
                                @if(count($estudiantesSeleccionados) > 0)
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <small class="text-info">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <strong>{{ count($estudiantesSeleccionados) }} estudiante(s) seleccionado(s)</strong>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i>
                                            Se generarán {{ count($estudiantesSeleccionados) }} tribunales con horarios distribuidos equitativamente.
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Columna derecha: Vista previa de horarios --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Vista Previa de Horarios:</label>

                                @if(count($estudiantesSeleccionados) > 0)
                                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                        <div class="alert alert-info mb-3">
                                            <h6 class="mb-2"><i class="bi bi-info-circle"></i> Distribución de Tribunales:</h6>
                                            <small class="text-muted">
                                                Los horarios se distribuyen equitativamente entre todos los estudiantes seleccionados.
                                            </small>
                                        </div>

                                        <div class="list-group list-group-flush">
                                            @foreach($estudiantesSeleccionados as $index => $estudianteId)
                                                @php
                                                    $estudiante = $estudiantesDisponibles->find($estudianteId);
                                                    $cantidadEstudiantes = count($estudiantesSeleccionados);

                                                    // Parsear las horas de la plantilla
                                                    try {
                                                        $horaInicio = \Carbon\Carbon::createFromFormat('H:i:s', $tribunalPlantilla->hora_inicio);
                                                    } catch (\Exception $e) {
                                                        $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $tribunalPlantilla->hora_inicio);
                                                    }

                                                    try {
                                                        $horaFin = \Carbon\Carbon::createFromFormat('H:i:s', $tribunalPlantilla->hora_fin);
                                                    } catch (\Exception $e) {
                                                        $horaFin = \Carbon\Carbon::createFromFormat('H:i', $tribunalPlantilla->hora_fin);
                                                    }

                                                    // Calcular duración total y por tribunal
                                                    $duracionTotalMinutos = $horaFin->diffInMinutes($horaInicio);
                                                    $duracionPorTribunal = floor($duracionTotalMinutos / $cantidadEstudiantes);

                                                    // Calcular horas para este tribunal específico
                                                    $horaInicioCalculada = $horaInicio->copy()->addMinutes($index * $duracionPorTribunal);

                                                    if ($index === $cantidadEstudiantes - 1) {
                                                        // Último tribunal usa la hora fin original
                                                        $horaFinCalculada = $horaFin->copy();
                                                    } else {
                                                        $horaFinCalculada = $horaInicioCalculada->copy()->addMinutes($duracionPorTribunal);
                                                    }
                                                @endphp
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div class="me-auto">
                                                        <div class="fw-bold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</div>
                                                        <small class="text-muted">ID: {{ $estudiante->ID_estudiante }}</small>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill">
                                                        {{ $horaInicioCalculada->format('H:i') }} - {{ $horaFinCalculada->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="border rounded p-4 text-center text-muted">
                                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                        <p class="mb-0">Selecciona estudiantes para ver la vista previa de horarios</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div>
                        @if(count($estudiantesSeleccionados) > 0)
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                {{ count($estudiantesSeleccionados) }} estudiante(s) seleccionado(s)
                            </small>
                        @endif
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2"
                                wire:click="cerrarAsignarEstudiantes">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" wire:click="generarTribunalesIndividuales"
                                @if(count($estudiantesSeleccionados) == 0) disabled @endif>
                            <i class="bi bi-diagram-3"></i>
                            Generar {{ count($estudiantesSeleccionados) > 0 ? count($estudiantesSeleccionados) : '' }} Tribunal{{ count($estudiantesSeleccionados) != 1 ? 'es' : '' }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de Importación desde Excel --}}
<div wire:ignore.self class="modal fade" id="importarTribunalesModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="importarTribunalesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importarTribunalesModalLabel">
                    <i class="bi bi-file-earmark-excel"></i> Importar Tribunales desde Excel
                </h5>
                <button wire:click="cerrarModalImportacion" type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="importarTribunales">
                    {{-- Fecha del Tribunal --}}
                    <div class="mb-3">
                        <label for="fechaImportacion" class="form-label">
                            Fecha del Tribunal <span class="text-danger">*</span>
                        </label>
                        <input wire:model.defer="fechaImportacion" type="date"
                               class="form-control @error('fechaImportacion') is-invalid @enderror"
                               id="fechaImportacion" min="{{ date('Y-m-d') }}">
                        @error('fechaImportacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-calendar-check"></i>
                            Todos los tribunales importados se crearán para esta fecha.
                        </small>
                    </div>

                    {{-- Archivo Excel --}}
                    <div class="mb-3">
                        <label for="archivoImportacion" class="form-label">
                            Archivo Excel <span class="text-danger">*</span>
                        </label>
                        <input wire:model="archivoImportacion" type="file"
                               class="form-control @error('archivoImportacion') is-invalid @enderror"
                               id="archivoImportacion" accept=".xlsx,.xls,.csv">
                        @error('archivoImportacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="bi bi-file-earmark-excel"></i>
                            Formatos aceptados: Excel (.xlsx, .xls) o CSV. Tamaño máximo: 10MB
                        </small>
                    </div>

                    {{-- Indicador de carga --}}
                    <div wire:loading wire:target="archivoImportacion" class="alert alert-secondary">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        Cargando archivo...
                    </div>

                    {{-- Vista previa del archivo cargado --}}
                    @if ($archivoImportacion && !$errors->has('archivoImportacion'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            Archivo cargado: <strong>{{ $archivoImportacion->getClientOriginalName() }}</strong>
                            ({{ number_format($archivoImportacion->getSize() / 1024, 2) }} KB)
                        </div>
                    @endif

                    {{-- Mensajes de resultado de importación --}}
                    @if (!empty($mensajesImportacion))
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-clipboard-data"></i> Resultado de la Importación
                                </h6>
                            </div>
                            <div class="card-body">
                                {{-- Resumen --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                            <div class="fs-3 text-success fw-bold">{{ $mensajesImportacion['exitosos'] }}</div>
                                            <small class="text-muted">Exitosos</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                                            <div class="fs-3 text-danger fw-bold">{{ count($mensajesImportacion['errores']) }}</div>
                                            <small class="text-muted">Con Errores</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                                            <div class="fs-3 text-primary fw-bold">{{ $mensajesImportacion['total'] }}</div>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Lista de errores --}}
                                @if (count($mensajesImportacion['errores']) > 0)
                                    <div class="alert alert-danger mb-0">
                                        <h6 class="alert-heading">
                                            <i class="bi bi-exclamation-triangle"></i> Tribunales con Errores
                                        </h6>
                                        <div class="list-group list-group-flush">
                                            @foreach ($mensajesImportacion['errores'] as $error)
                                                <div class="list-group-item bg-transparent px-0 border-0 border-bottom py-2">
                                                    <strong>{{ $error['tribunal'] }}:</strong>
                                                    <span class="text-danger">{{ $error['mensaje'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalImportacion"
                            data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary"
                                wire:loading.attr="disabled"
                                wire:target="importarTribunales"
                                @if(!$archivoImportacion || !$fechaImportacion) disabled @endif>
                            <span wire:loading.remove wire:target="importarTribunales">
                                <i class="bi bi-upload"></i> Importar Tribunales
                            </span>
                            <span wire:loading wire:target="importarTribunales">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Importando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
