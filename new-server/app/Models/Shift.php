<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ["name", "start_time", "end_time", "branch_id"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
