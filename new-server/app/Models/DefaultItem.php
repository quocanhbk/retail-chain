<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultItem extends Model
{
    use HasFactory;

    protected $connection = "default_items";

    protected $table = "barcode_data";

    public function category()
    {
        return $this->belongsTo(DefaultCategory::class);
    }
}
