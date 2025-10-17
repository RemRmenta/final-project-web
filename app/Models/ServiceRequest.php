<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id','assigned_to','title','description','address','photo','status','priority'
    ];

    public function resident() { return $this->belongsTo(User::class,'resident_id'); }
    public function assignedWorker() { return $this->belongsTo(User::class,'assigned_to'); }
}
