<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && $this->province_id === null;
    }

    public function isOperatorOrAdmin(): bool
    {
        return in_array($this->role, ['admin', 'operator'], true);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
}
