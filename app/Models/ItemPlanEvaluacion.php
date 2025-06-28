<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPlanEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'items_plan_evaluacion';

    protected $fillable = [
        'plan_evaluacion_id',
        'nombre_item',
        'tipo_item',
        'ponderacion_global',
        'rubrica_plantilla_id',
        'calificado_por_nota_directa',
        'orden',
    ];

    public function planEvaluacion()
    {
        return $this->belongsTo(PlanEvaluacion::class, 'plan_evaluacion_id', 'id');
    }

    public function rubricaPlantilla()
    {
        // Esta relación es para cuando el tipo_item es RUBRICA_TABULAR
        return $this->belongsTo(Rubrica::class, 'rubrica_plantilla_id');
    }

    // Nueva relación para las asignaciones de sus componentes (si es rúbrica)
    public function asignacionesCalificadorComponentes()
    {
        return $this->hasMany(AsignacionCalificadorComponentePlan::class, 'item_plan_id');
    }
}
