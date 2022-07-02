<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ["store_id", "name", "description"];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function employmentRoles()
    {
        return $this->hasMany(EmploymentRole::class);
    }

    public function permissionRoles()
    {
        return $this->hasMany(PermissionRole::class);
    }
}
