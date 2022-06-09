<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentRole extends Model
{
    use HasFactory;

    protected $fillable = ["employment_id", "role"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function employment()
    {
        return $this->belongsTo(Employment::class);
    }
}
