<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Channel_partner_user extends Authenticatable
{
	protected $guard = 'channel_partner_user';
	
    protected $fillable = [
        'photo',
        'name',
        'phone',
        'email',
        'password',
        'birth_date',
        'address',        
        'city',
        'state',
        'zip',
        'country',
        'agenc_id',
        'channel_partner_id',
        'join_date',
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
