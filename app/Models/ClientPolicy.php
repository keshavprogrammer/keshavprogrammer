<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ClientPolicy extends Model
{
    // protected $table= 'additional_interests';
    
    protected $fillable = [
        'client_id',
        'agent_id',
        'agency_id',    
        'line_of_business_id',
        'policy_status',
        'policy',
        'effective_date',
        'expiration_date',
        'master_company',
        'writing_company',
        'billing_type',
        'rating_state_id',
        'original_agent_code',
        'override_agent_code',
        'agency_code',
        'referral_partner_code',
        'lead_source_id',
        'written_premium',
        'estimated_fees',
        'estimated_taxes',
        'full_term_premium',
        'annual_premium',
        'total_commission',
        'department_id',
        'service_team',
        'insured_information_pl_id',
        'insured_information_cl_id',
        'additional_interests_id',
        'description',
    ];
    
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }
    public function additional()
    {
        return $this->belongsTo(AdditionalInterests::class,'additional_interests_id','id');
    }
    public function insuredPL()
    {
        return $this->belongsTo(InsuredInformationPl::class,'insured_information_pl_id','id');
    }
    public function insuredCL()
    {
        return $this->belongsTo(InsuredInformationCl::class,'insured_information_cl_id','id');
    }
    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class,'lead_source_id','id');
    }
}
