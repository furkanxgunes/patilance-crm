<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait

class SegmentServiceDiscount extends Model
{
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;
    
    protected $dates = ['deleted_at'];
    protected $fillable = ['segment_id', 'service_id', 'discount_percent'];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
