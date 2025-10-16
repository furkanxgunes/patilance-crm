<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait
class Service extends Model
{
    use HasFactory; // <-- 2. EKLEME
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;

    protected $dates = ['deleted_at'];
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
    
    public function breeds()
    {
        return $this->belongsToMany(Breed::class, 'service_breed_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }
    
    public function segmentDiscounts()
    {
        return $this->belongsToMany(Segment::class, 'segment_service_discounts')
                    ->withPivot('discount_percent')
                    ->withTimestamps();
    }
    
    // get categoires but unique example(Tümü, Bakım, Konaklama)    
    public function getCategoriesAttribute()
    {
        return $this->categories()->pluck('name')->unique()->toArray();
    }
    
    public static function getCategories()
    {
        return static::select('category')->distinct()->pluck('category');
    }
}