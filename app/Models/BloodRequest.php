<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodRequest extends Model
{
    protected $fillable = [
        'hospital_id',
        'blood_type',
        'location',
        'quantity_required',
        'urgency_level',
        'status',
        'patient_name',
        'patient_info',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
