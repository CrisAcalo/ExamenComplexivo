<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tribunale extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'tribunales';

    protected $fillable = [
        'carrera_periodo_id',
        'estudiante_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function carrerasPeriodo()
    {
        return $this->hasOne('App\Models\CarrerasPeriodo', 'id', 'carrera_periodo_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estudiante()
    {
        return $this->hasOne('App\Models\Estudiante', 'id', 'estudiante_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function miembrosTribunales()
    {
        return $this->hasMany('App\Models\MiembrosTribunal', 'tribunal_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tribunalComentarios()
    {
        return $this->hasMany('App\Models\TribunalComentario', 'tribunal_id', 'id');
    }

    // En app/Models/Tribunale.php
    public function logs()
    {
        return $this->hasMany(TribunalLog::class, 'tribunal_id')->latest(); // Mostrar los más recientes primero
    }

    /**
     * Genera el código de documento automáticamente basado en el año y orden de creación
     * Formato: UDED-FOR-V3-YYYY-NNN
     *
     * @return string
     */
    public function generarCodigoDocumento()
    {
        // Obtener el año de creación del tribunal (usar año actual si no existe created_at)
        $año = $this->created_at ? $this->created_at->year : date('Y');

        // Contar todos los tribunales creados en el mismo año antes que este (incluyendo este)
        $secuencia = self::whereYear('created_at', $año)
            ->where('id', '<=', $this->id)
            ->count();

        // Formatear el número de secuencia con 3 dígitos
        $numeroSecuencia = str_pad($secuencia, 3, '0', STR_PAD_LEFT);

        // Generar el código completo
        return "UDED-FOR-V3-{$año}-{$numeroSecuencia}";
    }
}
