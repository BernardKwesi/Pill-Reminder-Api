<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosage extends Model
{
    use HasFactory;
    protected $table = "pill_reminders";

    protected $fillable = [
        "user_id","pill_name","start_date","end_date","dosage_times","dosage_frequency","dosage_interval","next_dosage_time"
    ];

    public function user()
    {
        return $this->belongsTo(User::class,"user_id",'id');
    }
}
