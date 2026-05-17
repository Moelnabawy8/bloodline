<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'city',
        'event_date',
        'start_time',
        'end_time',
        'organizer',
        'contact_phone',
        'image',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
