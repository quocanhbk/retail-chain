<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnPurchaseSheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_purchase_sheet_id',
        'item_id',
        'quantity',
        'price',
        'return_price',
        'return_price_type',
        'total',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function returnPurchaseSheet()
    {
        return $this->belongsTo(ReturnPurchaseSheet::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
