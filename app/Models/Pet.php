<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\LogsAllChanges; // kendi yazdığın trait      
class Pet extends Model
{
    
    use HasFactory, SoftDeletes, LogsAllChanges, LogsActivity;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'customer_id', 
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'weight_kg',
        'allergies',
        'veterinarian_info',
        'chip_no',
        'appearance',
        'special_marks',
        'habits_toilet',
        'vaccines',
        'medications_text',
        'breed_id',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }
}
