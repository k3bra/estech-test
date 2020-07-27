<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'account_id',
        'external_reference',
        'delete_at',
    ];
}
