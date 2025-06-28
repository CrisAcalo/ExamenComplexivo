<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroCalificacion extends Model
{
    use HasFactory;
    protected $table = 'miembro_calificacion';

    protected $fillable = [
        'user_id',
        'tribunal_id',
        'item_plan_evaluacion_id',
        'criterio_id',
        'calificacion_criterio_id',
        'nota_obtenida_directa',
        'observacion',
    ];

    public function userCalificador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunale::class, 'tribunal_id');
    }
    public function miembroTribunal()
    {
        return $this->belongsTo(MiembrosTribunal::class, 'miembro_tribunal_id');
    }
    public function calificacionesRegistradas()
    {
        return $this->hasMany(MiembroCalificacion::class, 'miembro_tribunal_id');
    }

    public function itemPlanEvaluacion()
    {
        return $this->belongsTo(ItemPlanEvaluacion::class, 'item_plan_evaluacion_id');
    }
    public function criterioCalificado() // El criterio que se calificó
    {
        return $this->belongsTo(CriterioComponente::class, 'criterio_id');
    }
    public function opcionCalificacionElegida() // La opción de CalificacionCriterio que se seleccionó
    {
        return $this->belongsTo(CalificacionCriterio::class, 'calificacion_criterio_id');
    }
}
