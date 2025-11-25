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

            <div class="main-title">ANEXO "8" ACTA DE CALIFICACIÓN DEL EXÁMEN DE CARÁCTER COMPLEXIVO</div>
            <div class="subtitle" style="margin-top: 15px;margin-bottom: 15px">VICERRECTORADO DE DOCENCIA</div>
            <div class="subtitle" style="margin-bottom: 40px">UNIDAD DE REGISTRO</div>
            @php
                $nombreCarrera = $tribunal->carrerasPeriodo->carrera->nombre ?? 'N/A';
                // Convertir a mayúsculas y eliminar las dos últimas palabras
                $nombreCarreraMayus = mb_strtoupper($nombreCarrera, 'UTF-8');
                $partes = explode(' ', $nombreCarreraMayus);
                if (count($partes) > 2) {
                    array_splice($partes, -2);
                }
                $nombreCarreraFinal = implode(' ', $partes);
            @endphp
            <div><strong>CARRERA:</strong> {{ $nombreCarreraFinal }}</div>
            <div style="margin-top: 15px;margin-bottom:40px;"><strong>MODALIDAD:
                </strong>{{ $tribunal->carrerasPeriodo->carrera->modalidad ?? 'N/A' }}</div>
        </div>

        <div class="section-title">
            ACTA DE CALIFICACIÓN DEL EXAMEN COMPLEXIVO
        </div>

        <div class="student-info">
            Nombre del estudiante:
            <span class="">{{ $tribunal->estudiante->apellidos ?? 'N/A' }}
                {{ $tribunal->estudiante->nombres ?? 'N/A' }}</span>
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
                $componentesRubrica = [];

                foreach ($resumenNotasCalculadas as $itemPlanId => $itemResumen) {
                    if ($itemResumen['tipo_item'] === 'NOTA_DIRECTA') {
                        $itemsNotaDirecta[] = $itemResumen;
                    } elseif ($itemResumen['tipo_item'] === 'RUBRICA_TABULAR') {
                        $itemsRubrica[] = $itemResumen;
                    } elseif ($itemResumen['tipo_item'] === 'RUBRICA_COMPONENTE') {
                        $componentesRubrica[] = $itemResumen;
                    }
                }
            @endphp

            {{-- Sección de Evaluación Parte Escrita (Nota Directa) --}}
            @if (!empty($itemsNotaDirecta))
                <div class="section-title" style="font-size: 11px; margin-top: 35px; margin-bottom: 35px;;">
                    EVALUACIÓN COMPONENTE TEÓRICO (SOLUCIÓN DEL CUESTIONARIO) DEL EXAMEN DE CARÁCTER COMPLEXIVO
                </div>

                <table class="evaluation-table" style="width: 95%;margin: 0 auto;">
                    <tr>
                        <th style="width: 35%;">Componente teórico</th>
                        <th style="width: 25%;">Calificación sobre 20 pts.</th>
                        <th style="width: 20%;">Ponderación</th>
                        <th style="width: 20%;">Calificación</th>
                    </tr>
                    @foreach ($itemsNotaDirecta as $item)
                        <tr>
                            <td class="text-center">
                                <strong>
                                    {{ $loop->iteration }}.
                                    {{-- {{ $item['nombre_item_plan'] ?? 'CUESTIONARIO' }} --}}
                                    @if ($loop->iteration == 1)
                                        COMPONENTE TEÓRICO (SOLUCIÓN DEL CUESTIONARIO)
                                    @endif
                                </strong>
                            </td>
                            <td>{{ number_format($item['nota_tribunal_sobre_20'] ?? 0, 2) }}</td>
                            <td>{{ isset($item['ponderacion_global']) ? intval($item['ponderacion_global']) : 50 }}%
                            </td>
                            <td>{{ number_format($item['puntaje_ponderado_item'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            {{-- Sección de Evaluación de la Defensa/Sustentación (Rúbricas) --}}
            @if (!empty($componentesRubrica))
                <div class="section-title" style="font-size: 11px; margin-top: 35px; margin-bottom: 35px;">
                    EVALUACIÓN COMPONENTE PRÁCTICO (PARTE ESCRITA Y DEFENSA/SUSTENTACIÓN/EXPOSICIÓN ORAL DE LA
                    RESOLUCIÓN DEL PROBLEMA PROFESIONAL O ESTUDIO DE CASO) DEL EXAMEN DE CARÁCTER COMPLEXIVO
                </div>

                <table class="evaluation-table" style="width: 95%;margin: 0 auto;">
                    <tr>
                        <th style="width: 40%;">Componentes</th>
                        <th style="width: 20%;">Calificación sobre 20 pts.</th>
                        <th style="width: 20%;">Ponderación</th>
                        <th style="width: 20%;">Calificación</th>
                    </tr>
                    @php $totalRubrica = 0; @endphp
                    @foreach ($componentesRubrica as $item)
                        <tr>
                            <td class="text-left">

                                @if ($loop->first)
                                    Parte escrita (resolución del problema profesional / estudio de caso)
                                @elseif ($loop->iteration == 2)
                                    Defensa / sustentación/ exposición oral de la resolución del problema profesional /
                                    estudio de caso
                                @else
                                    {{ $item['nombre_item_plan'] ?? 'Componente Rúbrica' }}
                                @endif

                            </td>
                            <td>{{ number_format($item['nota_tribunal_sobre_20'] ?? 0, 2) }}</td>
                            <td>{{ isset($item['ponderacion_global']) * 2 ? number_format($item['ponderacion_global'] * 2, 0) : '0' }}%
                            </td>
                            <td>
                                {{ number_format($item['puntaje_ponderado_item'] ?? 0, 2) }}
                                @php $totalRubrica += $item['puntaje_ponderado_item'] ?? 0; @endphp
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>
                            <strong>
                                CALIFICACIÓN COMPONENTE PRÁCTICO
                            </strong>
                        </td>
                        <td></td>
                        <td><strong>{{ number_format(collect($componentesRubrica)->sum('ponderacion_global'), 0) }}
                                %</strong></td>
                        <td></td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td class="text-center" colspan="2">
                            <strong>
                                2. CALIFICACIÓN COMPONENTE PRÁCTICO (APORTE 50% CALIFICACIÓN FINAL)
                            </strong>
                        </td>
                        <td><strong>{{ number_format(collect($componentesRubrica)->sum('ponderacion_global'), 0) }}
                                %</strong></td>
                        <td><strong>{{ number_format($totalRubrica, 2) }}</strong></td>
                    </tr>
                </table>
            @endif
        @endif

        {{-- Códigos de pie de página --}}
        <div class="footer-codes">
            Código de documento: UDED-FOR-V4-2024-013<br>
            Código de proceso: GDOC-ATAD-5-3
        </div>

        <div class="footer-ref">
            Rev: UPDI: 2024-Sep-19
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
                <div class="main-title">
                    CALIFICACIÓN FINAL DE LA OPCIÓN DE TITULACIÓN DE EXÁMEN DE CARÁCTER COMPLEXIVO
                </div>
            </div>
            @if ($planEvaluacionActivo && $resumenNotasCalculadas)
                @php
                    // Calcular la nota final como la suma de los puntajes ponderados
                    $sumaSinRedondear =
                        (!empty($itemsNotaDirecta) ? $itemsNotaDirecta[0]['puntaje_ponderado_item'] ?? 0 : 0) +
                        (!empty($itemsRubrica) ? $itemsRubrica[0]['puntaje_ponderado_item'] ?? 0 : 0);

                    // Aplicar el mismo redondeo que se usa en la tabla
                    $notaFinalTotal = round($sumaSinRedondear, 2);

                    // Función para convertir números a letras con formato PUNTO
                    function numeroALetrasConPunto($numero)
                    {
                        $numeros = [
                            0 => 'CERO',
                            1 => 'UNO',
                            2 => 'DOS',
                            3 => 'TRES',
                            4 => 'CUATRO',
                            5 => 'CINCO',
                            6 => 'SEIS',
                            7 => 'SIETE',
                            8 => 'OCHO',
                            9 => 'NUEVE',
                            10 => 'DIEZ',
                            11 => 'ONCE',
                            12 => 'DOCE',
                            13 => 'TRECE',
                            14 => 'CATORCE',
                            15 => 'QUINCE',
                            16 => 'DIECISÉIS',
                            17 => 'DIECISIETE',
                            18 => 'DIECIOCHO',
                            19 => 'DIECINUEVE',
                            20 => 'VEINTE',
                            21 => 'VEINTIUNO',
                            22 => 'VEINTIDÓS',
                            23 => 'VEINTITRÉS',
                            24 => 'VEINTICUATRO',
                            25 => 'VEINTICINCO',
                            26 => 'VEINTISÉIS',
                            27 => 'VEINTISIETE',
                            28 => 'VEINTIOCHO',
                            29 => 'VEINTINUEVE',
                            30 => 'TREINTA',
                            31 => 'TREINTA Y UNO',
                            32 => 'TREINTA Y DOS',
                            33 => 'TREINTA Y TRES',
                            34 => 'TREINTA Y CUATRO',
                            35 => 'TREINTA Y CINCO',
                            36 => 'TREINTA Y SEIS',
                            37 => 'TREINTA Y SIETE',
                            38 => 'TREINTA Y OCHO',
                            39 => 'TREINTA Y NUEVE',
                            40 => 'CUARENTA',
                            41 => 'CUARENTA Y UNO',
                            42 => 'CUARENTA Y DOS',
                            43 => 'CUARENTA Y TRES',
                            44 => 'CUARENTA Y CUATRO',
                            45 => 'CUARENTA Y CINCO',
                            46 => 'CUARENTA Y SEIS',
                            47 => 'CUARENTA Y SIETE',
                            48 => 'CUARENTA Y OCHO',
                            49 => 'CUARENTA Y NUEVE',
                            50 => 'CINCUENTA',
                            51 => 'CINCUENTA Y UNO',
                            52 => 'CINCUENTA Y DOS',
                            53 => 'CINCUENTA Y TRES',
                            54 => 'CINCUENTA Y CUATRO',
                            55 => 'CINCUENTA Y CINCO',
                            56 => 'CINCUENTA Y SEIS',
                            57 => 'CINCUENTA Y SIETE',
                            58 => 'CINCUENTA Y OCHO',
                            59 => 'CINCUENTA Y NUEVE',
                            60 => 'SESENTA',
                            61 => 'SESENTA Y UNO',
                            62 => 'SESENTA Y DOS',
                            63 => 'SESENTA Y TRES',
                            64 => 'SESENTA Y CUATRO',
                            65 => 'SESENTA Y CINCO',
                            66 => 'SESENTA Y SEIS',
                            67 => 'SESENTA Y SIETE',
                            68 => 'SESENTA Y OCHO',
                            69 => 'SESENTA Y NUEVE',
                            70 => 'SETENTA',
                            71 => 'SETENTA Y UNO',
                            72 => 'SETENTA Y DOS',
                            73 => 'SETENTA Y TRES',
                            74 => 'SETENTA Y CUATRO',
                            75 => 'SETENTA Y CINCO',
                            76 => 'SETENTA Y SEIS',
                            77 => 'SETENTA Y SIETE',
                            78 => 'SETENTA Y OCHO',
                            79 => 'SETENTA Y NUEVE',
                            80 => 'OCHENTA',
                            81 => 'OCHENTA Y UNO',
                            82 => 'OCHENTA Y DOS',
                            83 => 'OCHENTA Y TRES',
                            84 => 'OCHENTA Y CUATRO',
                            85 => 'OCHENTA Y CINCO',
                            86 => 'OCHENTA Y SEIS',
                            87 => 'OCHENTA Y SIETE',
                            88 => 'OCHENTA Y OCHO',
                            89 => 'OCHENTA Y NUEVE',
                            90 => 'NOVENTA',
                            91 => 'NOVENTA Y UNO',
                            92 => 'NOVENTA Y DOS',
                            93 => 'NOVENTA Y TRES',
                            94 => 'NOVENTA Y CUATRO',
                            95 => 'NOVENTA Y CINCO',
                            96 => 'NOVENTA Y SEIS',
                            97 => 'NOVENTA Y SIETE',
                            98 => 'NOVENTA Y OCHO',
                            99 => 'NOVENTA Y NUEVE',
                        ];

                        $parteEntera = floor($numero);
                        $parteDecimal = round(($numero - $parteEntera) * 100);

                        $textoEntera = $numeros[$parteEntera] ?? 'ERROR';

                        // Convertir la parte decimal como un número completo (0-99)
                        $textoDecimal = $numeros[$parteDecimal] ?? 'ERROR';

                        // Formato: (número) LETRAS
                    return number_format($numero, 2) .'&nbsp;&nbsp;' . $textoEntera . ' PUNTO ' . $textoDecimal;
                    }

                    $numeroEnLetras = numeroALetrasConPunto($notaFinalTotal);
                @endphp

                <table class="final-table" style="margin-top: 40px; width: 75%; margin: 0 auto;">
                    <tr>
                        <th style="width: 70%;">Componentes</th>
                        <th style="width: 30%;">Calificación sobre 20 pts.</th>
                    </tr>
                    <tr>
                        <td class="text-left">1. COMPONENTE TEÓRICO (SOLUCIÓN DEL CUESTIONARIO)</td>
                        <td>{{ !empty($itemsNotaDirecta) ? number_format($itemsNotaDirecta[0]['puntaje_ponderado_item'] ?? 0, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-left">
                            2. CALIFICACIÓN COMPONENTE PRÁCTICO
                        </td>
                        <td>{{ !empty($itemsRubrica) ? number_format($itemsRubrica[0]['puntaje_ponderado_item'] ?? 0, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td class="text-center"><strong>CALIFICACIÓN FINAL DEL EXÁMEN DE CARÁCTER COMPLEXIVO (1+2)
                                TOTAL</strong>
                        </td>
                        <td><strong>{{ number_format($notaFinalTotal, 2) }}</strong></td>
                    </tr>
                </table>

                {{-- Nota final en letras --}}
                <div class="approval-section" style="margin-top: 30px;">
                    <strong>CALIFICACIÓN FINAL (NÚMEROS Y LETRAS):</strong>
                    <span class="underline-field" style="min-width: 300px;">
                        {!! $numeroEnLetras !!}
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
                        {{-- Fecha: {{ \Carbon\Carbon::parse($tribunal->fecha ?? now())->format('d/m/Y') }} --}}
                        Fecha: {{ \Carbon\Carbon::parse('2025-08-19')->format('m/d/Y') }}
                    </div>
                </div>

                {{-- Firmas de los miembros del tribunal --}}
                @php
                    $presidente = $tribunal->miembrosTribunales->where('status', 'PRESIDENTE')->first();
                    $integrante1 = $tribunal->miembrosTribunales->where('status', 'INTEGRANTE1')->first();
                    $integrante2 = $tribunal->miembrosTribunales->where('status', 'INTEGRANTE2')->first();

                    $nombreIntegrante1 = ucfirst(
                        strtolower(
                            mb_substr($integrante1->status, 0, mb_strlen($integrante1->status) - 1) .
                                ' ' .
                                mb_substr($integrante1->status, -1, 1),
                        ),
                    );
                    $nombreIntegrante2 = ucfirst(
                        strtolower(
                            mb_substr($integrante2->status, 0, mb_strlen($integrante2->status) - 1) .
                                ' ' .
                                mb_substr($integrante2->status, -1, 1),
                        ),
                    );
                @endphp

                <div class="signature-row">
                    <div class="signature-cell">
                        <div class="signature-line">
                            {{ $presidente->user->name ?? 'Presidente del Tribunal' }}<br>
                            CI.
                            {{ str_pad($presidente->user->cedula ?? '........................', 10, '0', STR_PAD_LEFT) }}<br>
                            Presidente del Tribunal
                        </div>
                    </div>

                    <div class="signature-cell">
                        <div class="signature-line">
                            {{ $integrante1->user->name ?? 'Integrante 2' }}<br>
                            CI.
                            {{ str_pad($integrante1->user->cedula ?? '........................', 10, '0', STR_PAD_LEFT) }}<br>
                            Miembro 2
                        </div>
                    </div>

                    <div class="signature-cell">
                        <div class="signature-line">
                            {{ $integrante2->user->name ?? 'Integrante 3' }}<br>
                            CI. {{ str_pad($integrante2->user->cedula ?? '........................', 10, '0', STR_PAD_LEFT) }}<br>
                            Miembro 3
                        </div>
                    </div>
                </div>

                {{-- Firma del Director de Carrera --}}
                <div class="director-signature">
                    <div class="director-line">
                        {{ $tribunal->carrerasPeriodo->director->name ?? 'Director de Carrera' }}<br>
                        CI. {{ str_pad($tribunal->carrerasPeriodo->director->cedula ?? '1710802925', 10, '0', STR_PAD_LEFT) }}<br>
                        <strong>Director de Carrera</strong>
                    </div>
                </div>
            @else
                <div style="text-align: center; margin: 50px 0;">
                    <p>No hay datos de evaluación disponibles para generar el acta.</p>
                </div>
            @endif
            <div class="footer-codes">
                Código de documento: UDED-FOR-V4-2024-013<br>
                Código de proceso: GDOC-ATAD-5-3
            </div>

            <div class="page-number">2</div>
            <div class="footer-ref">
                Rev: UPDI: 2024-Sep-19
            </div>
        </div>
    </div>
</body>

</html>
