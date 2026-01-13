{{-- Recibe $itemPlanId y ahora $detalleItemRubrica (que es $detalleRubricasParaModal[$itemPlanId]) --}}
@props(['itemPlanId', 'detalleItemRubrica'])

<div class="modal fade" id="detalleRubricaModal_{{ $itemPlanId }}" tabindex="-1"
    aria-labelledby="detalleRubricaModalLabel_{{ $itemPlanId }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient bg-primary text-white">
                <div>
                    <h4 class="modal-title mb-1" id="detalleRubricaModalLabel_{{ $itemPlanId }}">
                        <i class="bi bi-clipboard-data me-2"></i>{{ $detalleItemRubrica['nombre_item_plan'] ?? 'N/A' }}
                    </h4>
                    @if (!empty($detalleItemRubrica['rubrica_plantilla_nombre']))
                        <small class="opacity-75">
                            <i class="bi bi-file-text me-1"></i>Plantilla de Rúbrica: {{ $detalleItemRubrica['rubrica_plantilla_nombre'] }}
                        </small>
                    @endif
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                @if (empty($detalleItemRubrica['componentes']))
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">Sin calificaciones disponibles</h5>
                        <p class="text-muted">No hay calificaciones de rúbrica para mostrar en este ítem.</p>
                    </div>
                @else
                    {{-- Tabla única con todas las calificaciones --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary sticky-top">
                                <tr>
                                    <th style="width: 20%;">Componente</th>
                                    <th style="width: 15%;">Evaluador</th>
                                    <th style="width: 10%;">Rol</th>
                                    <th style="width: 25%;">Criterio</th>
                                    <th style="width: 15%;">Calificación</th>
                                    <th style="width: 15%;">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detalleItemRubrica['componentes'] as $componenteId => $datosComponente)
                                    @php
                                        $numeroCalificadores = count($datosComponente['calificaciones_por_usuario'] ?? []);
                                        $primeraFila = true;
                                    @endphp

                                    @if (empty($datosComponente['calificaciones_por_usuario']))
                                        <tr>
                                            <td class="text-center fw-bold">{{ $datosComponente['nombre_componente_rubrica'] }}</td>
                                            <td colspan="5" class="text-muted text-center">
                                                <em>No hay calificaciones para este componente</em>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($datosComponente['calificaciones_por_usuario'] as $userId => $datosUsuario)
                                            @php
                                                $numeroCriterios = count($datosUsuario['criterios_evaluados'] ?? []);
                                                $primeraFilaUsuario = true;
                                            @endphp

                                            @if (empty($datosUsuario['criterios_evaluados']))
                                                <tr>
                                                    @if ($primeraFila)
                                                        <td rowspan="{{ $numeroCalificadores }}" class="align-middle text-center fw-bold bg-light">
                                                            {{ $datosComponente['nombre_componente_rubrica'] }}
                                                        </td>
                                                        @php $primeraFila = false; @endphp
                                                    @endif
                                                    <td class="fw-semibold">{{ $datosUsuario['nombre_usuario'] }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $datosUsuario['rol_evaluador'] }}</span>
                                                    </td>
                                                    <td colspan="3" class="text-muted">
                                                        <em>Sin criterios calificados</em>
                                                    </td>
                                                </tr>
                                            @else
                                                @foreach ($datosUsuario['criterios_evaluados'] as $criterioId => $datosCriterio)
                                                    <tr>
                                                        @if ($primeraFila)
                                                            <td rowspan="{{ array_sum(array_map(fn($u) => max(1, count($u['criterios_evaluados'] ?? [])), $datosComponente['calificaciones_por_usuario'])) }}"
                                                                class="align-middle text-center fw-bold bg-light">
                                                                {{ $datosComponente['nombre_componente_rubrica'] }}
                                                            </td>
                                                            @php $primeraFila = false; @endphp
                                                        @endif

                                                        @if ($primeraFilaUsuario)
                                                            <td rowspan="{{ $numeroCriterios }}" class="align-middle fw-semibold">
                                                                {{ $datosUsuario['nombre_usuario'] }} {{ $datosUsuario['apellido_usuario'] ?? '' }}
                                                            </td>
                                                            <td rowspan="{{ $numeroCriterios }}" class="align-middle">
                                                                <span class="badge
                                                                    @if($datosUsuario['rol_evaluador'] === 'PRESIDENTE') bg-warning text-dark
                                                                    @elseif($datosUsuario['rol_evaluador'] === 'INTEGRANTE1' || $datosUsuario['rol_evaluador'] === 'INTEGRANTE2') bg-info
                                                                    @elseif($datosUsuario['rol_evaluador'] === 'DIRECTOR_CARRERA') bg-success
                                                                    @elseif($datosUsuario['rol_evaluador'] === 'DOCENTE_APOYO') bg-primary
                                                                    @else bg-secondary
                                                                    @endif">
                                                                    {{ $datosUsuario['rol_evaluador'] }}
                                                                </span>
                                                            </td>
                                                            @php $primeraFilaUsuario = false; @endphp
                                                        @endif

                                                        <td>{{ $datosCriterio['nombre_criterio_rubrica'] }}</td>
                                                        <td>
                                                            @if ($datosCriterio['calificacion_elegida_nombre'])
                                                                <span class="fw-semibold text-primary">
                                                                    {{ $datosCriterio['calificacion_elegida_nombre'] }}
                                                                </span>
                                                                @if ($datosCriterio['calificacion_elegida_valor'])
                                                                    <small class="text-muted">({{ $datosCriterio['calificacion_elegida_valor'] }})</small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">Sin calificar</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($datosCriterio['observacion'])
                                                                <small class="text-muted">{{ $datosCriterio['observacion'] }}</small>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
