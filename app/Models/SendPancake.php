<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendPancake extends Model
{
    protected $fillable = ['from_user_id', 'number', 'to_user_id', 'message'];

}
