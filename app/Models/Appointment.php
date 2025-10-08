<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;


    protected $dates = ['deleted_at'];
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
        'send_notification_checkin' => 'boolean',
        'send_notification_checkout' => 'boolean',
        'payment_status' => 'boolean',
        'send_notification_payment_status' => 'boolean',
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
    public function extraItems()
    {
        return $this->hasMany(AppointmentExtraItem::class);
    }
    public function getTotalAmountAttribute(): float
    {
        $total = 0.0;

        // Her bir hizmet için discounted_price * quantity hesaplayıp topluyoruz
        foreach ($this->services as $service) {
            // Pivot tablodan discounted_price ve quantity'yi alıyoruz
            $discountedPrice = (float) $service->pivot->discounted_price;
            $quantity = (int) $service->pivot->quantity; // Quantity'nin integer olduğundan emin olun
    
            $total += ($discountedPrice * $quantity);
        }
       
        $appointment_extra_items_amount = $this->extraItems->sum('price') ?? 0;
        return (float) $total + $appointment_extra_items_amount;
    }

}

