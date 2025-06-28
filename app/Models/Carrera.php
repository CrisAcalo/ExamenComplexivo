<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'carreras';

    protected $fillable = ['departamento_id', 'codigo_carrera', 'nombre', 'sede', 'modalidad'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carrerasPeriodos()
    {
        return $this->hasMany('App\Models\CarrerasPeriodo', 'carrera_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }
}
