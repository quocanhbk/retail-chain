<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    use HasFactory;

    protected $fillable = ["refund_sheet_id", "return_item_id", "quantity", "resellable"];

    public function refundSheet()
    {
        return $this->belongsTo(RefundSheet::class);
    }
}
