<?php

namespace Database\Seeders;

use App\Models\Rubrica;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usar una transacción para asegurar la integridad de los datos
        DB::transaction(function () {
            // --- 1. Crear la Rúbrica Principal ---
            $rubrica = Rubrica::create([
                'nombre' => 'Rúbrica General de Examen Complexivo',
            ]);

            // --- 2. Definir los Niveles de Calificación (Columnas) ---
            // El orden aquí es importante, debe coincidir con el orden de las descripciones abajo.
            $niveles = [
                ['nombre' => 'Muy Bueno', 'valor' => 4],
                ['nombre' => 'Bueno', 'valor' => 3],
                ['nombre' => 'Regular', 'valor' => 2],
                ['nombre' => 'Insuficiente', 'valor' => 1],
                ['nombre' => 'No presenta', 'valor' => 0],
            ];

            // --- 3. Definir los Componentes y sus Criterios ---
            $componentesData = [
                [
                    'nombre' => 'Parte escrita',
                    'ponderacion' => 50, // Asumiendo 50% para la parte escrita
                    'criterios' => [
                        [
                            'nombre' => 'Pericia en la especialidad',
                            'descripciones' => [
                                'Desarrolla y relaciona correctamente el marco conceptual de los campos de estudio que involucra al problema profesional y/o estudio de caso.',
                                'Desarrolla correctamente el marco conceptual de los campos de estudio que involucra al problema profesional y/o estudio de caso.',
                                'Desarrolla el marco conceptual de los campos de estudio que involucra al problema profesional y/o estudio de caso, con errores leves',
                                'Desarrolla el marco conceptual de los campos de estudio que involucra al problema profesional y/o estudio de caso, con errores graves.',
                                'No aplica el marco conceptual de los campos de estudio que involucra al problema profesional y/o estudio de caso.',
                            ],
                        ],
                        [
                            'nombre' => 'Análisis en el ámbito del problema profesionalizante y/o estudio de caso',
                            'descripciones' => [
                                'Realiza un análisis profundo para abordar una situación relacionada con el problema profesionalizante y/o estudio de caso',
                                'Realiza un análisis suficiente (que incluye lo mínimo requerido) para abordar una situación relacionada con el problema profesionalizante y/o estudio de caso',
                                'Realiza un análisis con errores leves, para abordar una situación relacionada con el problema profesionalizante y/o estudio de caso',
                                'Realiza un análisis con errores graves, para abordar una situación relacionada con el problema profesionalizante y/o estudio de caso',
                                'No realiza ningún análisis para abordar una situación relacionada con el problema profesionalizante y/o estudio de caso',
                            ],
                        ],
                        [
                            'nombre' => 'Planteamiento de acciones o soluciones a problemáticas del problema profesionalizante y/o estudio de caso',
                            'descripciones' => [
                                'Propone una o más acciones o soluciones, fundamentadas en su campo de estudio, que indican una profunda comprensión de la situación o el problema que atraviesa el caso de estudio presentado en el argumento del caso',
                                'Propone una o más acciones o soluciones, fundamentadas en su campo de estudio, que indican comprensión de la situación o el problema que atraviesa el caso de estudio presentado en el argumento del caso',
                                'Propone una o más acciones o soluciones, fundamentadas en su campo de estudio, que indican poca comprensión de la situación o el problema atraviesa el caso de estudio presentado en el argumento del caso',
                                'Propone una o más acciones o soluciones tomadas al azar y que no tienen relación con la situación o el problema que atraviesa el caso de estudio presentado en el argumento del caso',
                                'No propone acciones o soluciones a la situación o al problema que atraviesa el caso de estudio presentado en el argumento del caso.',
                            ],
                        ],
                        [
                            'nombre' => 'Selección de alternativa en el contexto del problema profesional y/o estudio de caso',
                            'descripciones' => [
                                'Selecciona una alternativa para el problema profesional y/o estudio de caso, respaldando su elección de manera precisa y coherente.',
                                'Selecciona una alternativa para el problema profesional y/o estudio de caso, respaldando su elección de manera correcta y suficiente (incluye lo mínimo requerido).',
                                'Selecciona una alternativa para el problema profesional y/o estudio de caso, presentando un argumento. Su fundamentación es débil o contiene errores leves.',
                                'Selecciona una alternativa para el problema profesional y/o estudio de caso, presentando un argumento. Su fundamentación es mínima o contiene errores graves.',
                                'No selecciona una alternativa para el problema profesional y/o estudio de caso',
                            ],
                        ],
                        [
                            'nombre' => 'Capacidad de síntesis',
                            'descripciones' => [
                                'Integra todas las ideas relevantes elementos y/o imágenes para formar una unidad cohesiva',
                                'Integra adecuadamente muchas de las ideas, elementos y/o imágenes para formar una unidad cohesiva',
                                'Integra algunas de las ideas, elementos y/o imágenes para formar una unidad cohesiva',
                                'Es confusa o integra de manera escasa algunas de las ideas, elementos y/o imágenes para formar una unidad cohesiva',
                                'No Integra las ideas, elementos y/o imágenes para formar una unidad cohesiva',
                            ],
                        ],
                    ],
                ],
                [
                    'nombre' => 'Parte Oral',
                    'ponderacion' => 50, // Asumiendo 50% para la parte oral
                    'criterios' => [
                        [
                            'nombre' => 'Claridad en las ideas',
                            'descripciones' => [
                                'Todos los argumentos fueron precisos, relevantes y consistentes.',
                                'La mayoría de los argumentos fueron precisos relevantes y consistentes.',
                                'Algunos argumentos fueron precisos, relevantes y consistentes.',
                                'Pocos argumentos fueron precisos, son irrelevantes y carecen de consistencia.',
                                'No incluye los argumentos',
                            ],
                        ],
                        [
                            'nombre' => 'Profundidad de Conocimiento',
                            'descripciones' => [
                                'Muestra un conocimiento excepcionalmente profundo y exhaustivo del tema, abordando aspectos complejos con claridad y solidez.',
                                'Muestra un conocimiento sólido, abordando adecuadamente los aspectos principales del tema, pero podría profundizar en algunos detalles.',
                                'Muestra un conocimiento aceptable, aunque limitado en profundidad, con áreas de mejora evidentes.',
                                'Muestra un conocimiento insuficiente del tema, con lagunas importantes y falta de comprensión.',
                                'No muestra conocimiento del tema.',
                            ],
                        ],
                        [
                            'nombre' => 'Facilidad de expresión',
                            'descripciones' => [
                                'Mantiene todo el tiempo contacto visual con la audiencia y proyecta seguridad, utiliza lenguaje y tono de voz apropiado.',
                                'Mantiene casi todo el tiempo contacto visual con la audiencia, proyecta seguridad, y usa un lenguaje y tono de voz apropiado.',
                                'Mantiene esporádico contacto visual con la audiencia, proyecta poca seguridad, y usa un lenguaje aceptable.',
                                'Mantiene escaso contacto visual con la audiencia, proyecta poca seguridad, y usa un lenguaje inadecuado.',
                                'No incluye los argumentos',
                            ],
                        ],
                        [
                            'nombre' => 'Claridad en la Presentación',
                            'descripciones' => [
                                'La presentación es excepcionalmente clara, con una estructura lógica y transiciones fluidas entre las secciones.',
                                'La presentación es clara y bien organizada, aunque podría haber mejoras en la estructura en algunos puntos.',
                                'La presentación es aceptable, pero la estructura y la claridad podrían mejorarse para facilitar la comprensión.',
                                'La presentación es confusa y desorganizada, dificultando la comprensión de la audiencia.',
                                'No presenta una presentación',
                            ],
                        ],
                        [
                            'nombre' => 'Respuestas y preguntas',
                            'descripciones' => [
                                'Contesta con precisión todas las preguntas planteadas sobre la resolución del problema profesional y/o estudio de caso',
                                'Contesta con precisión la mayoría de las preguntas planteadas sobre la resolución del problema profesional y/o estudio de caso',
                                'Contesta con precisión unas pocas preguntas sobre la resolución del problema profesional y/o estudio de caso',
                                'No contesta con precisión ninguna pregunta sobre la resolución del problema profesional y/o estudio de caso',
                                'No responde a las preguntas sobre el problema profesional y/o estudio de caso',
                            ],
                        ],
                    ],
                ],
            ];

            // --- 4. Poblar la Base de Datos ---
            foreach ($componentesData as $compData) {
                // Crear el Componente de la Rúbrica
                $componente = $rubrica->componentesRubrica()->create([
                    'nombre' => $compData['nombre'],
                    'ponderacion' => $compData['ponderacion'],
                ]);

                foreach ($compData['criterios'] as $critData) {
                    // Crear el Criterio para el componente actual
                    $criterio = $componente->criteriosComponente()->create([
                        'nombre' => $critData['nombre'],
                    ]);

                    // Crear las 5 opciones de calificación para este criterio
                    foreach ($niveles as $index => $nivelData) {
                        $criterio->calificacionesCriterio()->create([
                            'nombre' => $nivelData['nombre'],
                            'valor' => $nivelData['valor'],
                            'descripcion' => $critData['descripciones'][$index], // Mapear por índice
                        ]);
                    }
                }
            }
        });
    }
}
