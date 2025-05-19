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
        'descripción'
    ];

    public function criterio()
    {
        return $this->belongsTo(CriterioComponente::class, 'criterio_id');
    }
}
