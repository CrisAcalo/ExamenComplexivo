<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
	use HasFactory;
	
    public $timestamps = true;

    protected $table = 'carreras';

    protected $fillable = ['codigo_carrera','nombre','departamento','sede'];
	
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carrerasPeriodos()
    {
        return $this->hasMany('App\Models\CarrerasPeriodo', 'carrera_id', 'id');
    }
    
}
