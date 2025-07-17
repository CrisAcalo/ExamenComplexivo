<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Calificación - {{ $tribunal->estudiante->nombres_completos_id ?? 'Estudiante' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            margin: 15px;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }

        .student-info {
            margin: 15px 0;
            text-align: center;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }

        .evaluation-table td, .evaluation-table th {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .evaluation-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .final-table {
            width: 70%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        .final-table td, .final-table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .final-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .approval-section {
            margin-top: 30px;
            text-align: center;
        }

        .signatures {
            margin-top: 50px;
        }

        .signature-line {
            display: inline-block;
            width: 30%;
            margin: 0 1.5%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line .name-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
            font-size: 9px;
        }

        .footer-info {
            margin-top: 40px;
            text-align: center;
        }

        .underline-field {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <div class="header">
        <h1>ANEXO "8" ACTA DE CALIFICACIÓN DEL EXAMEN DE CARÁCTER COMPLEXIVO</h1>
        <h2>VICERRECTORADO DE DOCENCIA</h2>
        <h2>UNIDAD DE REGISTRO</h2>
    </div>

    {{-- Información del estudiante --}}
    <div style="margin: 20px 0;">
        <div style="text-align: center; margin-bottom: 10px;">
            <strong>CARRERA:</strong> {{ $tribunal->carrerasPeriodo->carrera->nombre ?? 'N/A' }}
        </div>
        <div style="text-align: center; margin-bottom: 10px;">
            <strong>MODALIDAD:</strong> En línea
        </div>
    </div>

    <div class="section-title">
        ACTA DE CALIFICACIÓN DEL EXAMEN COMPLEXIVO
    </div>

    <div class="student-info">
        <strong>Nombre del estudiante:</strong>
        <span class="underline-field">{{ $tribunal->estudiante->nombres_completos_id ?? 'N/A' }}</span>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <strong>ID:</strong>
        <span class="underline-field">{{ $tribunal->estudiante->id ?? 'N/A' }}</span>
    </div>

    <div class="section-title">
        EVALUACIONES DE LOS COMPONENTES DEL EXAMEN COMPLEXIVO
    </div>

    @if($planEvaluacionActivo && $resumenNotasCalculadas)
        {{-- Separar ítems por tipo --}}
        @php
            $itemsNotaDirecta = [];
            $itemsRubrica = [];

            foreach($resumenNotasCalculadas as $itemPlanId => $itemResumen) {
                if($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                    $itemsNotaDirecta[] = $itemResumen;
                } elseif($itemResumen['tipo_item'] === 'RUBRICA_TABULAR') {
                    $itemsRubrica[] = $itemResumen;
                }
            }
        @endphp

        {{-- Sección de Evaluación Parte Escrita (Nota Directa) --}}
        @if(!empty($itemsNotaDirecta))
            <div class="section-title" style="font-size: 10px; margin-top: 30px;">
                EVALUACIÓN PARTE ESCRITA (RESOLUCIÓN DEL PROBLEMA PROFESIONAL / ESTUDIO DE CASO) DEL EXAMEN DE CARÁCTER COMPLEXIVO
            </div>

            <table class="evaluation-table">
                <tr>
                    <th style="width: 40%;">Componentes</th>
                    <th style="width: 30%;">Nota sobre 20 pts.</th>
                    <th style="width: 15%;">Ponderación</th>
                    <th style="width: 15%;">Nota</th>
                </tr>
                @foreach($itemsNotaDirecta as $item)
                <tr>
                    <td class="text-left">{{ $item['nombre_item_plan'] }}</td>
                    <td>{{ !is_null($item['nota_tribunal_sobre_20']) ? number_format($item['nota_tribunal_sobre_20'], 1) : '________' }}</td>
                    <td>{{ $item['ponderacion_global'] }}%</td>
                    <td>{{ number_format($item['puntaje_ponderado_item'], 1) }}</td>
                </tr>
                @endforeach
            </table>
        @endif

        {{-- Sección de Evaluación de la Defensa/Sustentación (Rúbricas) --}}
        @if(!empty($itemsRubrica))
            <div class="section-title" style="font-size: 10px; margin-top: 30px;">
                EVALUACIÓN DE LA DEFENSA/SUSTENTACIÓN/EXPOSICIÓN ORAL DE LA RESOLUCIÓN DEL PROBLEMA PROFESIONAL O ESTUDIO DE CASO
            </div>

            <table class="evaluation-table">
                <tr>
                    <th style="width: 40%;">Componentes</th>
                    <th style="width: 20%;">Nota sobre 20 pts.</th>
                    <th style="width: 20%;">Ponderación</th>
                    <th style="width: 20%;">Nota</th>
                </tr>
                @foreach($itemsRubrica as $item)
                <tr>
                    <td class="text-left">{{ $item['nombre_item_plan'] }}</td>
                    <td>{{ !is_null($item['nota_tribunal_sobre_20']) ? number_format($item['nota_tribunal_sobre_20'], 1) : '________' }}</td>
                    <td>{{ $item['ponderacion_global'] }}%</td>
                    <td>{{ number_format($item['puntaje_ponderado_item'], 1) }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #f5f5f5;">
                    <td class="text-left"><strong>2. TOTAL DEFENSA/SUSTENTACIÓN/EXPOSICIÓN ORAL</strong></td>
                    <td></td>
                    <td><strong>{{ array_sum(array_column($itemsRubrica, 'ponderacion_global')) }} %</strong></td>
                    <td><strong>{{ number_format(array_sum(array_column($itemsRubrica, 'puntaje_ponderado_item')), 1) }}</strong></td>
                </tr>
            </table>
        @endif

        {{-- Tabla de Nota Final --}}
        <div class="section-title" style="margin-top: 40px;">
            NOTA FINAL DE LA OPCIÓN DE TITULACIÓN DE EXAMEN DE CARÁCTER COMPLEXIVO
        </div>

        <table class="final-table">
            <tr>
                <th style="width: 60%;">Componentes</th>
                <th style="width: 40%;">Nota sobre 20 pts.</th>
            </tr>
            @if(!empty($itemsNotaDirecta))
            <tr>
                <td class="text-left">Nota del Cuestionario</td>
                <td>{{ number_format(array_sum(array_column($itemsNotaDirecta, 'puntaje_ponderado_item')), 1) }}</td>
            </tr>
            @endif
            @if(!empty($itemsRubrica))
            <tr>
                <td class="text-left">Nota de la defensa/sustentación/exposición oral de la resolución del problema profesional / estudio de caso</td>
                <td>{{ number_format(array_sum(array_column($itemsRubrica, 'puntaje_ponderado_item')), 1) }}</td>
            </tr>
            @endif
            <tr style="background-color: #f5f5f5;">
                <td class="text-left"><strong>NOTA FINAL DEL EXAMEN DE CARÁCTER COMPLEXIVO (1+2) TOTAL</strong></td>
                <td><strong>{{ is_numeric($notaFinalCalculadaDelTribunal) ? number_format($notaFinalCalculadaDelTribunal, 1) : 'N/C' }}</strong></td>
            </tr>
        </table>

        {{-- Nota final en letras --}}
        <div style="margin: 20px 0; text-align: left;">
            <strong>NOTA FINAL (NÚMEROS Y LETRAS):</strong>
            <span class="underline-field" style="min-width: 300px;">
                {{ is_numeric($notaFinalCalculadaDelTribunal) ? number_format($notaFinalCalculadaDelTribunal, 1) : 'N/C' }}
            </span>
        </div>

        {{-- Aprobación --}}
        <div class="approval-section">
            <div style="margin: 20px 0;">
                <strong>Aprobación: SÍ _____ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NO _____</strong>
            </div>
            <div>
                <strong>Fecha:</strong> {{ $tribunal->fecha ? date('d/m/Y', strtotime($tribunal->fecha)) : date('d/m/Y') }}
            </div>
        </div>

        {{-- Firmas --}}
        <div class="signatures">
            @php
                $presidente = null;
                $integrante1 = null;
                $integrante2 = null;

                foreach($tribunal->miembrosTribunales as $miembro) {
                    if($miembro->status === 'PRESIDENTE') {
                        $presidente = $miembro->user;
                    } elseif($miembro->status === 'INTEGRANTE1') {
                        $integrante1 = $miembro->user;
                    } elseif($miembro->status === 'INTEGRANTE2') {
                        $integrante2 = $miembro->user;
                    }
                }
            @endphp

            <div class="signature-line">
                <div class="name-line">
                    <div>{{ $presidente ? $presidente->name : 'N/A' }}</div>
                    <div style="margin-top: 5px;"><strong>CI. _____________</strong></div>
                    <div style="margin-top: 10px;"><strong>Presidente del Tribunal</strong></div>
                </div>
            </div>

            <div class="signature-line">
                <div class="name-line">
                    <div>{{ $integrante1 ? $integrante1->name : 'N/A' }}</div>
                    <div style="margin-top: 5px;"><strong>CI. _____________</strong></div>
                    <div style="margin-top: 10px;"><strong>Miembro 2</strong></div>
                </div>
            </div>

            <div class="signature-line">
                <div class="name-line">
                    <div>{{ $integrante2 ? $integrante2->name : 'N/A' }}</div>
                    <div style="margin-top: 5px;"><strong>CI. _____________</strong></div>
                    <div style="margin-top: 10px;"><strong>Miembro 3</strong></div>
                </div>
            </div>
        </div>

        {{-- Footer con Director de Carrera --}}
        <div class="footer-info">
            <div style="margin-top: 80px;">
                <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px;">
                    <strong>{{ $tribunal->carrerasPeriodo->director->name ?? 'N/A' }}</strong>
                </div>
                <div style="margin-top: 10px;">
                    <strong>Director de Carrera</strong>
                </div>
            </div>
        </div>

    @else
        <div style="text-align: center; margin: 50px 0;">
            <p>No hay datos de evaluación disponibles para generar el acta.</p>
        </div>
    @endif

</body>
</html>
