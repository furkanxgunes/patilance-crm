<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory; // <-- 2. EKLEME

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const UNIT_DAY = 'day';
    public const UNIT_HOUR = 'hour';
    public const UNIT_SESSION = 'session';

    public static function getUnits()
    {
        return [
            self::UNIT_DAY => 'Gün',
            self::UNIT_HOUR => 'Saat',
            self::UNIT_SESSION => 'Seans',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'base_price',
        'discounted_price',
        'duration_minutes',
    ];
    
    protected $casts = [
        'base_price' => 'float',
        'discounted_price' => 'float',
        'duration_minutes' => 'integer',
    ];

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class);
    }


}