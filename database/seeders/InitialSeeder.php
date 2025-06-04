<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User; // Asegúrate que el namespace de tu modelo User sea este
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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 1. CREAR PERMISOS ---
        $permissionsList = [
            // Admin (Operativo) y Configuración General del Sistema
            'gestionar usuarios',                       // CRUD de usuarios
            'gestionar roles y permisos',             // Asignar roles a usuarios, crear/editar roles/permisos (si no es solo Super Admin)
            'gestionar periodos',                     // CRUD de periodos académicos
            'gestionar carreras',                     // CRUD de carreras
            'gestionar plantillas rubricas',          // CRUD de plantillas de rúbricas
            'asignar carrera a periodo',            // Crear la entidad carrera_periodo y asignar director/apoyo
            'editar asignacion carrera-periodo',    // Cambiar director/apoyo en carrera_periodo

            // Nivel Carrera-Periodo (Admin global, Directores/Apoyos contextual)
            'configurar plan evaluacion',             // Crear/editar el plan para un carrera-periodo
            'crear tribunales',                       // En un carrera-periodo
            'ver listado tribunales',                 // De un carrera-periodo
            'eliminar tribunales',                    // De un carrera-periodo (con validaciones)
            'ver todas las calificaciones de un tribunal', // Ver notas de todos los miembros
            'ver resumenes y reportes academicos',    // Generar/ver reportes

            // Nivel Tribunal (Docentes - contextual)
            'ver detalles mi tribunal',                // Ver la página de perfil de un tribunal donde es miembro
            'calificar mi tribunal',                   // Ingresar/editar/ver PROPIAS calificaciones
            'subir evidencia mi tribunal',             // Futuro

            // Permisos Específicos del Presidente del Tribunal (Docentes con rol Presidente - contextual)
            'editar datos basicos mi tribunal (presidente)', // Editar fecha, hora, miembros (antes de calificar)
            'exportar acta mi tribunal (presidente)',      // Generar/exportar el acta del tribunal que preside
        ];

        foreach ($permissionsList as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- 2. CREAR ROLES ---
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $directorRole = Role::firstOrCreate(['name' => 'Director de Carrera']);
        $apoyoRole = Role::firstOrCreate(['name' => 'Docente de Apoyo']);
        $docenteRole = Role::firstOrCreate(['name' => 'Docente']);

        // --- 3. ASIGNAR PERMISOS A ROLES ---

        // Super Admin: Tiene todos los permisos implícitamente por el Gate::before.
        // No es estrictamente necesario asignar permisos aquí si Gate::before está activo.
        // $superAdminRole->givePermissionTo(Permission::all()); // Opcional

        // Administrador (Operativo)
        $adminRole->givePermissionTo([
            'gestionar usuarios', // Decide si el Admin operativo maneja esto o solo el Super Admin
            'gestionar roles y permisos', // Igual que arriba
            'gestionar periodos',
            'gestionar carreras',
            'gestionar plantillas rubricas',
            'asignar carrera a periodo',
            'editar asignacion carrera-periodo',
            'configurar plan evaluacion',        // Para cualquier carrera-periodo
            'crear tribunales',                  // Para cualquier carrera-periodo
            'ver listado tribunales',            // Para cualquier carrera-periodo
            'eliminar tribunales',               // Para cualquier carrera-periodo
            'ver todas las calificaciones de un tribunal', // Para cualquier tribunal
            'ver resumenes y reportes academicos',
            'exportar acta mi tribunal (presidente)', // Un admin podría tener un permiso más general como 'exportar cualquier acta'
        ]);

        // Director de Carrera
        // Los permisos se otorgan de forma general. Los Gates refinarán el acceso contextual.
        $directorRole->givePermissionTo([
            'configurar plan evaluacion',
            'crear tribunales',
            'ver listado tribunales',
            'eliminar tribunales',
            'ver todas las calificaciones de un tribunal',
            'ver resumenes y reportes academicos',
            // Si un Director también puede ser miembro/presidente de tribunal:
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);

        // Docente de Apoyo
        // Similar al Director, los Gates refinarán el acceso.
        $apoyoRole->givePermissionTo([
            'configurar plan evaluacion',
            'crear tribunales',
            'ver listado tribunales',
            'eliminar tribunales',
            'ver todas las calificaciones de un tribunal',
            'ver resumenes y reportes academicos',
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);

        // Docente
        // Los Gates refinarán para que solo aplique a SUS tribunales y si es presidente.
        $docenteRole->givePermissionTo([
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);


        // --- 4. CREAR USUARIOS DE EJEMPLO Y ASIGNAR ROLES ---

        // Usuario Super Admin (ya lo creaste al inicio, solo nos aseguramos de que tenga el rol)
        $superAdminUser = User::firstWhere('email', 'admin@admin.com');
        if ($superAdminUser) {
            $superAdminUser->assignRole($superAdminRole);
        } else {
            $superAdminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678') // Cambia la contraseña en un entorno real
            ]);
            $superAdminUser->assignRole($superAdminRole);
        }

        // Usuario Administrador (Operativo) de Ejemplo
        $adminUserEmail = 'operativo@admin.com';
        if (User::where('email', $adminUserEmail)->doesntExist()) {
            $adminUser = User::factory()->create([
                'name' => 'Admin Operativo',
                'email' => $adminUserEmail,
                'password' => Hash::make('password') // Cambia la contraseña
            ]);
            $adminUser->assignRole($adminRole);
        }


        // Usuarios de ejemplo (Director, Apoyo, Docentes)
        // El código que ya tenías para crear estos usuarios de ejemplo es bueno.
        // Solo asegúrate de que los emails no se repitan si ejecutas el seeder múltiples veces.
        $directorEmail = 'director@example.com';
        if (User::where('email', $directorEmail)->doesntExist()) {
            $directorUser = User::factory()->create([
                'name' => 'Director Ejemplo',
                'email' => $directorEmail,
                'password' => Hash::make('password')
            ]);
            $directorUser->assignRole($directorRole);
        }

        $apoyoEmail = 'apoyo@example.com';
        if (User::where('email', $apoyoEmail)->doesntExist()) {
            $apoyoUser = User::factory()->create([
                'name' => 'Docente Apoyo Ejemplo',
                'email' => $apoyoEmail,
                'password' => Hash::make('password')
            ]);
            $apoyoUser->assignRole($apoyoRole);
        }

        $docente1Email = 'docente1@example.com';
        if (User::where('email', $docente1Email)->doesntExist()) {
            $docenteUser1 = User::factory()->create([
                'name' => 'Docente Uno',
                'email' => $docente1Email,
                'password' => Hash::make('password')
            ]);
            $docenteUser1->assignRole($docenteRole);
        }

        $docente2Email = 'docente2@example.com';
        if (User::where('email', $docente2Email)->doesntExist()) {
            $docenteUser2 = User::factory()->create([
                'name' => 'Docente Dos',
                'email' => $docente2Email,
                'password' => Hash::make('password')
            ]);
            $docenteUser2->assignRole($docenteRole);
        }

        $docente3Email = 'docente3@example.com';
        if (User::where('email', $docente3Email)->doesntExist()) {
            $docenteUser3 = User::factory()->create([
                'name' => 'Docente Tres',
                'email' => $docente3Email,
                'password' => Hash::make('password')
            ]);
            $docenteUser3->assignRole($docenteRole);
        }

        // 3 estudiantes de prueba

        //'nombres','apellidos','ID_estudiante'
        $estudiante1 = Estudiante::firstOrCreate([
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'ID_estudiante' => '1234567890',
        ]);
        $estudiante2 = Estudiante::firstOrCreate([
            'nombres' => 'Ana',
            'apellidos' => 'Gómez',
            'ID_estudiante' => '0987654321',
        ]);
        $estudiante3 = Estudiante::firstOrCreate([
            'nombres' => 'Luis',
            'apellidos' => 'Martínez',
            'ID_estudiante' => '1122334455',
        ]);

        // 3 Periodos, Carreras y CarreraPeriodo de ejemplo
        $periodo = \App\Models\Periodo::firstOrCreate([
            'codigo_periodo' => '2025-1',
            'fecha_inicio' => '2025-01-01',
            'fecha_fin' => '2025-06-30',
        ]);
        //'codigo_carrera','nombre','departamento','sede'
        $carrera = \App\Models\Carrera::firstOrCreate([
            'codigo_carrera' => 'CS101',
            'nombre' => 'Ciencias de la Computación',
            'departamento' => 'DCCO',
            'sede' => 'Sangolqui',
        ]);
        //'carrera_id','periodo_id','docente_apoyo_id','director_id'
        $carreraPeriodo = \App\Models\CarrerasPeriodo::firstOrCreate([
            'carrera_id' => $carrera->id,
            'periodo_id' => $periodo->id,
            'docente_apoyo_id' => $apoyoUser->id, // Asignar el docente de apoyo
            'director_id' => $directorUser->id, // Asignar el director
        ]);
    }
}
