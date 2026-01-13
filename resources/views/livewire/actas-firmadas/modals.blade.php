<!-- Modal para Subir Acta Firmada -->
<div wire:ignore.self class="modal fade" id="subirActaModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="subirActaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="subirActaModalLabel">
                    <i class="bi bi-upload"></i>
                    @if($tribunalSeleccionado && $tribunalSeleccionado->acta_firmada_path)
                        Reemplazar Acta Firmada
                    @else
                        Subir Acta Firmada
                    @endif
                </h5>
                <button wire:click="cancelar" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($tribunalSeleccionado)
                    <!-- Información del Tribunal -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle"></i> Información del Tribunal
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong>Estudiante:</strong><br>
                                    {{ $tribunalSeleccionado->estudiante->apellidos }}, {{ $tribunalSeleccionado->estudiante->nombres }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>ID Estudiante:</strong><br>
                                    {{ $tribunalSeleccionado->estudiante->ID_estudiante }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Fecha:</strong><br>
                                    {{ \Carbon\Carbon::parse($tribunalSeleccionado->fecha)->format('d/m/Y') }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Horario:</strong><br>
                                    {{ \Carbon\Carbon::parse($tribunalSeleccionado->hora_inicio)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($tribunalSeleccionado->hora_fin)->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Actual -->
                    @if($tribunalSeleccionado->acta_firmada_path)
                        <div class="alert alert-info">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Estado Actual:</strong> Este tribunal ya tiene un acta firmada subida.<br>
                            <small>Subida el {{ \Carbon\Carbon::parse($tribunalSeleccionado->acta_firmada_fecha)->format('d/m/Y H:i') }}
                            por {{ $tribunalSeleccionado->usuarioSubioActa->name ?? 'Usuario desconocido' }}</small><br>
                            <small class="text-muted">Al subir un nuevo archivo, se reemplazará el anterior.</small>
                        </div>
                    @endif

                    <!-- Formulario de Subida -->
                    <form wire:submit.prevent="subirActa">
                        <div class="mb-3">
                            <label for="actaFirmada" class="form-label">
                                Archivo PDF del Acta Firmada <span class="text-danger">*</span>
                            </label>
                            <input wire:model="actaFirmada" type="file"
                                   class="form-control @error('actaFirmada') is-invalid @enderror"
                                   id="actaFirmada" accept=".pdf">
                            @error('actaFirmada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i>
                                Formato: PDF | Tamaño máximo: 10MB
                            </small>
                        </div>

                        <!-- Indicador de carga -->
                        <div wire:loading wire:target="actaFirmada" class="alert alert-secondary">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando archivo...
                        </div>

                        <!-- Vista previa del archivo cargado -->
                        @if ($actaFirmada && !$errors->has('actaFirmada'))
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i>
                                Archivo cargado: <strong>{{ $actaFirmada->getClientOriginalName() }}</strong>
                                ({{ number_format($actaFirmada->getSize() / 1024, 2) }} KB)
                            </div>
                        @endif

                        <!-- Instrucciones -->
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i> Importante
                            </h6>
                            <ul class="mb-0 small">
                                <li>Asegúrese de que el acta PDF contenga todas las firmas requeridas.</li>
                                <li>Verifique que el archivo sea legible y esté completo.</li>
                                <li>Una vez subida, el acta estará disponible para descarga por el director y docente de apoyo.</li>
                            </ul>
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cancelar">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" wire:click="subirActa"
                        wire:loading.attr="disabled"
                        wire:target="subirActa"
                        @if(!$actaFirmada) disabled @endif>
                    <span wire:loading.remove wire:target="subirActa">
                        <i class="bi bi-upload"></i>
                        @if($tribunalSeleccionado && $tribunalSeleccionado->acta_firmada_path)
                            Reemplazar Acta
                        @else
                            Subir Acta
                        @endif
                    </span>
                    <span wire:loading wire:target="subirActa">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Subiendo...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
