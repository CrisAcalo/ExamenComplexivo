<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\CarrerasPeriodo;
use App\Models\Departamento;
use App\Models\Estudiante;
use App\Models\MiembrosTribunal;
use App\Models\Periodo;
use App\Models\Tribunale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User; // Asegúrate que el namespace de tu modelo User sea este
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class InitialSeeder extends Seeder // O RolesAndPermissionsSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        // --- 4. CREAR USUARIOS DE EJEMPLO Y ASIGNAR ROLES ---

        // Usuario Super Admin (ya lo creaste al inicio, solo nos aseguramos de que tenga el rol)
        $superAdminUser = User::firstWhere('email', 'admin@admin.com');
        if ($superAdminUser) {
            $superAdminUser->assignRole('Super Admin');
        } else {
            $superAdminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password')
            ]);
            $superAdminUser->assignRole('Super Admin');
        }

        // Usuario Administrador (Operativo) de Ejemplo
        $adminUserEmail = 'operativo@admin.com';
        if (User::where('email', $adminUserEmail)->doesntExist()) {
            $adminUser = User::factory()->create([
                'name' => 'Admin Operativo',
                'email' => $adminUserEmail,
                'password' => Hash::make('password')
            ]);
            $adminUser->assignRole('Administrador');
        }


        // $directorEmail = 'director@example.com';
        // if (User::where('email', $directorEmail)->doesntExist()) {
        //     $directorUser = User::factory()->create([
        //         'name' => 'Director Ejemplo',
        //         'email' => $directorEmail,
        //         'password' => Hash::make('password')
        //     ]);
        //     $directorUser->assignRole($directorRole);
        // }

        // $apoyoEmail = 'apoyo@example.com';
        // if (User::where('email', $apoyoEmail)->doesntExist()) {
        //     $apoyoUser = User::factory()->create([
        //         'name' => 'Docente Apoyo Ejemplo',
        //         'email' => $apoyoEmail,
        //         'password' => Hash::make('password')
        //     ]);
        //     $apoyoUser->assignRole($apoyoRole);
        // }

        // $docente1Email = 'docente1@example.com';
        // if (User::where('email', $docente1Email)->doesntExist()) {
        //     $docenteUser1 = User::factory()->create([
        //         'name' => 'Docente Uno',
        //         'email' => $docente1Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser1->assignRole($docenteRole);
        // }

        // $docente2Email = 'docente2@example.com';
        // if (User::where('email', $docente2Email)->doesntExist()) {
        //     $docenteUser2 = User::factory()->create([
        //         'name' => 'Docente Dos',
        //         'email' => $docente2Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser2->assignRole($docenteRole);
        // }

        // $docente3Email = 'docente3@example.com';
        // if (User::where('email', $docente3Email)->doesntExist()) {
        //     $docenteUser3 = User::factory()->create([
        //         'name' => 'Docente Tres',
        //         'email' => $docente3Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser3->assignRole($docenteRole);
        // }

        // $docente4Email = 'docente4@example.com';
        // if (User::where('email', $docente4Email)->doesntExist()) {
        //     $docenteUser4 = User::factory()->create([
        //         'name' => 'Docente 4',
        //         'email' => $docente4Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser4->assignRole($docenteRole);
        // }

        // $docente5Email = 'docente5@example.com';
        // if (User::where('email', $docente5Email)->doesntExist()) {
        //     $docenteUser5 = User::factory()->create([
        //         'name' => 'Docente 5',
        //         'email' => $docente5Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser5->assignRole($docenteRole);
        // }

        // $docente6Email = 'docente6@example.com';
        // if (User::where('email', $docente6Email)->doesntExist()) {
        //     $docenteUser6 = User::factory()->create([
        //         'name' => 'Docente 6',
        //         'email' => $docente6Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser6->assignRole($docenteRole);
        // }

        // $docente7Email = 'docente7@example.com';
        // if (User::where('email', $docente7Email)->doesntExist()) {
        //     $docenteUser7 = User::factory()->create([
        //         'name' => 'Docente 7',
        //         'email' => $docente7Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser7->assignRole($docenteRole);
        // }

        // $docente8Email = 'docente8@example.com';
        // if (User::where('email', $docente8Email)->doesntExist()) {
        //     $docenteUser8 = User::factory()->create([
        //         'name' => 'Docente 8',
        //         'email' => $docente8Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser8->assignRole($docenteRole);
        // }

        // $docente9Email = 'docente9@example.com';
        // if (User::where('email', $docente9Email)->doesntExist()) {
        //     $docenteUser9 = User::factory()->create([
        //         'name' => 'Docente 9',
        //         'email' => $docente9Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser9->assignRole($docenteRole);
        // }

        // $docente10Email = 'docente10@example.com';
        // if (User::where('email', $docente10Email)->doesntExist()) {
        //     $docenteUser10 = User::factory()->create([
        //         'name' => 'Docente 10',
        //         'email' => $docente10Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser10->assignRole($docenteRole);
        // }

        // $docente11Email = 'docente11@example.com';
        // if (User::where('email', $docente11Email)->doesntExist()) {
        //     $docenteUser11 = User::factory()->create([
        //         'name' => 'Docente 11',
        //         'email' => $docente11Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser11->assignRole($docenteRole);
        // }

        // $docente12Email = 'docente12@example.com';
        // if (User::where('email', $docente12Email)->doesntExist()) {
        //     $docenteUser12 = User::factory()->create([
        //         'name' => 'Docente 12',
        //         'email' => $docente12Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser12->assignRole($docenteRole);
        // }

        // $docente13Email = 'docente13@example.com';
        // if (User::where('email', $docente13Email)->doesntExist()) {
        //     $docenteUser13 = User::factory()->create([
        //         'name' => 'Docente 13',
        //         'email' => $docente13Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser13->assignRole($docenteRole);
        // }

        // $docente14Email = 'docente14@example.com';
        // if (User::where('email', $docente14Email)->doesntExist()) {
        //     $docenteUser14 = User::factory()->create([
        //         'name' => 'Docente 14',
        //         'email' => $docente14Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser14->assignRole($docenteRole);
        // }

        // $docente15Email = 'docente15@example.com';
        // if (User::where('email', $docente15Email)->doesntExist()) {
        //     $docenteUser15 = User::factory()->create([
        //         'name' => 'Docente 15',
        //         'email' => $docente15Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser15->assignRole($docenteRole);
        // }

        // $docente16Email = 'docente16@example.com';
        // if (User::where('email', $docente16Email)->doesntExist()) {
        //     $docenteUser16 = User::factory()->create([
        //         'name' => 'Docente 16',
        //         'email' => $docente16Email,
        //         'password' => Hash::make('password')
        //     ]);
        //     $docenteUser16->assignRole($docenteRole);
        // }

        $departamentos = [
            ['codigo_departamento' => '20250201', 'nombre' => 'Ciencias de la Computación'],
            ['codigo_departamento' => '20250202', 'nombre' => 'Ciencias de la Tierra y de la Construcción'],
            ['codigo_departamento' => '20250203', 'nombre' => 'Ciencias de la Vida y de la Agricultura'],
            ['codigo_departamento' => '20250204', 'nombre' => 'Ciencias Económicas Administrativas y de Comercio'],
            ['codigo_departamento' => '20250205', 'nombre' => 'Ciencias Exactas'],
            ['codigo_departamento' => '20250206', 'nombre' => 'Ciencias Humanas y Sociales'],
            ['codigo_departamento' => '20250207', 'nombre' => 'Eléctrica, Electrónica y Telecomunicaciones'],
            ['codigo_departamento' => '20250208', 'nombre' => 'Ciencias de Energía y Mecánica'],
            ['codigo_departamento' => '20250209', 'nombre' => 'Seguridad y Defensa'],
            ['codigo_departamento' => '20250210', 'nombre' => 'Departamento de Ciencias Médicas']
        ];
        foreach ($departamentos as $data) {
            Departamento::firstOrCreate($data);
        }

        // --- 6. CREAR CARRERAS INICIALES ---

        // Primero, obtenemos los departamentos de la BD y los indexamos por nombre para un acceso fácil.
        $departamentosDB = Departamento::all()->keyBy('nombre');

        // Estructura de carreras por departamento para facilitar la asignación.
        $carrerasPorDepartamento = [
            'Ciencias de Energía y Mecánica' => [
                'Petroquímica',
                'Mecatrónica',
                'Ingeniería Automotriz',
                'Mecánica'
            ],
            'Ciencias de la Computación' => [
                'Ingeniería de Software',
                'Tecnologías de la Información'
            ],
            'Eléctrica, Electrónica y Telecomunicaciones' => [
                'Telecomunicaciones',
                'Electrónica y Automatización',
                'Electromecánica'
            ],
            'Ciencias de la Vida y de la Agricultura' => [
                'Agropecuaria',
                'Biotecnología'
            ],
            'Ciencias Económicas Administrativas y de Comercio' => [
                'Administración de Empresas',
                'Comercio Exterior',
                'Contabilidad y Auditoría',
                'Mercadotecnia',
                'Turismo'
            ],
            'Ciencias de la Tierra y de la Construcción' => [
                'Ingeniería Civil',
                'Ingeniería Geoespacial',
                'Ingeniería Ambiental'
            ],
            'Departamento de Ciencias Médicas' => [
                'Medicina'
            ],
            'Ciencias Humanas y Sociales' => [
                'Pedagogía de la Actividad Física y Deporte',
                'Educación Inicial',
                'Educación Básica',
                'Pedagogía de los Idiomas Nacionales y Extranjeros',
                'Economía'
            ],
            'Seguridad y Defensa' => [
                'Relaciones Internacionales',
                'Ciencias Militares',
                'Ciencias Navales',
                'Ciencias Náuticas',
                'Ciencias Militares Aeronáuticas',
                'Tecnología Sup. en Ciencias Militares',
                'Tecnología Sup. en Operaciones Militares de Selva',
                'Tecnología Sup. en Ciencias Militares Aeronáuticas'
            ],
        ];

        // Lista de carreras que son explícitamente virtuales
        $carrerasVirtuales = [
            'Educación Básica',
            'Educación Inicial',
            'Economía',
            'Turismo',
            'Tecnologías de la Información',
            'Pedagogía de los Idiomas Nacionales y Extranjeros'
        ];

        // Contador para generar códigos de carrera únicos
        $codigoCounter = 20251001;

        foreach ($carrerasPorDepartamento as $nombreDepto => $carreras) {
            // Verificamos si el departamento existe en nuestra colección obtenida de la BD
            if (!isset($departamentosDB[$nombreDepto])) {
                // Opcional: registrar un warning si un departamento no se encuentra
                // $this->command->warn("Departamento no encontrado: {$nombreDepto}. Saltando sus carreras.");
                continue;
            }

            $departamento = $departamentosDB[$nombreDepto];

            foreach ($carreras as $nombreCarrera) {
                // Determinamos la modalidad
                $modalidad = in_array($nombreCarrera, $carrerasVirtuales) ? 'Virtual' : 'Presencial';

                // Usamos firstOrCreate para evitar duplicados si el seeder se ejecuta múltiples veces.
                // La clave única para la búsqueda es 'codigo_carrera'.
                Carrera::firstOrCreate(
                    ['codigo_carrera' => (string)$codigoCounter],
                    [
                        'nombre' => $nombreCarrera,
                        'departamento_id' => $departamento->id,
                        'modalidad' => $modalidad,
                        'sede' => 'Sangolquí' // Sede constante como se indica
                    ]
                );

                $codigoCounter++; // Incrementamos para la siguiente carrera
            }
        }


        //------------------------------------------------------------------------------------------------------------------
        // --- 7. CREAR ESTUDIANTES DE PRUEBA ---
        // Se comprueba si la tabla está vacía para no añadir duplicados en futuras ejecuciones.
        // if (Estudiante::count() === 0) {
        //     $this->command->info('Creando 50 estudiantes de prueba...');
        //     Estudiante::factory()->count(50)->create();
        //     $this->command->info('Estudiantes de prueba creados.');
        // } else {
        //     $this->command->info('La tabla de estudiantes ya tiene datos. No se crearon nuevos.');
        // }

        // // --- 8. CREAR PERIODOS ACADÉMICOS ---
        // if (Periodo::count() === 0) {
        //     $this->command->info('Creando periodos de prueba...');
        //     $periodo1 = Periodo::firstOrCreate(
        //         ['codigo_periodo' => '202450'],
        //         [
        //             'descripcion' => 'MAY-SEP 2024',
        //             'fecha_inicio' => Carbon::create(2024, 5, 1),
        //             'fecha_fin' => Carbon::create(2024, 9, 30),
        //         ]
        //     );

        //     $periodo2 = Periodo::firstOrCreate(
        //         ['codigo_periodo' => '202460'],
        //         [
        //             'descripcion' => 'OCT 2024-FEB 2025',
        //             'fecha_inicio' => Carbon::create(2024, 10, 1),
        //             'fecha_fin' => Carbon::create(2025, 2, 28),
        //         ]
        //     );
        //     $this->command->info('Periodos creados.');
        // }


        // // --- 9. ASIGNAR CARRERAS A PERIODOS (CREAR CARRERA_PERIODO) ---
        // if (CarrerasPeriodo::count() === 0) {
        //     $this->command->info('Asignando carreras a periodos...');
        //     // Obtenemos los usuarios con los roles necesarios
        //     $director = User::role('Director de Carrera')->first();
        //     $apoyo = User::role('Docente de Apoyo')->first();

        //     // Obtenemos algunos periodos y carreras ya creados
        //     $periodos = Periodo::all();
        //     $carreras = Carrera::take(5)->get(); // Tomamos 5 carreras para el ejemplo

        //     if ($director && $apoyo && $periodos->isNotEmpty() && $carreras->isNotEmpty()) {
        //         // Asignamos las 5 carreras al primer periodo
        //         foreach ($carreras as $carrera) {
        //             CarrerasPeriodo::firstOrCreate(
        //                 [
        //                     'carrera_id' => $carrera->id,
        //                     'periodo_id' => $periodos->first()->id
        //                 ],
        //                 [
        //                     'director_id' => $director->id,
        //                     'docente_apoyo_id' => $apoyo->id
        //                 ]
        //             );
        //         }
        //         $this->command->info('Asignaciones Carrera-Periodo creadas.');
        //     } else {
        //         $this->command->warn('No se pudieron crear asignaciones Carrera-Periodo. Faltan Directores, Docentes de Apoyo, Periodos o Carreras.');
        //     }
        // }


        // // --- 10. CREAR TRIBUNALES Y SUS MIEMBROS ---
        // if (Tribunale::count() === 0) {
        //     $this->command->info('Creando tribunales de prueba y asignando miembros...');
        //     // Obtenemos los datos necesarios
        //     $carreraPeriodo = CarrerasPeriodo::first(); // Tomamos la primera asignación como ejemplo
        //     $estudiantes = Estudiante::take(3)->get(); // Crearemos 3 tribunales, uno por estudiante
        //     $docentes = User::role('Docente')->get();   // Obtenemos todos los usuarios con rol Docente

        //     // Verificamos que tenemos suficientes datos para proceder
        //     if ($carreraPeriodo && $estudiantes->count() > 0 && $docentes->count() >= 3) {
        //         // Tomamos 3 docentes para que sean los miembros fijos de estos tribunales de ejemplo
        //         $presidente = $docentes[0];
        //         $integrante1 = $docentes[1];
        //         $integrante2 = $docentes[2];

        //         // Usaremos un contador manual en lugar de $loop
        //         $contador = 0;

        //         foreach ($estudiantes as $estudiante) {
        //             // Creamos el tribunal
        //             $tribunal = Tribunale::create([
        //                 'carrera_periodo_id' => $carreraPeriodo->id,
        //                 'estudiante_id' => $estudiante->id,
        //                 // SOLUCIÓN: Usamos el contador manual para incrementar los días
        //                 'fecha' => Carbon::now()->addWeeks(2)->addDays($contador),
        //                 'hora_inicio' => '09:00:00',
        //                 'hora_fin' => '10:00:00',
        //             ]);

        //             // Asignamos los miembros a este tribunal recién creado
        //             MiembrosTribunal::create([
        //                 'tribunal_id' => $tribunal->id,
        //                 'user_id' => $presidente->id,
        //                 'status' => 'PRESIDENTE',
        //             ]);
        //             MiembrosTribunal::create([
        //                 'tribunal_id' => $tribunal->id,
        //                 'user_id' => $integrante1->id,
        //                 'status' => 'INTEGRANTE1',
        //             ]);
        //             MiembrosTribunal::create([
        //                 'tribunal_id' => $tribunal->id,
        //                 'user_id' => $integrante2->id,
        //                 'status' => 'INTEGRANTE2',
        //             ]);

        //             $contador++; // Incrementamos el contador en cada iteración
        //         }
        //         $this->command->info('3 tribunales de prueba creados con sus miembros.');
        //     } else {
        //         $this->command->warn('No se pudieron crear tribunales. Se necesitan al menos 1 CarreraPeriodo, 1 Estudiante y 3 Docentes en la BD.');
        //     }
        // }
    }
}
