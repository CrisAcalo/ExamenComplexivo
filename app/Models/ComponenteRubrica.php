<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponenteRubrica extends Model
{
	use HasFactory;

    public $timestamps = true;

    protected $table = 'componentes_rubrica';

    protected $fillable = ['rubrica_id','nombre','ponderacion'];
    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class, 'rubrica_id');
    }
    public function criterios()
    {
        return $this->hasMany(CriterioComponente::class, 'componente_id');
    }
}
