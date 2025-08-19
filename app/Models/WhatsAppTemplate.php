<?php

// app/Models/WhatsAppTemplate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppTemplate extends Model
{
    use SoftDeletes;
    protected $table = 'whatsapp_templates';

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