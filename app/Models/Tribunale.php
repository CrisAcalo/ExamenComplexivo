<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tribunale extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'tribunales';

    protected $fillable = [
        'carrera_periodo_id',
        'estudiante_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'es_plantilla',
        'descripcion_plantilla',
        'laboratorio',
        'nombre_tribunal',
        'caso',
        'acta_firmada_path',
        'acta_firmada_subida_por',
        'acta_firmada_fecha'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function carrerasPeriodo()
    {
        return $this->hasOne('App\Models\CarrerasPeriodo', 'id', 'carrera_periodo_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estudiante()
    {
        return $this->hasOne('App\Models\Estudiante', 'id', 'estudiante_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function miembrosTribunales()
    {
        return $this->hasMany('App\Models\MiembrosTribunal', 'tribunal_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tribunalComentarios()
    {
        return $this->hasMany('App\Models\TribunalComentario', 'tribunal_id', 'id');
    }

    // En app/Models/Tribunale.php
    public function logs()
    {
        return $this->hasMany(TribunalLog::class, 'tribunal_id')->latest(); // Mostrar los más recientes primero
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioSubioActa()
    {
        return $this->belongsTo('App\Models\User', 'acta_firmada_subida_por');
    }
}
