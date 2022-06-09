<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemProperty extends Model
{
    use HasFactory;

    protected $fillable = ["item_id", "branch_id", "quantity", "sell_price", "base_price", "last_purchase_price"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
