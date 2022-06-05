<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPriceHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ["item_id", "price", "start_date", "end_date"];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
