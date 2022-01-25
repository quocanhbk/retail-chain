<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseSheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_sheet_id',
        'item_id',
        'quantity',
        'price',
        'discount',
        'discount_type',
        'total',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function purchaseSheet()
    {
        return $this->belongsTo(PurchaseSheet::class);
    }
}
