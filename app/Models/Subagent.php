<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Subagent extends Authenticatable
{
    use Notifiable;
    
    protected $guard = 'subagent';
    protected $appends = ['eno_file_path'];
    
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
        'agent_id',
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

    public function getEnoFilePathAttribute()
    {
        if($this->eno_file)
            return asset('/uploads/sub_agent_files/'.$this->eno_file);
        else
           return null;
    }
}
