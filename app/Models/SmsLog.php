<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'wa_message_id',
        'phone',
        'message',
        'status',
        'response',
        'error_message',
        'sent_at',
        'failed_at'
    ];

    protected $casts = [
        'response' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function waMessage(): BelongsTo
    {
        return $this->belongsTo(WaMessageLog::class, 'wa_message_id');
    }
}
