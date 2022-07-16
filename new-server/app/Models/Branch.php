<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ["name", "address", "store_id", "image", "image_key"];

    protected $hidden = ["deleted_at"];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function employments()
    {
        return $this->hasMany(Employment::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function purchase_sheets()
    {
        return $this->hasMany(PurchaseSheet::class);
    }

    public function quantity_checking_sheets()
    {
        return $this->hasMany(QuantityCheckingSheet::class);
    }
}
