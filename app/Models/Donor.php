<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donor extends Model
{
    protected $fillable = [
        'user_id',
        'national_id',
        'age',
        'gender',
        'phone',
        'governorate',
        'city',
        'address',
        'latitude',
        'longitude',
        'blood_type',
        'weight',
        'points',
        'notifications_enabled',
    ];

    protected $casts = [
        'latitude'              => 'float',
        'longitude'             => 'float',
        'notifications_enabled' => 'boolean',
    ];

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function isEligible(): bool
    {
        return $this->age >= 18 && $this->age <= 65 && $this->weight >= 50;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
