<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
	use HasFactory;

    public $timestamps = true;

    protected $table = 'estudiantes';

    protected $fillable = ['nombres','apellidos','ID_estudiante', 'cedula', 'correo', 'telefono', 'username'];

    //atributo para retornar nombres_completos_id
    protected $appends = ['nombres_completos_id'];

    /**
     * Accessor para obtener el nombre completo del estudiante con su ID.
     *
     * @return string
     */
    public function getNombresCompletosIdAttribute()
    {
        return $this->nombres . ' ' . $this->apellidos . ' (' . $this->ID_estudiante . ')';
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tribunales()
    {
        return $this->hasMany('App\Models\Tribunale', 'estudiante_id', 'id');
    }

}
