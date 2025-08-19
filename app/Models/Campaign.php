<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'discount_type',
        'discount_value',
        'max_uses',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The services that belong to the campaign.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Scope active campaigns
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }
}
