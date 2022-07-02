<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ["name", "start_time", "end_time", "branch_id"];

    protected $hidden = ["deleted_at"];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
