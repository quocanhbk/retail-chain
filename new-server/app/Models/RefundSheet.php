<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'branch_id',
        'employee_id',
        'invoice_id',
        'total',
        'reason'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }
}
