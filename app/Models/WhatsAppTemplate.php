<?php

// app/Models/WhatsAppTemplate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait
class WhatsAppTemplate extends Model
{
    use SoftDeletes, LogsAllChanges, LogsActivity;
    protected $table = 'whatsapp_templates';

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'identifier',
        'category',
        'content',
        'variables',
        'is_active'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];
}