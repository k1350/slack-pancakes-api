<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackUser extends Model
{
    protected $fillable = ['slack_user_id', 'slack_user_name', 'team_id'];
}
