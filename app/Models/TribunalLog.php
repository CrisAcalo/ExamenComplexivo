<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TribunalLog extends Model
{
    use HasFactory;

    protected $table = 'tribunal_logs';

    protected $fillable = [
        'tribunal_id',
        'user_id',
        'accion',
        'descripcion',
        'datos_antiguos',
        'datos_nuevos',
    ];

    protected $casts = [
        'datos_antiguos' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function tribunal()
    {
        return $this->belongsTo(Tribunale::class, 'tribunal_id'); // Ajusta Tribunale si tu modelo se llama diferente
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
