<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ["store_id", "name", "address", "code", "phone", "email", "tax_number", "note"];

    protected $hidden = ["deleted_at"];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
