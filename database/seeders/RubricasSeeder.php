<?php

namespace Database\Seeders;

use App\Models\CarrerasPeriodo;
use App\Models\Estudiante;
use App\Models\MiembrosTribunal;
use App\Models\Rubrica;
use App\Models\Tribunale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RubricasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Verificación inicial para no duplicar datos
        // if (Tribunale::count() > 0) {
        //     $this->command->info('La tabla de tribunales ya tiene datos. No se ejecutó el seeder.');
        //     return;
        // }

        // 2. Obtener los datos necesarios de la base de datos
        $this->command->info('Buscando datos necesarios para crear tribunales...');

        // Tomamos la primera asignación de carrera-periodo como ejemplo
        $carreraPeriodo = CarrerasPeriodo::first();

        // Buscamos estudiantes que AÚN NO tengan un tribunal asignado
        $estudiantesSinTribunal = Estudiante::whereDoesntHave('tribunales')->take(10)->get();

        // Obtenemos todos los usuarios con el rol 'Docente'
        $docentes = User::role('Docente')->get();

        // 3. Validación de que tenemos suficientes datos para proceder
        if (!$carreraPeriodo || $estudiantesSinTribunal->isEmpty() || $docentes->count() < 3) {
            $this->command->warn('No se pueden crear tribunales. Verifique lo siguiente:');
            $this->command->warn('- Debe existir al menos una entrada en "carreras_periodos".');
            $this->command->warn('- Deben existir estudiantes sin un tribunal ya asignado.');
            $this->command->warn('- Deben existir al menos 3 usuarios con el rol "Docente".');
            return;
        }

        $this->command->info("Creando tribunales para {$estudiantesSinTribunal->count()} estudiantes...");

        $contador = 0;
        // 4. Bucle para crear un tribunal por cada estudiante encontrado
        foreach ($estudiantesSinTribunal as $estudiante) {

            // Seleccionamos 3 docentes al azar de la colección para formar el tribunal
            // Esto asegura que no todos los tribunales tengan los mismos miembros
            $miembrosSeleccionados = $docentes->random(3);

            // Creamos el registro del tribunal
            $tribunal = Tribunale::create([
                'carrera_periodo_id' => $carreraPeriodo->id,
                'estudiante_id'      => $estudiante->id,
                'fecha'              => Carbon::today()->addDays(14 + $contador), // Programar para 2 semanas en adelante
                'hora_inicio'        => '10:00:00',
                'hora_fin'           => '11:00:00',
            ]);

            // Asignamos los miembros a este tribunal recién creado
            MiembrosTribunal::create([
                'tribunal_id' => $tribunal->id,
                'user_id'     => $miembrosSeleccionados[0]->id,
                'status'      => 'PRESIDENTE',
            ]);

            MiembrosTribunal::create([
                'tribunal_id' => $tribunal->id,
                'user_id'     => $miembrosSeleccionados[1]->id,
                'status'      => 'INTEGRANTE1',
            ]);

            MiembrosTribunal::create([
                'tribunal_id' => $tribunal->id,
                'user_id'     => $miembrosSeleccionados[2]->id,
                'status'      => 'INTEGRANTE2',
            ]);

            $contador++;
        }

        $this->command->info("¡Seeder de tribunales completado! Se crearon {$contador} nuevos tribunales.");


        // 1. Verificación para no duplicar datos
        if (Rubrica::count() > 0) {
            $this->command->info('La tabla de rúbricas ya contiene datos. No se ejecutó el seeder.');
            return;
        }

        $this->command->info('Creando plantilla de Rúbrica de ejemplo...');

        // 2. Definir la estructura completa de la rúbrica en un array
        $rubricaData = [
            'nombre' => 'Rúbrica General de Defensa de Trabajo de Titulación',
            'componentes' => [
                [
                    'nombre' => 'Presentación Oral',
                    'ponderacion' => 50.00,
                    'criterios' => [
                        ['nombre' => 'Claridad y Coherencia en la Exposición'],
                        ['nombre' => 'Uso Efectivo de Ayudas Visuales'],
                        ['nombre' => 'Manejo Adecuado del Tiempo'],
                    ]
                ],
                [
                    'nombre' => 'Contenido del Trabajo Escrito',
                    'ponderacion' => 50.00,
                    'criterios' => [
                        ['nombre' => 'Estructura, Organización y Redacción'],
                        ['nombre' => 'Metodología Aplicada y Rigor Científico'],
                        ['nombre' => 'Resultados, Conclusiones y Aportes'],
                    ]
                ],
                // [
                //     'nombre' => 'Dominio del Tema y Respuestas',
                //     'ponderacion' => 30.00,
                //     'criterios' => [
                //         ['nombre' => 'Profundidad del Conocimiento Demostrado'],
                //         ['nombre' => 'Precisión y Fundamentación en las Respuestas'],
                //     ]
                // ]
            ]
        ];

        // Definimos una escala de calificación estándar que se usará para todos los criterios
        $escalaCalificaciones = [
            ['nombre' => 'Excelente', 'valor' => 10.0, 'descripcion' => 'Supera todas las expectativas y demuestra un dominio excepcional.'],
            ['nombre' => 'Bueno', 'valor' => 8.0, 'descripcion' => 'Cumple satisfactoriamente con los requisitos del criterio.'],
            ['nombre' => 'Regular', 'valor' => 6.0, 'descripcion' => 'Cumple con los requisitos mínimos pero presenta áreas de mejora.'],
            ['nombre' => 'Deficiente', 'valor' => 4.0, 'descripcion' => 'No cumple con los requisitos básicos del criterio.'],
        ];


        // 3. Crear los registros en la base de datos iterando sobre la estructura

        // Crear la Rúbrica principal
        $rubrica = Rubrica::create(['nombre' => $rubricaData['nombre']]);
        $this->command->line("  - Rúbrica creada: '{$rubrica->nombre}'");

        // Iterar sobre los Componentes
        foreach ($rubricaData['componentes'] as $componenteData) {
            $componente = $rubrica->componentes()->create([
                'nombre' => $componenteData['nombre'],
                'ponderacion' => $componenteData['ponderacion'],
            ]);
            $this->command->line("    - Componente creado: '{$componente->nombre}' (Ponderación: {$componente->ponderacion}%)");

            // Iterar sobre los Criterios de cada Componente
            foreach ($componenteData['criterios'] as $criterioData) {
                $criterio = $componente->criteriosComponente()->create([
                    'nombre' => $criterioData['nombre'],
                ]);
                $this->command->line("      - Criterio creado: '{$criterio->nombre}'");

                // Iterar sobre la Escala de Calificaciones y crearla para cada Criterio
                foreach ($escalaCalificaciones as $calificacionData) {
                    $criterio->calificacionesCriterio()->create([
                        'nombre' => $calificacionData['nombre'],
                        'valor' => $calificacionData['valor'],
                        'descripcion' => $calificacionData['descripcion'],
                    ]);
                }
                $this->command->line("        - Escala de calificación ('Excelente' a 'Deficiente') creada para el criterio.");
            }
        }

        $this->command->info('¡Seeder de Rúbricas completado exitosamente!');
    }
}
