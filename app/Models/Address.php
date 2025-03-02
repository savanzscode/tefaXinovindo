<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['name', 'phone', 'locality', 'address', 'city', 'state', 'country', 'landmark', 'zip', 'user_id'];
}
