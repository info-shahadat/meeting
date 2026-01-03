<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Meeting extends Model
{
    protected $fillable = ['title', 'room', 'host_id'];

    public static function generateRoom()
    {
        return 'meeting-' . Str::random(10);
    }
}
