<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    use HasFactory;

    protected $table = 'rubricas';

    protected $fillable = [
        'nombre',
    ];
    public function componentes()
    {
        return $this->hasMany(ComponenteRubrica::class, 'rubrica_id');
    }

    public function componentesRubrica() // El nombre debe coincidir exactamente
    {
        return $this->hasMany(ComponenteRubrica::class, 'rubrica_id', 'id');
    }
}
