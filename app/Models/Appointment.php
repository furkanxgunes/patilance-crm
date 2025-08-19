<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;

class Appointment extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => null, // Bunu observer üzerinden yöneteceğiz
        'updated' => null  // Bunu da observer üzerinden yöneteceğiz
    ];
    
    protected $guarded = [];

        protected $casts = [
        'status' => AppointmentStatus::class,
        'planned_at' => 'datetime',
        'planned_exit' => 'datetime',
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
        'send_notification' => 'boolean',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['unit_price', 'discounted_price', 'quantity', 'notes', 'user_id'])
            ->withTimestamps(); 
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

