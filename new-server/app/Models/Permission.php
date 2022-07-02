<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ["action", "action_slug"];

    public function roles()
    {
        return $this->hasMany(PermissionRole::class);
    }
}
