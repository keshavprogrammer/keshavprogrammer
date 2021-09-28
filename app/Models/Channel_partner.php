<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Channel_partner extends Authenticatable
{
    protected $guard = 'channel_partner';
    
    protected $fillable = [
        'photo',
        'name',
        'phone',
        'email',
        'password',        
        'address',        
        'city',
        'state',
        'zip',
        'country',
        'agenc_id',
        'contact_name',
        'contact_email',
        'contact_phone',        
        'status',
        'w9_file',
        'license_file',
        'eno_file',
    ];
    
    protected $hidden = [
        'password', 
        'remember_token',
    ];
}
