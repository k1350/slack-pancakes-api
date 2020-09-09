<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalPancake extends Model
{
    protected $fillable = ['user_id', 'received', 'sent', 'used'];
}
