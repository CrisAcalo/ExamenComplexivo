<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponenteRubrica extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'componentes_rubrica';

    protected $fillable = ['rubrica_id', 'nombre', 'ponderacion'];
    /**
     * Get the rubrica that owns the ComponenteRubrica.
     */
    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class, 'rubrica_id', 'id');
    }

    /**
     * Get all of the criteriosComponente for the ComponenteRubrica.
     */
    public function criteriosComponente() // Nombre usado en Create.php
    {
        return $this->hasMany(CriterioComponente::class, 'componente_id', 'id');
    }
    // Relación a las asignaciones donde este componente de plantilla es usado
    public function asignacionesEnPlanes()
    {
        return $this->hasMany(AsignacionCalificadorComponentePlan::class, 'componente_rubrica_id');
    }
}
