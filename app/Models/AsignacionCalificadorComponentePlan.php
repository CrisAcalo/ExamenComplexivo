<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionCalificadorComponentePlan extends Model
{
    use HasFactory;

    protected $table = 'asignacion_calificador_comp_plans';

    protected $fillable = [
        'item_plan_id',
        'componente_rubrica_id',
        'calificado_por',
    ];

    public function itemPlanEvaluacion()
    {
        return $this->belongsTo(ItemPlanEvaluacion::class, 'item_plan_id');
    }

    public function componenteRubrica() // El componente de la plantilla
    {
        return $this->belongsTo(ComponenteRubrica::class, 'componente_rubrica_id');
    }
}
