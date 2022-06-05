<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Store extends User implements MustVerifyEmail
{
    use HasFactory;

    protected $guard = "stores";

    protected $fillable = ["name", "email", "password"];

    protected $hidden = ["password", "remember_token", "email_verified_at"];

    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function categories()
    {
        return $this->hasMany(ItemCategory::class);
    }
}
