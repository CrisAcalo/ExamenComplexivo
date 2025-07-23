<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
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

        // --- 0. desasignar todos los permisos a los roles ---
        Role::all()->each(function ($role) {
            $role->syncPermissions([]);
        });
        // --- 1. CREAR PERMISOS ---
        $permissionsList = [
            // === PERMISOS DE SISTEMA Y ADMINISTRACIÓN ===
            'gestionar usuarios',                       // CRUD de usuarios
            'gestionar roles y permisos',             // Asignar roles a usuarios, crear/editar roles/permisos
            'ver dashboard administrativo',           // Acceso a estadísticas y métricas del sistema
            'gestionar configuracion sistema',       // Configuraciones globales

            // === PERMISOS DE ESTRUCTURA ACADÉMICA ===
            'gestionar periodos',
            'gestionar periodos:crear',                     // CRUD de periodos académicos
            'gestionar periodos:editar',                     // CRUD de periodos académicos
            'gestionar periodos:eliminar',                   // CRUD de periodos académicos
            'gestionar periodos:ver',                        // CRUD de periodos académicos

            'gestionar carreras',                         // CRUD de carreras
            'gestionar departamentos',               // CRUD de departamentos
            'asignar carrera a periodo',            // Crear la entidad carrera_periodo y asignar director/apoyo
            'ingresar asignacion carrera-periodo',    // Ingresar a la lista de tribunales de un carrera_periodo
            'editar asignacion carrera-periodo',    // Cambiar director/apoyo en carrera_periodo
            'eliminar asignacion carrera-periodo', // Eliminar carrera_periodo (con validaciones)


            // === PERMISOS DE ESTUDIANTES ===
            'gestionar estudiantes',                 // CRUD de estudiantes
            'importar estudiantes',                  // Importar desde Excel
            'exportar estudiantes',                  // Exportar a Excel/PDF
            'ver listado estudiantes',              // Ver lista de estudiantes (contextual)

            // === PERMISOS DE RÚBRICAS ===
            'gestionar plantillas rubricas',        // CRUD de plantillas de rúbricas
            'gestionar rubricas',                   // CRUD de rúbricas específicas
            'asignar rubricas a carrera-periodo',   // Asignar rúbricas a una carrera-periodo
            'ver rubricas',                         // Ver rúbricas (contextual)

            // === PERMISOS DENTRO DE UN CARRERA_PERIODO ===
            'configurar plan evaluacion',           // Crear/editar el plan para un carrera-periodo
            'ver plan evaluacion',                  // Ver plan de evaluación (contextual)
            'asignar docentes calificadores generales',

            // === PERMISOS DE TRIBUNALES ===
            'crear tribunales',                     // En un carrera-periodo
            'editar tribunales',                    // Editar datos básicos de tribunales
            'eliminar tribunales',                  // De un carrera-periodo (con validaciones)
            'ver listado tribunales',               // De un carrera-periodo
            'gestionar estado tribunales',          // Abrir/cerrar tribunales
            'asignar miembros tribunales',          // Asignar docentes a tribunales

            // === PERMISOS DE CALIFICACIONES ===
            'ver todas las calificaciones de un tribunal', // Ver notas de todos los miembros
            'calificar en tribunal',                // Ingresar/editar calificaciones en tribunales asignados
            'exportar calificaciones',             // Exportar notas y resultados

            // === PERMISOS DE TRIBUNAL INDIVIDUAL ===
            'ver detalles mi tribunal',            // Ver la página de perfil de un tribunal donde es miembro
            'calificar mi tribunal',               // Ingresar/editar/ver PROPIAS calificaciones
            'subir evidencia mi tribunal',         // Subir documentos de evidencia

            // === PERMISOS DE PRESIDENTE DE TRIBUNAL ===
            'editar datos basicos mi tribunal (presidente)', // Editar fecha, hora, miembros (antes de calificar)
            'exportar acta mi tribunal (presidente)',      // Generar/exportar el acta del tribunal que preside
            'gestionar actas tribunales',                  // Administrar actas de tribunales

            // === PERMISOS DE REPORTES Y ESTADÍSTICAS ===
            'ver resumenes y reportes academicos',  // Generar/ver reportes
            'exportar reportes',                    // Exportar reportes en diferentes formatos
            'ver estadisticas carrera-periodo',     // Ver métricas de una carrera-periodo específica

            // === PERMISOS DE IMPORTACIÓN/EXPORTACIÓN ===
            'importar profesores',                  // Importar docentes desde Excel
            'exportar datos sistema',              // Exportar datos del sistema
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
        // También se asignan explícitamente para claridad y respaldo
        $superAdminRole->givePermissionTo(Permission::all());

        // === ADMINISTRADOR (Operativo) ===
        // Tiene permisos globales para gestionar todo el sistema
        $adminRole->givePermissionTo([
            // Sistema y administración
            'gestionar usuarios',
            'gestionar roles y permisos',
            'ver dashboard administrativo',
            'gestionar configuracion sistema',

            // Estructura académica
            'gestionar periodos',
            'gestionar periodos:crear',
            'gestionar periodos:editar',
            'gestionar periodos:eliminar',
            'gestionar periodos:ver',
            'gestionar carreras',
            'gestionar departamentos',
            'asignar carrera a periodo',
            'ingresar asignacion carrera-periodo',
            'editar asignacion carrera-periodo',
            'eliminar asignacion carrera-periodo',

            // Estudiantes
            'gestionar estudiantes',
            'importar estudiantes',
            'exportar estudiantes',
            'ver listado estudiantes',

            // Rúbricas
            'gestionar plantillas rubricas',
            'gestionar rubricas',
            'asignar rubricas a carrera-periodo',
            'ver rubricas',

            // Plan de evaluación
            'configurar plan evaluacion',
            'ver plan evaluacion',
            'asignar docentes calificadores generales',

            // Tribunales
            'crear tribunales',
            'editar tribunales',
            'eliminar tribunales',
            'ver listado tribunales',
            'gestionar estado tribunales',
            'asignar miembros tribunales',

            // Calificaciones y reportes
            'ver todas las calificaciones de un tribunal',
            'calificar en tribunal',
            'exportar calificaciones',
            'ver resumenes y reportes academicos',
            'exportar reportes',
            'ver estadisticas carrera-periodo',
            'gestionar actas tribunales',

            // Importación/exportación
            'importar profesores',
            'exportar datos sistema',
        ]);

        // === DIRECTOR DE CARRERA ===
        // Permisos básicos + contextuales (manejados por ContextualAuth)
        // Los permisos contextuales (gestionar estudiantes, rúbricas, etc.)
        // se verifican dinámicamente según la asignación en carreras_periodos
        $directorRole->givePermissionTo([
            // Permisos base para ver/gestionar contenido (contextuales)
            'ver listado estudiantes',      // ContextualAuth verifica si es director de esa carrera-período
            'gestionar estudiantes',        // ContextualAuth verifica acceso
            'importar estudiantes',         // ContextualAuth verifica acceso
            'ver rubricas',                 // ContextualAuth verifica acceso
            'gestionar rubricas',           // ContextualAuth verifica acceso
            'asignar rubricas a carrera-periodo', // ContextualAuth verifica acceso
            'configurar plan evaluacion',   // ContextualAuth verifica acceso
            'ver plan evaluacion',          // ContextualAuth verifica acceso
            'asignar docentes calificadores generales', // ContextualAuth verifica acceso
            'crear tribunales',             // ContextualAuth verifica acceso
            'editar tribunales',            // ContextualAuth verifica acceso
            'eliminar tribunales',          // ContextualAuth verifica acceso
            'ver listado tribunales',       // ContextualAuth verifica acceso
            'gestionar estado tribunales',  // ContextualAuth verifica acceso
            'asignar miembros tribunales',  // ContextualAuth verifica acceso
            'ver todas las calificaciones de un tribunal', // ContextualAuth verifica acceso
            'exportar calificaciones',     // ContextualAuth verifica acceso
            'ver resumenes y reportes academicos', // ContextualAuth verifica acceso
            'exportar reportes',           // ContextualAuth verifica acceso
            'ver estadisticas carrera-periodo', // ContextualAuth verifica acceso

            // Permisos básicos de tribunal (cuando es miembro)
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'calificar en tribunal',
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);

        // === DOCENTE DE APOYO ===
        // Similar al Director pero con menos permisos administrativos
        // Los permisos contextuales se verifican dinámicamente
        $apoyoRole->givePermissionTo([
            // Permisos base para ver/gestionar contenido (contextuales)
            'ver listado estudiantes',      // ContextualAuth verifica acceso
            'gestionar estudiantes',        // ContextualAuth verifica acceso
            'importar estudiantes',         // ContextualAuth verifica acceso
            'ver rubricas',                 // ContextualAuth verifica acceso
            'gestionar rubricas',           // ContextualAuth verifica acceso
            'configurar plan evaluacion',   // ContextualAuth verifica acceso
            'ver plan evaluacion',          // ContextualAuth verifica acceso
            'asignar docentes calificadores generales', // ContextualAuth verifica acceso
            'crear tribunales',             // ContextualAuth verifica acceso
            'editar tribunales',            // ContextualAuth verifica acceso
            'eliminar tribunales',          // ContextualAuth verifica acceso
            'ver listado tribunales',       // ContextualAuth verifica acceso
            'gestionar estado tribunales',  // ContextualAuth verifica acceso
            'asignar miembros tribunales',  // ContextualAuth verifica acceso
            'ver todas las calificaciones de un tribunal', // ContextualAuth verifica acceso
            'exportar calificaciones',     // ContextualAuth verifica acceso
            'ver resumenes y reportes academicos', // ContextualAuth verifica acceso
            'exportar reportes',           // ContextualAuth verifica acceso
            'ver estadisticas carrera-periodo', // ContextualAuth verifica acceso

            // Permisos básicos de tribunal (cuando es miembro)
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'calificar en tribunal',
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);

        // === DOCENTE ===
        // Solo permisos relacionados con SUS tribunales asignados
        $docenteRole->givePermissionTo([
            'ver detalles mi tribunal',
            'calificar mi tribunal',
            'subir evidencia mi tribunal',
            'calificar en tribunal',
            // Solo si es presidente del tribunal
            'editar datos basicos mi tribunal (presidente)',
            'exportar acta mi tribunal (presidente)',
        ]);
    }
}
