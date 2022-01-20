<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'gender',
        'store_id',
        'birthday'

    ];

    protected $hidden = [
        'point',
        'code',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
