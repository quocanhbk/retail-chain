<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuantityCheckingSheet extends Model
{
    use HasFactory;

    protected $fillable = ["code", "employee_id", "branch_id", "note"];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function quantityCheckingItems()
    {
        return $this->hasMany(QuantityCheckingItem::class);
    }
}
