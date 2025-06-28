<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamentos';
    protected $fillable = [
        'codigo_departamento',
        'nombre',
    ];
    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'departamento_id');
    }
}
