<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'shift_id',
        'user_id_list'
        'start_date'
        'end_date'
        'absent',
        'note'
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

}