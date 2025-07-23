<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acta de Calificación - {{ $tribunal->estudiante->nombres_completos_id ?? 'Estudiante' }}</title>
    <style>
        @page {
            margin: 10mm 20mm 10mm 20mm;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-before: always;
        }

        .header-container {
            width: 100%;
            margin-bottom: 40px;
            margin-top: 0;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .logo-section {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            padding-right: 10px;
        }

        .logo-section img {
            width: 150px;
            height: auto;
        }

        .title-section {
            display: table-cell;
            width: 70%;
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }

        .right-section {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
            font-size: 14px;
            padding-left: 10px;
        }

        .main-title {
            font-size: 13px;
            font-weight: bold;
            margin: 2px 0;
            text-align: center;
            line-height: 1.2;
        }

        .subtitle {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            text-align: center;
            line-height: 1.2;
        }

        .career-info {
            text-align: center;
            margin: 15px 0 25px 0;
            font-size: 12px;
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
        }

        .student-info {
            margin: 15px 0;
            text-align: center;
            font-size: 12px;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }

        .evaluation-table td,
        .evaluation-table th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .evaluation-table th {
            font-weight: bold;
            background-color: transparent;
        }

        .text-left {
            text-align: left !important;
        }

        .final-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }

        .final-table td,
        .final-table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .final-table th {
            font-weight: bold;
            background-color: transparent;
        }

        .approval-section {
            margin: 20px 0;
            text-align: left;
            font-size: 12px;
        }

        .signatures {
            margin-top: 60px;
            width: 100%;
        }

        .signature-row {
            display: table;
            vertical-align: top;
            width: 100%;
            margin-top: 100px;
        }

        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 0 20px;
            padding-top: 5px;
            font-size: 10px;
            text-align: center;
        }

        .director-signature {
            text-align: center;
            margin-top: 150px;
        }

        .director-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 11px;
        }

        .underline-field {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            padding-bottom: 2px;
        }

        .footer-codes {
            position: fixed;
            bottom: 15px;
            left: 20px;
            font-size: 8px;
            line-height: 1.1;
        }

        .footer-ref {
            position: fixed;
            bottom: 15px;
            right: 20px;
            font-size: 8px;
        }

        .page-number {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
        }

        .page-wrapper {
            position: relative;
            min-height: 250mm;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="header-container">
            <div class="header-top">
                <div class="logo-section">
                    @if ($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo ESPE">
                    @else
                        <div
                            style="width: 70px; height: 70px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px;">
                            LOGO</div>
                    @endif
                </div>
                <div class="right-section">
                    Vicerrectorado de Docencia<br>
                    Unidad de Desarrollo Educativo
                </div>
            </div>
        </div>

        {{-- Información de la carrera --}}
        <div class="career-info">

            <div class="main-title">ANEXO "8" ACTA DE CALIFICACIÓN DEL EXAMEN DE CARÁCTER COMPLEXIVO</div>
            <div class="subtitle" style="margin-top: 15px;margin-bottom: 15px">VICERRECTORADO DE DOCENCIA</div>
            <div class="subtitle" style="margin-bottom: 40px">UNIDAD DE REGISTRO</div>
            <div><strong>CARRERA:</strong> {{ $tribunal->carrerasPeriodo->carrera->nombre ?? 'N/A' }}</div>
            <div style="margin-top: 15px;margin-bottom:40px;"><strong>MODALIDAD:</strong> En línea</div>
        </div>

        <div class="section-title">
            ACTA DE CALIFICACIÓN DEL EXAMEN COMPLEXIVO
        </div>

        <div class="student-info">
            Nombre del estudiante:
            <span class="">{{ $tribunal->estudiante->nombres ?? 'N/A' }}
                {{ $tribunal->estudiante->apellidos ?? 'N/A' }}</span>
            &nbsp;&nbsp;&nbsp;&nbsp;
            ID:
            <span class="">{{ $tribunal->estudiante->ID_estudiante ?? 'N/A' }}</span>
        </div>

        <div class="section-title">
            EVALUACIONES DE LOS COMPONENTES DEL EXAMEN COMPLEXIVO
        </div>

        @if ($planEvaluacionActivo && $resumenNotasCalculadas)
            {{-- Separar ítems por tipo --}}
            @php
                $itemsNotaDirecta = [];
                $itemsRubrica = [];

                foreach ($resumenNotasCalculadas as $itemPlanId => $itemResumen) {
                    if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                        $itemsNotaDirecta[] = $itemResumen;
                    } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR') {
                        $itemsRubrica[] = $itemResumen;
                    }
                }
            @endphp

            {{-- Sección de Evaluación Parte Escrita (Nota Directa) --}}
            @if (!empty($itemsNotaDirecta))
                <div class="section-title" style="font-size: 11px; margin-top: 35px; margin-bottom: 35px;;">
                    EVALUACIÓN PARTE ESCRITA (RESOLUCIÓN DEL PROBLEMA PROFESIONAL / ESTUDIO DE CASO)<br>
                    DEL EXAMEN DE CARÁCTER COMPLEXIVO
                </div>

                <table class="evaluation-table" style="width: 95%;margin: 0 auto;">
                    <tr>
                        <th style="width: 35%;">Componentes</th>
                        <th style="width: 25%;">Nota sobre 20 pts.</th>
                        <th style="width: 20%;">Ponderación</th>
                        <th style="width: 20%;">Nota</th>
                    </tr>
                    @foreach ($itemsNotaDirecta as $item)
                        <tr>
                            <td class="text-left"><strong>{{ $loop->iteration }}.
                                    {{ $item['nombre_item_plan'] ?? 'CUESTIONARIO' }}</strong></td>
                            <td>{{ number_format($item['nota_tribunal_sobre_20'] ?? 0, 2) }}</td>
                            <td>{{ $item['ponderacion_global'] ?? 50 }}%</td>
                            <td>{{ number_format($item['puntaje_ponderado_item'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            {{-- Sección de Evaluación de la Defensa/Sustentación (Rúbricas) --}}
            @if (!empty($itemsRubrica))
                <div class="section-title" style="font-size: 11px; margin-top: 35px; margin-bottom: 35px;;">
                    EVALUACIÓN DE LA DEFENSA/SUSTENTACIÓN/EXPOSICIÓN ORAL DE LA RESOLUCIÓN DEL PROBLEMA PROFESIONAL O
                    ESTUDIO DE CASO
                </div>

                <table class="evaluation-table" style="width: 85%;margin: 0 auto;">
                    <tr>
                        <th style="width: 40%;">Componentes</th>
                        <th style="width: 20%;">Nota sobre 20 pts.</th>
                        <th style="width: 20%;">Ponderación</th>
                        <th style="width: 20%;">Nota</th>
                    </tr>
                    <tr>
                        <td class="text-left">
                            Parte escrita (resolución del problema profesional / estudio de caso)
                        </td>
                        <td>{{ !empty($itemsNotaDirecta) ? number_format($itemsNotaDirecta[0]['nota_tribunal_sobre_20'] ?? 0, 2) : '0.00' }}
                        </td>
                        <td>25%</td>
                        <td>{{ !empty($itemsNotaDirecta) ? number_format(($itemsNotaDirecta[0]['nota_tribunal_sobre_20'] ?? 0) * 0.25, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-left">
                            Evaluación de la defensa / sustentación/ exposición oral de la resolución del problema
                            profesional / estudio de caso
                        </td>
                        <td>{{ !empty($itemsRubrica) ? number_format($itemsRubrica[0]['nota_tribunal_sobre_20'] ?? 0, 2) : '0.00' }}
                        </td>
                        <td>25%</td>
                        <td>{{ !empty($itemsRubrica) ? number_format(($itemsRubrica[0]['nota_tribunal_sobre_20'] ?? 0) * 0.25, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td class="text-left" colspan="2"><strong>2. TOTAL DEFENSA/SUSTENTACIÓN/EXPOSICIÓN
                                ORAL</strong>
                        </td>
                        <td><strong>50 %</strong></td>
                        <td><strong>{{ number_format((!empty($itemsNotaDirecta) ? ($itemsNotaDirecta[0]['nota_tribunal_sobre_20'] ?? 0) * 0.25 : 0) + (!empty($itemsRubrica) ? ($itemsRubrica[0]['nota_tribunal_sobre_20'] ?? 0) * 0.25 : 0), 2) }}</strong>
                        </td>
                    </tr>
                </table>
            @endif
        @endif

        {{-- Códigos de pie de página --}}
        <div class="footer-codes">
            Código de documento: {{ $tribunal->generarCodigoDocumento() }}<br>
            Código de proceso: GDOC-ATAD-5-3
        </div>

        <div class="footer-ref">
            Rev: UPDI: {{ \Carbon\Carbon::now()->format('Y-M-d') }}
        </div>

        <div class="page-number">1</div>
    </div>

    {{-- PÁGINA 2 --}}
    <div class="page-break">
        <div class="page-wrapper">
            <div class="header-container">
                <div class="header-top">
                    <div class="logo-section">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" alt="Logo ESPE">
                        @else
                            <div
                                style="width: 70px; height: 70px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px;">
                                LOGO</div>
                        @endif
                    </div>
                    <div class="right-section">
                        Vicerrectorado de Docencia<br>
                        Unidad de Desarrollo Educativo
                    </div>
                </div>
            </div>

            <div class="career-info">
                <div class="main-title">NOTA FINAL DE LA OPCIÓN DE TITULACIÓN DE EXAMEN DE CARÁCTER COMPLEXIVO</div>
            </div>
            @if ($planEvaluacionActivo && $resumenNotasCalculadas)
                @php
                    $notaFinalTotal = $notaFinalCalculadaDelTribunal ?? 0;

                    // Convertir nota a letras
                    $notaEnLetras = '';
                    if ($notaFinalTotal >= 18) {
                        $notaEnLetras = 'EXCELENTE';
                    } elseif ($notaFinalTotal >= 16) {
                        $notaEnLetras = 'MUY BUENO';
                    } elseif ($notaFinalTotal >= 14) {
                        $notaEnLetras = 'BUENO';
                    } elseif ($notaFinalTotal >= 12) {
                        $notaEnLetras = 'REGULAR';
                    } else {
                        $notaEnLetras = 'INSUFICIENTE';
                    }

                    // Función para convertir números a letras con formato PUNTO
                    function numeroALetrasConPunto($numero) {
                        $numeros = [
                            0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO',
                            5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
                            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
                            15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO',
                            19 => 'DIECINUEVE', 20 => 'VEINTE'
                        ];

                        $parteEntera = floor($numero);
                        $parteDecimal = round(($numero - $parteEntera) * 100);

                        $textoEntera = $numeros[$parteEntera] ?? 'ERROR';

                        // Siempre mostrar dos dígitos decimales
                        $digitoDecenas = floor($parteDecimal / 10);
                        $digitoUnidades = $parteDecimal % 10;

                        $textoDecimal = $numeros[$digitoDecenas] . ' ' . $numeros[$digitoUnidades];

                        return $textoEntera . ' PUNTO ' . $textoDecimal . ' (' . number_format($numero, 2) . ')';
                    }

                    $numeroEnLetras = numeroALetrasConPunto($notaFinalTotal);
                @endphp

                <table class="final-table" style="margin-top: 40px; width: 75%; margin: 0 auto;">
                    <tr>
                        <th style="width: 70%;">Componentes</th>
                        <th style="width: 30%;">Nota sobre 20 pts.</th>
                    </tr>
                    <tr>
                        <td class="text-left">Nota del Cuestionario</td>
                        <td>{{ !empty($itemsNotaDirecta) ? number_format($itemsNotaDirecta[0]['nota_tribunal_sobre_20'] ?? 0, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-left">Nota de la defensa/sustentación/exposición oral de la resolución del
                            problema
                            profesional / estudio de caso</td>
                        <td>{{ !empty($itemsRubrica) ? number_format($itemsRubrica[0]['nota_tribunal_sobre_20'] ?? 0, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td class="text-left"><strong>NOTA FINAL DEL EXAMEN DE CARÁCTER COMPLEXIVO (1+2) TOTAL</strong>
                        </td>
                        <td><strong>{{ number_format($notaFinalTotal, 2) }}</strong></td>
                    </tr>
                </table>

                {{-- Nota final en letras --}}
                <div class="approval-section" style="margin-top: 30px;">
                    <strong>NOTA FINAL (NÚMEROS Y LETRAS):</strong>
                    <span class="underline-field" style="min-width: 400px;">
                        {{ $numeroEnLetras }}
                    </span>
                </div>

                {{-- Aprobación --}}
                <div class="approval-section" style="margin-top: 30px;">
                    <div style="margin: 20px 0;">
                        <strong>Aprobación:</strong>
                        @if ($notaFinalTotal >= 14)
                            <strong>SÍ</strong> __X__
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <strong>NO</strong> _____
                        @else
                            <strong>SÍ</strong> _____
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <strong>NO</strong> __X__
                        @endif
                    </div>
                    <div style="text-align: right; margin-top: 20px;margin-right:135px;">
                        Fecha: {{ \Carbon\Carbon::parse($tribunal->fecha ?? now())->format('d/m/Y') }}
                    </div>
                </div>

                {{-- Firmas de los miembros del tribunal --}}
                @php
                    $presidente = $tribunal->miembrosTribunales->where('status', 'PRESIDENTE')->first();
                    $integrante1 = $tribunal->miembrosTribunales->where('status', 'INTEGRANTE1')->first();
                    $integrante2 = $tribunal->miembrosTribunales->where('status', 'INTEGRANTE2')->first();
                @endphp

                <div class="signature-row">
                    <div class="signature-cell">
                        <div class="signature-line">
                            CI. {{ $presidente->user->cedula ?? '........................' }}<br>
                            {{ $presidente->user->name ?? 'Presidente del Tribunal' }}<br>
                            <strong>Presidente del Tribunal</strong>
                        </div>
                    </div>

                    <div class="signature-cell">
                        <div class="signature-line">
                            CI. {{ $integrante1->user->cedula ?? '........................' }}<br>
                            {{ $integrante1->user->name ?? 'Miembro 2' }}<br>
                            <strong>Miembro 2</strong>
                        </div>
                    </div>

                    <div class="signature-cell">
                        <div class="signature-line">
                            CI. {{ $integrante2->user->cedula ?? '........................' }}<br>
                            {{ $integrante2->user->name ?? 'Miembro 3' }}<br>
                            <strong>Miembro 3</strong>
                        </div>
                    </div>
                </div>

                {{-- Firma del Director de Carrera --}}
                <div class="director-signature">
                    <div class="director-line">
                        {{ $tribunal->carrerasPeriodo->director->name ?? 'Director de Carrera' }}<br>
                        <strong>Director de Carrera</strong>
                    </div>
                </div>
            @else
                <div style="text-align: center; margin: 50px 0;">
                    <p>No hay datos de evaluación disponibles para generar el acta.</p>
                </div>
            @endif
            <div class="footer-codes">
                Código de documento: {{ $tribunal->generarCodigoDocumento() }}<br>
                Código de proceso: GDOC-ATAD-5-3
            </div>

            <div class="page-number">2</div>
            <div class="footer-ref">
                Rev: UPDI: {{ \Carbon\Carbon::now()->format('Y-M-d') }}
            </div>
        </div>
    </div>
</body>

</html>
