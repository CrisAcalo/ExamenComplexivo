<?php

// Función para probar el nuevo formato de números a letras
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

// Probar con diferentes notas
$notasPrueba = [15.08, 18.75, 14.00, 12.50, 19.99, 16.25, 20.00, 13.33];

echo "Prueba del nuevo formato de notas en letras:\n";
echo "===========================================\n\n";

foreach ($notasPrueba as $nota) {
    echo "Nota: $nota\n";
    echo "En letras: " . numeroALetrasConPunto($nota) . "\n";
    echo "---\n";
}
