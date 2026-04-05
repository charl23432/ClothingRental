<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'size',
        'rent_time',
        'delivery',
        'gcash_reference',
        'price',
        'status',
    ];

    protected $casts = [
        'rent_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Item::class, 'product_id');
    }
}