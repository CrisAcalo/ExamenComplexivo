<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionCriterio extends Model
{
    use HasFactory;

    protected $table = 'calificaciones_criterio';

    protected $fillable = [
        'criterio_id',
        'nombre',
        'valor',
        'descripcion'
    ];

    /**
     * Get the criterioComponente that owns the CalificacionCriterio.
     */
    public function criterioComponente()
    {
        return $this->belongsTo(CriterioComponente::class, 'criterio_id', 'id');
    }
}
