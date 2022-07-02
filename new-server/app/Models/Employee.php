<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;

class Employee extends User
{
    use Notifiable;
    use HasFactory;
    use SoftDeletes;

    protected $guard = "employees";

    protected $fillable = [
        "name",
        "email",
        "password",
        "store_id",
        "avatar",
        "avatar_key",
        "phone",
        "birthday",
        "gender",
    ];

    protected $hidden = ["password", "remember_token", "deleted_at"];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function employments()
    {
        return $this->hasMany(Employment::class);
    }

    public function employment()
    {
        return $this->hasOne(Employment::class)->latestOfMany();
    }
}
