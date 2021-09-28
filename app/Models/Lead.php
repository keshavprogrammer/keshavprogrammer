<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Lead extends Model
{
	use Notifiable;
    
    protected $guard = 'lead';
	
    protected $fillable = [
        'fname',
        'lname',
        'email',        
        'phone',
        'policyplan_id',        
        'mark_opportunity',
        'mark_client',
        'notes',
        'agenc_id',
        'agent_id',
        'subagent_id',
        'channel_partner_id',      
        'insured_name',  
        'lead_status',  
        'policy_type',  
        'xdate',  
        'referal_partner',  
        'lead_owner',  
    ];
}
