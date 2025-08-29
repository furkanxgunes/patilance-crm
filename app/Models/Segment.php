<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait
class Segment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'icon',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'segment_service_discounts') // pivot tablosu segment_service_discounts olacak
                    ->withPivot('discount_percent')
                    ->withTimestamps();
    }
    

    public function serviceDiscounts()
{
    return $this->hasMany(SegmentServiceDiscount::class);
}

}
