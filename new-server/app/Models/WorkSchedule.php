<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'employee_id',
        'branch_id',
        'date',
        'note',
        'is_absent'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
