<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','role','profile_photo'
    ];

    protected $hidden = ['password'];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'resident_id');
    }

    public function assignedRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_to');
    }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isServiceWorker() { return $this->role === 'service_worker'; }
    public function isResident() { return $this->role === 'resident'; }
}
