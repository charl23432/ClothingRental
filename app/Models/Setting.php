<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'store_name',
        'gcash_number',
        'contact_number',
        'email',
        'address',
        'logo',
    ];
}
