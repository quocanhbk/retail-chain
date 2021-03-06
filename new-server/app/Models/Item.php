<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ["store_id", "barcode", "name", "image", "item_category_id"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function itemProperties()
    {
        return $this->hasMany(ItemProperty::class);
    }
}
