<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuantityCheckingItem extends Model
{
    use HasFactory;

    protected $fillable = ["quantity_checking_sheet_id", "item_id", "expected_quantity", "actual_quantity", "total"];

    public function quantity_checking_sheet()
    {
        return $this->belongsTo(QuantityCheckingSheet::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
