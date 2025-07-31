<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\CarrerasPeriodo;
use App\Models\MiembrosTribunal;
use App\Models\Tribunale;

class ContextualAuth
{
    /**
     * Verifica si el usuario es Super Admin o Administrador
     */
    public static function isSuperAdminOrAdmin($user): bool
    {
        if (!$user) return false;

        return $user->roles()->whereIn('name', ['Super Admin', 'Administrador'])->exists();
    }

    /**
     * Verifica si el usuario es director de una carrera-período específica
     */
    public static function isDirectorOf(User $user, $carreraPeriodoId): bool
    {
        return CarrerasPeriodo::where('id', $carreraPeriodoId)
            ->where('director_id', $user->id)
            ->exists();
    }

    /**
     * Verifica si el usuario es docente de apoyo de una carrera-período específica
     */
    public static function isApoyoOf(User $user, $carreraPeriodoId): bool
    {
        return CarrerasPeriodo::where('id', $carreraPeriodoId)
            ->where('docente_apoyo_id', $user->id)
            ->exists();
    }

    /**
     * Verifica si el usuario puede acceder a una carrera-período específica
     */
    public static function canAccessCarreraPeriodo(User $user, $carreraPeriodoId): bool
    {
        // Super Admin y Administrador tienen acceso global
        if (self::isSuperAdminOrAdmin($user)) {
            return true;
        }

        // Director o Docente de Apoyo de esa carrera-período específica
        return self::isDirectorOf($user, $carreraPeriodoId) ||
               self::isApoyoOf($user, $carreraPeriodoId);
    }

    /**
     * Obtiene todas las carreras-período donde el usuario es director
     */
    public static function getCarrerasAsDirector(User $user)
    {
        return CarrerasPeriodo::where('director_id', $user->id)
            ->with(['carrera', 'periodo'])
            ->get();
    }

    /**
     * Obtiene todas las carreras-período donde el usuario es docente de apoyo
     */
    public static function getCarrerasAsApoyo(User $user)
    {
        return CarrerasPeriodo::where('docente_apoyo_id', $user->id)
            ->with(['carrera', 'periodo'])
            ->get();
    }

    /**
     * Obtiene todas las carreras-período a las que el usuario tiene acceso
     */
    public static function getAccessibleCarrerasPeriodos(User $user)
    {
        // Super Admin y Administrador ven todas
        if (self::isSuperAdminOrAdmin($user)) {
            return CarrerasPeriodo::with(['carrera', 'periodo'])->get();
        }

        // Director y Docente de Apoyo solo ven las suyas
        return CarrerasPeriodo::where(function($query) use ($user) {
            $query->where('director_id', $user->id)
                  ->orWhere('docente_apoyo_id', $user->id);
        })->with(['carrera', 'periodo'])->get();
    }

    /**
     * Verifica si el usuario es miembro de un tribunal específico
     */
    public static function isMemberOfTribunal(User $user, $tribunalId): bool
    {
        return MiembrosTribunal::where('user_id', $user->id)
            ->where('tribunal_id', $tribunalId)
            ->exists();
    }

    /**
     * Verifica si el usuario es presidente de un tribunal específico
     */
    public static function isPresidentOfTribunal(User $user, $tribunalId): bool
    {
        return MiembrosTribunal::where('user_id', $user->id)
            ->where('tribunal_id', $tribunalId)
            ->where('status', 'PRESIDENTE')
            ->exists();
    }

    /**
     * Verifica permisos contextuales para estudiantes
     */
    public static function canManageEstudiantesInCarreraPeriodo(User $user, $carreraPeriodoId): bool
    {
        // Permisos globales
        if (self::isSuperAdminOrAdmin($user) &&
            $user->can('gestionar estudiantes')) {
            return true;
        }

        // Permisos contextuales para Director y Docente de Apoyo
        if ((self::isDirectorOf($user, $carreraPeriodoId)) ||
            (self::isApoyoOf($user, $carreraPeriodoId))) {
            return $user->can('ver listado estudiantes');
        }

        return false;
    }

    /**
     * Verifica permisos contextuales para rúbricas
     */
    public static function canManageRubricasInCarreraPeriodo(User $user, $carreraPeriodoId): bool
    {
        // Permisos globales
        if (self::isSuperAdminOrAdmin($user) &&
            ($user->can('gestionar rubricas') || $user->can('gestionar plantillas rubricas'))) {
            return true;
        }

        // Permisos contextuales para Director y Docente de Apoyo
        if ((self::isDirectorOf($user, $carreraPeriodoId)) ||
            (self::isApoyoOf($user, $carreraPeriodoId))) {
            return $user->can('ver rubricas');
        }

        return false;
    }

    /**
     * Verifica permisos contextuales para tribunales
     */
    public static function canManageTribunalesInCarreraPeriodo(User $user, $carreraPeriodoId): bool
    {
        // Permisos globales
        if (self::isSuperAdminOrAdmin($user) &&
            $user->can('ver listado tribunales')) {
            return true;
        }

        // Permisos contextuales para Director y Docente de Apoyo
        if ((self::isDirectorOf($user, $carreraPeriodoId)) ||
            (self::isApoyoOf($user, $carreraPeriodoId))) {
            return $user->can('ver listado tribunales');
        }

        return false;
    }

    /**
     * Verifica si el usuario tiene alguna asignación activa (para el panel)
     */
    public static function hasActiveAssignments(User $user): bool
    {
        if (self::isSuperAdminOrAdmin($user)) {
            return true;
        }

        // Verificar si es Director o Docente de Apoyo de alguna carrera-período
        $hasCarreraAssignment = CarrerasPeriodo::where(function($query) use ($user) {
            $query->where('director_id', $user->id)
                  ->orWhere('docente_apoyo_id', $user->id);
        })->exists();

        // Verificar si es miembro de algún tribunal
        $hasTribunalAssignment = MiembrosTribunal::where('user_id', $user->id)->exists();

        // Verificar si es calificador general
        $hasCalificadorGeneralAssignment = \App\Models\CalificadorGeneralCarreraPeriodo::where('user_id', $user->id)->exists();

        return $hasCarreraAssignment || $hasTribunalAssignment || $hasCalificadorGeneralAssignment;
    }

    /**
     * Verifica si el usuario es calificador general en una carrera-período específica
     */
    public static function isCalificadorGeneralOf($user, $carreraPeriodoId): bool
    {
        if (!$user) return false;

        return \App\Models\CalificadorGeneralCarreraPeriodo::where('carrera_periodo_id', $carreraPeriodoId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Verifica si el usuario tiene algún tipo de acceso a calificar en un tribunal
     */
    public static function canCalifyInTribunal($user, $tribunal): bool
    {
        if (!$user || !$tribunal) return false;

        // Super Admin siempre puede
        if (self::isSuperAdminOrAdmin($user)) {
            return true;
        }

        $carreraPeriodoId = $tribunal->carrera_periodo_id;

        // 1. Es miembro físico del tribunal
        if (self::isMemberOfTribunal($user, $tribunal->id)) {
            return true;
        }

        // 2. Es Director de la carrera-período
        if (self::isDirectorOf($user, $carreraPeriodoId)) {
            return true;
        }

        // 3. Es Docente de Apoyo de la carrera-período
        if (self::isApoyoOf($user, $carreraPeriodoId)) {
            return true;
        }

        // 4. Es Calificador General de la carrera-período
        if (self::isCalificadorGeneralOf($user, $carreraPeriodoId)) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene el tipo de asignación del usuario en un tribunal específico
     */
    public static function getTipoAsignacionEnTribunal($user, $tribunal): array
    {
        if (!$user || !$tribunal) {
            return ['tipo' => 'sin_acceso', 'descripcion' => 'Sin Acceso', 'puede_calificar' => false];
        }

        $carreraPeriodoId = $tribunal->carrera_periodo_id;

        // 1. Verificar si es miembro directo del tribunal
        $miembroDirecto = MiembrosTribunal::where('user_id', $user->id)
            ->where('tribunal_id', $tribunal->id)
            ->first();

        if ($miembroDirecto) {
            return [
                'tipo' => 'miembro_tribunal',
                'descripcion' => ucwords(strtolower(str_replace('_', ' ', $miembroDirecto->status))),
                'puede_calificar' => true,
                'detalle' => 'Miembro asignado del tribunal'
            ];
        }

        // 2. Verificar si es Director de Carrera
        if (self::isDirectorOf($user, $carreraPeriodoId)) {
            return [
                'tipo' => 'director',
                'descripcion' => 'Director de Carrera',
                'puede_calificar' => true,
                'detalle' => 'Director de la carrera-período'
            ];
        }

        // 3. Verificar si es Docente de Apoyo
        if (self::isApoyoOf($user, $carreraPeriodoId)) {
            return [
                'tipo' => 'apoyo',
                'descripcion' => 'Docente de Apoyo',
                'puede_calificar' => true,
                'detalle' => 'Docente de apoyo de la carrera-período'
            ];
        }

        // 4. Verificar si es Calificador General
        if (self::isCalificadorGeneralOf($user, $carreraPeriodoId)) {
            return [
                'tipo' => 'calificador_general',
                'descripcion' => 'Calificador General',
                'puede_calificar' => true,
                'detalle' => 'Calificador general del período'
            ];
        }

        return ['tipo' => 'sin_acceso', 'descripcion' => 'Sin Acceso', 'puede_calificar' => false];
    }

    /**
     * Obtiene información del contexto del usuario para mostrar en el panel
     */
    public static function getUserContextInfo(User $user): array
    {
        $info = [
            'carreras_director' => collect(),
            'carreras_apoyo' => collect(),
            'tribunales' => collect(),
            'calificador_general' => collect(),
            'has_assignments' => false
        ];

        // SIEMPRE obtener asignaciones contextuales, independientemente del rol global

        // Asignaciones como Director
        $info['carreras_director'] = self::getCarrerasAsDirector($user);

        // Asignaciones como Docente de Apoyo
        $info['carreras_apoyo'] = self::getCarrerasAsApoyo($user);

        // Asignaciones en tribunales como miembro
        $info['tribunales'] = MiembrosTribunal::where('user_id', $user->id)
            ->with(['tribunal.estudiante', 'tribunal.carrerasPeriodo.carrera'])
            ->get();

        // Asignaciones como Calificador General
        $info['calificador_general'] = \App\Models\CalificadorGeneralCarreraPeriodo::where('user_id', $user->id)
            ->with(['carreraPeriodo.carrera', 'carreraPeriodo.periodo'])
            ->get();

        // Un usuario tiene asignaciones cuando:
        // 1. Es Super Admin/Admin (acceso global) O
        // 2. Tiene asignaciones contextuales específicas
        $info['has_assignments'] = self::isSuperAdminOrAdmin($user) ||
                                  $info['carreras_director']->isNotEmpty() ||
                                  $info['carreras_apoyo']->isNotEmpty() ||
                                  $info['tribunales']->isNotEmpty() ||
                                  $info['calificador_general']->isNotEmpty();

        return $info;
    }
}
