<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;

class Employee extends User
{
    use HasFactory;

    protected $guard = 'employees';

    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id',
        'avatar_url',
        'phone',
        'birthday',
        'gender',
        'active'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function employments() {
        return $this->hasMany(Employment::class);
    }

    public function employment() {
        return $this->hasOne(Employment::class)->latestOfMany();
    }

}
