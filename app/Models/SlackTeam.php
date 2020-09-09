<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackTeam extends Model
{
    protected $fillable = ['team_id', 'token', 'incoming_urls', 'incoming_channel', 'enterprise_id'];

    
}
