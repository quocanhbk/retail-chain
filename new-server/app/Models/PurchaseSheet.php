<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        "code",
        "employee_id",
        "branch_id",
        "supplier_id",
        "discount",
        "discount_type",
        "total",
        "paid_amount",
        "note",
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseSheetItem::class);
    }
}
