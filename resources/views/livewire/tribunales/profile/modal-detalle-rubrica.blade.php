{{-- Recibe $itemPlanId y ahora $detalleItemRubrica (que es $detalleRubricasParaModal[$itemPlanId]) --}}
@props(['itemPlanId', 'detalleItemRubrica'])

<div class="modal fade" id="detalleRubricaModal_{{ $itemPlanId }}" tabindex="-1"
    aria-labelledby="detalleRubricaModalLabel_{{ $itemPlanId }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detalleRubricaModalLabel_{{ $itemPlanId }}">
                    <i class="bi bi-card-list"></i> Detalle Calificación: {{ $detalleItemRubrica['nombre_item_plan'] ?? 'N/A' }}
                    @if (!empty($detalleItemRubrica['rubrica_plantilla_nombre']))
                        <small class="d-block fw-normal">Plantilla: {{ $detalleItemRubrica['rubrica_plantilla_nombre'] }}</small>
                    @endif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (empty($detalleItemRubrica['componentes']))
                    <p class="text-muted text-center">No hay detalle de componentes o calificaciones para mostrar.</p>
                @else
                    @foreach ($detalleItemRubrica['componentes'] as $componenteId => $datosComponenteModal)
                        <div class="mb-4 p-3 border rounded shadow-sm bg-light">
                            <h6 class="text-primary border-bottom pb-2 mb-2">
                                <i class="bi bi-diagram-2-fill"></i> Componente: <strong>{{ $datosComponenteModal['nombre_componente_rubrica'] }}</strong>
                            </h6>

                            @if (empty($datosComponenteModal['calificaciones_por_usuario']))
                                <p class="text-muted small">Nadie ha calificado este componente aún.</p>
                            @else
                                @foreach ($datosComponenteModal['calificaciones_por_usuario'] as $userId => $califUsuario)
                                    <div class="mb-3 ps-2 border-start border-3 border-info">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong>{{ $califUsuario['nombre_usuario'] }}</strong>
                                            <span class="badge bg-secondary ms-2">{{ Str::title(Str::lower(Str_replace('_', ' ', $califUsuario['rol_evaluador']))) }}</span>
                                        </div>
                                        @if (!empty($califUsuario['observacion_general_item_miembro']))
                                            <p class="ms-1 mb-2 text-muted border-start border-2 border-secondary ps-2">
                                                <em>Obs. General del Ítem (por este miembro): {{ $califUsuario['observacion_general_item_miembro'] }}</em>
                                            </p>
                                        @endif
                                        @if (isset($califUsuario['criterios_evaluados']) && !empty($califUsuario['criterios_evaluados']))
                                            <ul class="list-unstyled ps-1 mb-0">
                                                @foreach ($califUsuario['criterios_evaluados'] as $criterioId => $datosCrit)
                                                    <li class="border-bottom py-1">
                                                        {{ $datosCrit['nombre_criterio_rubrica'] }}:
                                                        <span class="text-info fw-bold">{{ $datosCrit['calificacion_elegida_nombre'] ?? 'N/R' }}
                                                            ({{ $datosCrit['calificacion_elegida_valor'] ?? 'N/R' }})</span>
                                                        @if (!empty($datosCrit['observacion']))
                                                            <br><em class="text-muted" style="font-size:0.9em;">  - Obs: {{ $datosCrit['observacion'] }}</em>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted small"><em>No calificó criterios para este componente.</em></p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
