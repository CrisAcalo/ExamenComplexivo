<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
	use HasFactory;
	
    public $timestamps = true;

    protected $table = 'periodos';

    protected $fillable = ['codigo_periodo','fecha_inicio','fecha_fin'];
	
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carrerasPeriodos()
    {
        return $this->hasMany('App\Models\CarrerasPeriodo', 'periodo_id', 'id');
    }
    
}
