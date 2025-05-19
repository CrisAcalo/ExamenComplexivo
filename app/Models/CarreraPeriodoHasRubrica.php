<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarreraPeriodoHasRubrica extends Model
{
    use HasFactory;
    protected $table = 'carreras_periodos_has_rubrica';
    protected $fillable = [
        'carrera_periodo_id',
        'rubrica_id',
    ];
    public function carreraPeriodo()
    {
        return $this->belongsTo(CarrerasPeriodo::class, 'carrera_periodo_id');
    }
    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class, 'rubrica_id');
    }
}
