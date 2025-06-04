<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroCalificacion extends Model
{
    use HasFactory;
    protected $table = 'miembro_calificacion';
    protected $fillable = [
        'criterio_id',
        'calificacion_criterio_id',
        'miembro_tribunal_id',
    ];
}
