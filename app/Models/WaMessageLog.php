<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessageLog extends Model
{
    protected $fillable = [
        'direction','wa_id','message_id','type','body','status','raw'
    ];

    protected $casts = [
        'raw' => 'array',
    ];
}
