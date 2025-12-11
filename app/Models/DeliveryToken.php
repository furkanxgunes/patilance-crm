<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryToken extends Model
{
    protected $fillable = [
        'appointment_id',
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}