<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait  
class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsAllChanges;
    use LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'segment_id',
    ];

    // Customer.php

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function segment()
    { 
        return $this->belongsTo(Segment::class);
    }
    // BURAYI EKLEYİN:
    public function whatsappMessages()
    {
        return $this->hasMany(WhatsAppMessage::class);
    }
}