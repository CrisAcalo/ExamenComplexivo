<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriterioComponente extends Model
{
    use HasFactory;
    //nombre de la tabla
    protected $table = 'criterios_componente';
    protected $fillable = [
        'componente_id',
        'nombre',
    ];

    /**
     * Get the componenteRubrica that owns the CriterioComponente.
     */
    public function componenteRubrica()
    {
        return $this->belongsTo(ComponenteRubrica::class, 'componente_id', 'id');
    }

    /**
     * Get all of the calificacionesCriterio for the CriterioComponente.
     */
    public function calificacionesCriterio() // Nombre usado en Create.php
    {
        return $this->hasMany(CalificacionCriterio::class, 'criterio_id', 'id');
    }
}
