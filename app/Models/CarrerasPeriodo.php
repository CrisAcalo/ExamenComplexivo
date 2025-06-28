<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrerasPeriodo extends Model
{
	use HasFactory;

    public $timestamps = true;

    protected $table = 'carreras_periodos';

    protected $fillable = ['carrera_id','periodo_id','docente_apoyo_id','director_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function carrera()
    {
        return $this->hasOne('App\Models\Carrera', 'id', 'carrera_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function componentesEvaluacions()
    {
        return $this->hasMany('App\Models\ComponentesEvaluacion', 'carrera_periodo_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function criterioCalificaciones()
    {
        return $this->hasMany('App\Models\CriterioCalificacione', 'carrera_periodo_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function periodo()
    {
        return $this->hasOne('App\Models\Periodo', 'id', 'periodo_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tribunales()
    {
        return $this->hasMany('App\Models\Tribunale', 'carrera_periodo_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function director()
    {
        return $this->hasOne('App\Models\User', 'id', 'director_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function docenteApoyo()
    {
        return $this->hasOne('App\Models\User', 'id', 'docente_apoyo_id');
    }

    // NUEVA RELACIÓN
    public function calificadoresGenerales()
    {
        // Devuelve los registros de la tabla pivote
        return $this->hasMany(CalificadorGeneralCarreraPeriodo::class, 'carrera_periodo_id');
    }

    public function docentesCalificadoresGenerales()
    {
        // Devuelve directamente los usuarios (docentes) que son calificadores generales
        return $this->belongsToMany(User::class, 'calificador_general_carrera_periodos', 'carrera_periodo_id', 'user_id');
    }
}
