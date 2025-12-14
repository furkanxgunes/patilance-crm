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
    // WhatsAppMessage modeline ekleyin
public function smsLogs()
{
    return $this->hasMany(SmsLog::class, 'wa_message_id');
}

public function getHasSmsAttribute()
{
    return $this->smsLogs()->exists();
}

public function getSmsStatusAttribute()
{
    $latestLog = $this->smsLogs()->latest()->first();
    
    if (!$latestLog) {
        return [
            'status' => 'not_sent',
            'label' => 'SMS Gönderilmedi',
            'class' => 'bg-gray-100 text-gray-800'
        ];
    }

    $statuses = [
        'pending' => ['label' => 'Bekliyor', 'class' => 'bg-yellow-100 text-yellow-800'],
        'sent' => ['label' => 'Gönderildi', 'class' => 'bg-green-100 text-green-800'],
        'failed' => ['label' => 'Başarısız', 'class' => 'bg-red-100 text-red-800']
    ];

    return [
        'status' => $latestLog->status,
        'label' => $statuses[$latestLog->status]['label'] ?? 'Bilinmeyen',
        'class' => $statuses[$latestLog->status]['class'] ?? 'bg-gray-100 text-gray-800'
    ];
}
}