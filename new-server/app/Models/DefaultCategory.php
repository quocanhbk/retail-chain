<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultCategory extends Model
{
    use HasFactory;

    protected $connection = "default_items";

    protected $table = "categories";

    public function items()
    {
        return $this->hasMany(DefaultItem::class);
    }
}
