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
        'criterio',
    ];

    public function componente()
    {
        return $this->belongsTo(ComponenteRubrica::class, 'componente_id');
    }
    public function criteriosCalificaciones()
    {
        return $this->hasMany(CalificacionCriterio::class, 'criterio_id');
    }
}
