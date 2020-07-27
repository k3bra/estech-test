<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'product_id',
        'account_id',
        'user_id',
        'quantity',
        'value',
        'deleted_at'
    ];
}
