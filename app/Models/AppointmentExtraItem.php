<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait

class AppointmentExtraItem extends Model
{
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;
    
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'appointment_id',
        'name',
        'price',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

   
}
