<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use HasRoles;
    use Impersonate;

    public $timestamps = true;

    protected $table = 'users';

    protected $fillable = [
        'ID_espe',
        'name',
        'lastname',
        'username',
        'email',
        'cedula',
        'password',
        'departamento_id',
    ];

    public function getAuthIdentifierName()
    {
        return 'id';
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sede()
    {
        return $this->hasOne('App\Models\SedeSede', 'id', 'sede_id');
    }

    /**
     * Relación organizacional - Departamento al que pertenece el docente
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }

    /**
     * Relaciones contextuales - Asignaciones por carrera/periodo
     */
    public function carrerasComoDirector()
    {
        return $this->hasMany(CarrerasPeriodo::class, 'director_id', 'id');
    }

    public function carrerasComoApoyo()
    {
        return $this->hasMany(CarrerasPeriodo::class, 'docente_apoyo_id', 'id');
    }

    public function asignacionesCalificadorGeneral()
    {
        return $this->hasMany(CalificadorGeneralCarreraPeriodo::class, 'user_id', 'id');
    }

    public function miembrosTribunales()
    {
        return $this->hasMany(MiembrosTribunal::class, 'user_id', 'id');
    }

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null; // see the note above in Gate::before about why null must be returned here.
    }
}
