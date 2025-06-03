<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanEvaluacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrera_periodo_id',
        'nombre',
        'descripcion',
    ];

    public function carreraPeriodo()
    {
        return $this->belongsTo(CarrerasPeriodo::class, 'carrera_periodo_id');
    }

    public function itemsPlanEvaluacion()
    {
        return $this->hasMany(ItemPlanEvaluacion::class, 'plan_evaluacion_id')->orderBy('orden');
    }
}
