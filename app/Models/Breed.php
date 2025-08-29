<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait

class Breed extends Model
{
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;
    
    protected $dates = ['deleted_at'];
    protected $fillable = ['name'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_breed_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }
}
