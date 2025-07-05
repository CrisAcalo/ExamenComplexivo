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
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null; // see the note above in Gate::before about why null must be returned here.
    }
}
