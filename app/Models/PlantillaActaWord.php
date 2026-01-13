<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaActaWord extends Model
{
    use HasFactory;

    protected $table = 'plantillas_acta_word';

    protected $fillable = [
        'nombre',
        'archivo_path',
        'activa',
        'descripcion',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /**
     * Relación con el usuario que creó la plantilla
     */
    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación con el usuario que actualizó la plantilla
     */
    public function usuarioActualizador()
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    /**
     * Scope para obtener solo plantillas activas
     */
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Obtener la plantilla activa actual
     */
    public static function obtenerPlantillaActiva()
    {
        return self::where('activa', true)->first();
    }

    /**
     * Activar esta plantilla y desactivar las demás
     */
    public function activar()
    {
        // Desactivar todas las demás plantillas
        self::where('id', '!=', $this->id)->update(['activa' => false]);

        // Activar esta plantilla
        $this->update(['activa' => true]);
    }
}
