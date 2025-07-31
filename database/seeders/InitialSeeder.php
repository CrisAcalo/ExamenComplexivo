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
            'Educación Básica en línea',
            'Educación Inicial en línea',
            'Economía en línea',
            'Turismo en línea',
            'Tecnologías de la Información en línea',
            'Pedagogía de los Idiomas Nacionales y Extranjeros',
            'Software en línea'
        ];

        // Contador para generar códigos de carrera únicos
        $codigoCounter = 20251001;

        foreach ($carrerasPorDepartamento as $nombreDepto => $carreras) {

            $departamento = $departamentosDB[$nombreDepto];

            foreach ($carreras as $nombreCarrera) {
                // Determinamos la modalidad
                $modalidad = in_array($nombreCarrera, $carrerasVirtuales) ? 'En línea' : 'Presencial';

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

    }
}
