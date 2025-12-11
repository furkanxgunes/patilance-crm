<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait  
class WhatsAppMessage extends Model
{
    use SoftDeletes, LogsAllChanges, LogsActivity;
    protected $table = 'whatsapp_messages';

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'customer_id',
        'appointment_id',
        'type',
        'payment_status_value', // Bu satırı ekleyin
        'content',
        'status',
        'metadata',
        'scheduled_at',
        'sent_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
        'payment_status_value' => 'boolean', // Boolean olarak cast etmek de faydalı olacaktır
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}