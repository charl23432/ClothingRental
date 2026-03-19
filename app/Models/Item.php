<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'quantity',    // AVAILABLE
        'rented',      // RENTED
        'rental_fee',
        'category',
        'image',
        'sizes',
    ];

    protected $casts = [
        'sizes' => 'array',
    ];

    // Relationship to reservations
    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class, 'product_id');
    }

    /**
     * Safety net: prevent negative inventory values
     */
    protected static function booted()
{
    static::saving(function ($item) {
        if ($item->rented < 0) {
            $item->rented = 0;
        }

        if ($item->quantity < 0) {
            $item->quantity = 0;
        }
    });
}


    /**
     * Helper: check if item is available
     */
    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }
}
