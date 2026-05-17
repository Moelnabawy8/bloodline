<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BloodCar extends Model
{
    protected $fillable = [
        'hospital_id',
        'vehicle_name',
        'driver_name',
        'status',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
