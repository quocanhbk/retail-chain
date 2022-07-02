<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentRole extends Model
{
    use HasFactory;

    protected $fillable = ["employment_id", "role_id"];

    public function employment()
    {
        return $this->belongsTo(Employment::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
