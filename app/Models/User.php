<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements Authenticatable
{
    use AuthenticableTrait, HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'name', 
        'email'
    ];

    protected $hidden = [
        'password',
        'pivot'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    public function is($roleName)
    {
        foreach ($this->roles()->get() as $role)
        {
            if ($role->name == $roleName)
            {
                return true;
            }
        }
        return false;
    }
}
