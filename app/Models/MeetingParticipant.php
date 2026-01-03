<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingParticipant extends Model
{
    protected $fillable = [
        'meeting_id',
        'email',
        'name',
        'joined_at',
        'left_at',
    ];
}
