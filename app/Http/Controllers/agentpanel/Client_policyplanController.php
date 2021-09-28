<?php

namespace App\Http\Controllers\agentpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client_policyplan;
use App\Models\Agency;
use App\Models\Client;
use App\Models\Policytype;
use App\Models\Policyplan;
use App\Models\LineOfBusiness;
use App\Models\Company;
use App\Models\LeadSource;
use App\Models\Department;
use App\Models\InsuredInformationPl;
use App\Models\InsuredInformationCl;
use App\Models\ClientPolicy;
use App\Models\RatingState;
use App\Models\AdditionalInterests;
use Validator;
use Illuminate\Pagination\Paginator;

class Client_policyplanController extends Controller
{
    private $pagination = 20;

    public function manage($cid) {
    	$client = Client::where("id", "=",$cid)->first();

        $data = ClientPolicy::with(['department','additional','insuredPL','insuredCL','leadSource'])->where("client_id", "=", $cid)->paginate($this->pagination);
        return view('agentpanel.manageclientpolicy', compact('data', 'cid','client'));
    }
    
    public function add($cid) {
    	
    	$user = Auth::user();
    	$agency_id = $user->agenc_id;
    	
        $lineOfBusiness = LineOfBusiness::query()->pluck('name','id');
        $company = Company::query()->pluck('name','id');
        $additionalInterests = AdditionalInterests::query()->pluck('name','id');
        $ratingState = RatingState::query()->pluck('name','id');
        $insuredInformationPl = InsuredInformationPl::query()->pluck('name','id');
        $insuredInformationCl = InsuredInformationCl::query()->pluck('name','id');
        $leadSource = LeadSource::query()->pluck('name','id');
        $department = Department::query()->pluck('name','id');
        $policyStatus = array(
            'New Business'=>'New Business',
            'Policy Change'=>'Policy Change',
            'Reinstate'=>'Reinstate',
            'Rewrite'=>'Rewrite',
            'Renewal'=>'Renewal',
            'Cancel'=>'Cancel',
            'Active'=>'Active',
            'Expired'=>'Expired',
        );

        $billingType = array(
            'Agency'=>'Agency',
            'Direct'=>'Direct',
        );
        $originalAgentCode = array(
            'OPCA'=>'OPCA',
            'OPCB'=>'OPCB',
            'OPCC'=>'OPCC',
            'OPCD'=>'OPCD',
            'OPCE'=>'OPCE',
        );
        $overrideProducerCode = array(
            'OCCA'=>'OCCA',  
            'OCCB'=>'OCCB',
            'OCCC'=>'OCCC',
            'OCCD'=>'OCCD',
        );
        $referalPartnerCode = array(
            'RFPA'=>'RFPA',  
            'RFPB'=>'RFPB',
            'RFPC'=>'RFPC',
            'RFPD'=>'RFPD',
        );
        $serviceTeam = array(
            'No rule matches'=>'No rule matches',  
            'Override'=>'Override',
        );
        
        $data = array('type'=>'add', 'cid'=>$cid, 'agency_id'=>$agency_id, 'agent_id'=>Auth::id());
    	return view('agentpanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
        'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
        'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'));
    }
    public function search($cid,Request $request) {
        $input = $request->all();
    	$client = Client::where("id", "=",$cid)->first();

        $qry = ClientPolicy::with(['department','additional','insuredPL','insuredCL','leadSource'])->where("client_id", "=", $cid); 
        if(trim($input["search"])!="") {
            $search = $input["search"];
            $qry->where([
                ["policy_status", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["policy", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["billing_type", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["agency_code", "like", "%{$search}%"],
            ]);
        }
        $data = $qry->paginate($this->pagination);
        $data->appends($input);
        return view('agentpanel.manageclientpolicy', compact('data', 'cid','client'));
    }
    public function save(Request $request) {
        $input = $request->all();
        $update = array();
        $validator = Validator::make( $input, $this->getRules('Add', $input), $this->messages());
        if ($validator->fails()) {
        	
        	$user = Auth::user();
    		$agency_id = $user->agenc_id;
            $lineOfBusiness = LineOfBusiness::query()->pluck('name','id');
            $company = Company::query()->pluck('name','id');
            $additionalInterests = AdditionalInterests::query()->pluck('name','id');
            $ratingState = RatingState::query()->pluck('name','id');
            $insuredInformationPl = InsuredInformationPl::query()->pluck('name','id');
            $insuredInformationCl = InsuredInformationCl::query()->pluck('name','id');
            $leadSource = LeadSource::query()->pluck('name','id');
            $department = Department::query()->pluck('name','id');
            $policyStatus = array(
                'New Business'=>'New Business',
                'Policy Change'=>'Policy Change',
                'Reinstate'=>'Reinstate',
                'Rewrite'=>'Rewrite',
                'Renewal'=>'Renewal',
                'Cancel'=>'Cancel',
                'Active'=>'Active',
                'Expired'=>'Expired',
            );
    
            $billingType = array(
                'Agency'=>'Agency',
                'Direct'=>'Direct',
            );
            $originalAgentCode = array(
                'OPCA'=>'OPCA',
                'OPCB'=>'OPCB',
                'OPCC'=>'OPCC',
                'OPCD'=>'OPCD',
                'OPCE'=>'OPCE',
            );
            $overrideProducerCode = array(
                'OCCA'=>'OCCA',  
                'OCCB'=>'OCCB',
                'OCCC'=>'OCCC',
                'OCCD'=>'OCCD',
            );
            $referalPartnerCode = array(
                'RFPA'=>'RFPA',  
                'RFPB'=>'RFPB',
                'RFPC'=>'RFPC',
                'RFPD'=>'RFPD',
            );
            $serviceTeam = array(
                'No rule matches'=>'No rule matches',  
                'Override'=>'Override',
            );

            $data = array('type'=>'add', 'input'=>$input, 'cid'=>$input['client_id'], 'agency_id'=>$agency_id, 'agent_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('agentpanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
            'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
            'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'));
            exit();            
        }
        
        
        $CP = ClientPolicy::create($input);
        if($CP->id>0) {
            return redirect('/agentpanel/manageclientpolicy/'.$input['client_id'])->with('success', 'Created successfully.');
        } else {
            return redirect('/agentpanel/addclientpolicy/'.$input['client_id'])->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($cid, $id) {
    	
    	$user = Auth::user();
    	$agency_id = $user->agenc_id;
    	
        $lineOfBusiness = LineOfBusiness::query()->pluck('name','id');
        $company = Company::query()->pluck('name','id');
        $additionalInterests = AdditionalInterests::query()->pluck('name','id');
        $ratingState = RatingState::query()->pluck('name','id');
        $insuredInformationPl = InsuredInformationPl::query()->pluck('name','id');
        $insuredInformationCl = InsuredInformationCl::query()->pluck('name','id');
        $leadSource = LeadSource::query()->pluck('name','id');
        $department = Department::query()->pluck('name','id');
        $policyStatus = array(
            'New Business'=>'New Business',
            'Policy Change'=>'Policy Change',
            'Reinstate'=>'Reinstate',
            'Rewrite'=>'Rewrite',
            'Renewal'=>'Renewal',
            'Cancel'=>'Cancel',
            'Active'=>'Active',
            'Expired'=>'Expired',
        );

        $billingType = array(
            'Agency'=>'Agency',
            'Direct'=>'Direct',
        );
        $originalAgentCode = array(
            'OPCA'=>'OPCA',
            'OPCB'=>'OPCB',
            'OPCC'=>'OPCC',
            'OPCD'=>'OPCD',
            'OPCE'=>'OPCE',
        );
        $overrideProducerCode = array(
            'OCCA'=>'OCCA',  
            'OCCB'=>'OCCB',
            'OCCC'=>'OCCC',
            'OCCD'=>'OCCD',
        );
        $referalPartnerCode = array(
            'RFPA'=>'RFPA',  
            'RFPB'=>'RFPB',
            'RFPC'=>'RFPC',
            'RFPD'=>'RFPD',
        );
        $serviceTeam = array(
            'No rule matches'=>'No rule matches',  
            'Override'=>'Override',
        );
        
        $input = ClientPolicy::where('id', '=', $id)->get();
        $data = array('type'=>'edit', 'input'=>$input, 'agency_id'=>$agency_id, 'agent_id'=>Auth::id(), 'cid'=>$cid);
	    return view('agentpanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
        'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
        'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'));
	}
	
	public function update(Request $request) {
		$input = $request->all();
        $id = $input['id'];
        $update = array();        
        $validator = Validator::make( $input, $this->getRules('Edit', $input), $this->messages()); 
        
        if ($validator->fails()) {
        	
        	$user = Auth::user();
    		$agency_id = $user->agenc_id;
            $lineOfBusiness = LineOfBusiness::query()->pluck('name','id');
            $company = Company::query()->pluck('name','id');
            $additionalInterests = AdditionalInterests::query()->pluck('name','id');
            $ratingState = RatingState::query()->pluck('name','id');
            $insuredInformationPl = InsuredInformationPl::query()->pluck('name','id');
            $insuredInformationCl = InsuredInformationCl::query()->pluck('name','id');
            $leadSource = LeadSource::query()->pluck('name','id');
            $department = Department::query()->pluck('name','id');
            $policyStatus = array(
                'New Business'=>'New Business',
                'Policy Change'=>'Policy Change',
                'Reinstate'=>'Reinstate',
                'Rewrite'=>'Rewrite',
                'Renewal'=>'Renewal',
                'Cancel'=>'Cancel',
                'Active'=>'Active',
                'Expired'=>'Expired',
            );
    
            $billingType = array(
                'Agency'=>'Agency',
                'Direct'=>'Direct',
            );
            $originalAgentCode = array(
                'OPCA'=>'OPCA',
                'OPCB'=>'OPCB',
                'OPCC'=>'OPCC',
                'OPCD'=>'OPCD',
                'OPCE'=>'OPCE',
            );
            $overrideProducerCode = array(
                'OCCA'=>'OCCA',  
                'OCCB'=>'OCCB',
                'OCCC'=>'OCCC',
                'OCCD'=>'OCCD',
            );
            $referalPartnerCode = array(
                'RFPA'=>'RFPA',  
                'RFPB'=>'RFPB',
                'RFPC'=>'RFPC',
                'RFPD'=>'RFPD',
            );
            $serviceTeam = array(
                'No rule matches'=>'No rule matches',  
                'Override'=>'Override',
            );
                
            $data = array('type'=>'Edit', 'input'=>$input, 'cid'=>$input['client_id'], 'agency_id'=>$agency_id, 'agent_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('agentpanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
            'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
            'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'));
            exit();            
        }
        
       
        unset($input['_token']);
        
        $user = ClientPolicy::where('id', '=', $id)->update($input);
        return redirect('/agentpanel/manageclientpolicy/'.$input['client_id'])->with('success', 'Updated successfully.');

	}
    
    public function delete($cid,$id) {
        $client = ClientPolicy::where('id',$id)->delete();
        if ($client) 
            return redirect()->back()->with('success', 'Deleted successfully.');
        else
            return redirect()->back()->withError('Somthing went wrong');
    }
    
    public function ajaxGetPolicyByPolicytypeId(Request $request) {
    	$user = Auth::user();
    	$agenc_id = $user->agenc_id;
        $input = $request->all();
        $data = Policyplan::where("policy_type_id", "=",  $input['id'])->where("agenc_id", "=", $agenc_id)->pluck('title', 'id');
        return response()->json($data);
    }
    
    private function removeimage($imagepath, $id) {
        $user = Client_policyplan::where('id', '=', $id)->get();
        if($user[0]->logo!=null && $user[0]->logo!="") {
            if(file_exists($imagepath.'/'.$user[0]->logo)) {
                unlink($imagepath.'/'.$user[0]->logo);
            }
        }
        return true;
    }
    
    private function getRules($type, $input) {
        $return = array();
        $return['policy_status'] = 'required';
        $return['policy'] = 'required';
        $return['effective_date'] = 'required';
        $return['expiration_date'] = 'required';
        $return['master_company'] = 'required';
        $return['writing_company'] = 'required';
        $return['billing_type'] = 'required';
        $return['rating_state_id'] = 'required';
        $return['written_premium'] = 'required';
        $return['full_term_premium'] = 'required';
        $return['annual_premium'] = 'required';
        return $return;
    }

    private function messages() {
        return [
            'policy_type_id.required'  => $this->getRequiredMessage('select policy type'),
            'policy_id.required'  => $this->getRequiredMessage('select policy plan'),
            'premium_price.required'  => $this->getRequiredMessage('policy premium price'),
            'start_date.required'  => $this->getRequiredMessage('Policy start date'),
            'next_premium_date.required'  => $this->getRequiredMessage('Policy next premium date'),
            'holder_name.required'  => $this->getRequiredMessage('Policy holder name'),
            'holder_birth_date.required'  => $this->getRequiredMessage('Policy holder birthdate'),
        ];
    }

    private function getRequiredMessage($string) {
        return 'The ' . $string . ' field is required.';
    }

    private function getGreaterMessage($string, $max) {
        return 'The ' . $string . ' may not be greater than ' . $max . ' characters.';
    }
    
}
