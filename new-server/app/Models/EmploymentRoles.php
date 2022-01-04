<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentRoles extends Model
{
    use HasFactory;

    protected $fillable = [
        'employment_id',
        'role'
    ];

    public function employment()
    {
        return $this->belongsTo(Employment::class);
    }
}
