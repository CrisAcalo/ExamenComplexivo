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
        return $this->hasMany(MiembroCalificacion::class, 'miembro_tribunal_id');
    }
}
