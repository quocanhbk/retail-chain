<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuantityCheckingItem extends Model
{
    use HasFactory;

    protected $fillable = ["quantity_checking_sheet_id", "item_id", "expected", "actual", "total"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function quantityCheckingSheet()
    {
        return $this->belongsTo(QuantityCheckingSheet::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
