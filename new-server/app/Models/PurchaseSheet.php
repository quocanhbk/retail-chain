<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'employee_id',
        'brand_id',
        'supplier_id',
        'discount',
        'discount_type',
        'total',
        'status',
        'note'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseSheetItems()
    {
        return $this->hasMany(PurchaseSheetItem::class);
    }
}
