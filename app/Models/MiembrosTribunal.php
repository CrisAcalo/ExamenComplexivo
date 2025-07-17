<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembrosTribunal extends Model
{
    use HasFactory;

    //tabla
    protected $table = 'miembros_tribunales';

    protected $fillable = [
        'tribunal_id',
        'user_id',
        'status'
    ];
    public function tribunal()
    {
        return $this->belongsTo(Tribunale::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calificacionesRegistradas()
    {
        // Obtener calificaciones de este miembro para este tribunal específico
        return MiembroCalificacion::where('user_id', $this->user_id)
                                  ->where('tribunal_id', $this->tribunal_id);
    }

    /**
     * Método helper para verificar si tiene calificaciones
     */
    public function tieneCalificaciones()
    {
        return $this->calificacionesRegistradas()->exists();
    }
}
