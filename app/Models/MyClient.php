<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyClient extends Model
{
    protected $table = 'my_client';
    protected $fillable = [
        'name',
        'slug',
        'is_project',
        'self_capture',
        'client_prefix',
        'client_logo',
        'address',
        'phone_number',
        'city',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
