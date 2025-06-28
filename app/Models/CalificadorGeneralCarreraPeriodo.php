<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificadorGeneralCarreraPeriodo extends Model
{
    use HasFactory;
    protected $table = 'calificador_general_carrera_periodos';

    protected $fillable = [
        'carrera_periodo_id',
        'user_id',
        // 'especialidad',
    ];

    public function carreraPeriodo()
    {
        return $this->belongsTo(CarrerasPeriodo::class, 'carrera_periodo_id');
    }

    public function user() // El docente calificador
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
