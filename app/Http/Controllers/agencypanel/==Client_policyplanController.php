<?php

namespace App\Http\Controllers\agencypanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client_policyplan;
use App\Models\Agency;
use App\Models\Client;
use App\Models\LineOfBusiness;
use App\Models\Company;
use App\Models\LeadSource;
use App\Models\Department;
use App\Models\Policytype;
use App\Models\Policyplan;
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
        $data = ClientPolicy::with(['department','additional','insuredPL','insuredCL','leadSource'])->where("client_id", "=", $cid)->paginate($this->pagination);
        return view('agencypanel.manageclientpolicy', compact('data', 'cid'));
    }
    
    public function add($cid) {

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
    	$policytype = Policytype::pluck('policy_type_title', 'id');
        $policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('title', 'id');
    	$data = array('type'=>'add', 'cid'=>$cid, 'agenc_id'=>Auth::id(), 'policytype'=>$policytype, 'policyplan'=>$policyplan);
    	return view('agencypanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
            'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
            'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'
        ));
    }
    
    public function save(Request $request) {
        $input = $request->all();
        $update = array();
     
     
        $validator = Validator::make( $input, $this->getRules('Add', $input), $this->messages());
        if ($validator->fails()) { 
        	$policytype = Policytype::pluck('policy_type_title', 'id');
        	$policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('title', 'id');
            $data = array('type'=>'add', 'input'=>$input, 'cid'=>$input['client_id'], 'agenc_id'=>Auth::id(), 'policytype'=>$policytype, 'policyplan'=>$policyplan, 'error'=>$validator->messages());
            return view('agencypanel.addclientpolicy', compact('data'));
            exit();            
        }
        // $update["client_id"] = $input["client_id"];
        // $update["policy_type_id"] = $input["policy_type_id"];
        // $update["policy_id"] = $input["policy_id"];
        
        
        // $update["line_of_business_id"] = $input["line_of_business_id"];
        // $update["start_date"] = $input["start_date"];
        // $update["next_premium_date"] = $input["next_premium_date"];
        // $update["holder_name"] = $input["holder_name"];
        // $update["holder_birth_date"] = $input["holder_birth_date"];
        // $update["vehicle_no"] = $input["vehicle_no"];
        // $update["vehicle_engine_no"] = $input["vehicle_engine_no"];
        // $update["property_address"] = $input["property_address"];
        
        
        $input["agency_id"] = $input["agenc_id"];
        
        
        $CP = ClientPolicy::create($input);
        if($CP->id>0) {
            return redirect('/agencypanel/manageclientpolicy/'.$input['client_id'])->with('success', 'Created successfully.');
        } else {
            return redirect('/agencypanel/addclientpolicy/'.$input['client_id'])->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($cid, $id) {
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
        $input = ClientPolicy::with(['department','additional','insuredPL','insuredCL','leadSource'])->where("id", "=", $id)->paginate($this->pagination);

        $data = array('type'=>'edit','input'=>$input, 'cid'=>$cid, 'agenc_id'=>Auth::id());
    	return view('agencypanel.addclientpolicy', compact('data','overrideProducerCode','lineOfBusiness',
            'ratingState','company','originalAgentCode','policyStatus','billingType','referalPartnerCode',
            'leadSource','department','serviceTeam','insuredInformationPl','insuredInformationCl','additionalInterests'
        ));
        // $data = array('type'=>'edit', 'input'=>$input, 'agenc_id'=>Auth::id(), 'policytype'=>$policytype, 'policyplan'=>$policyplan, 'cid'=>$cid);
	    // return view('agencypanel.addclientpolicy', compact('data'));
	}
	
	public function update(Request $request) {
		$input = $request->all();
        $id = $input['id'];
        $update = array();        
        $validator = Validator::make( $input, $this->getRules('Edit', $input), $this->messages()); 
        
        if ($validator->fails()) {
        	$policytype = Policytype::pluck('policy_type_title', 'id');
        	$policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('title', 'id'); 
            $data = array('type'=>'Edit', 'input'=>$input, 'cid'=>$input['client_id'], 'agenc_id'=>Auth::id(), 'policytype'=>$policytype, 'policyplan'=>$policyplan, 'error'=>$validator->messages());
            return view('agencypanel.addclientpolicy', compact('data'));
            exit();            
        }
        
        $update["client_id"] = $input["client_id"];
        $update["policy_type_id"] = $input["policy_type_id"];
        $update["policy_id"] = $input["policy_id"];
        $update["premium_price"] = $input["premium_price"];
        $update["start_date"] = $input["start_date"];
        $update["next_premium_date"] = $input["next_premium_date"];
        $update["holder_name"] = $input["holder_name"];
        $update["holder_birth_date"] = $input["holder_birth_date"];
        $update["vehicle_no"] = $input["vehicle_no"];
        $update["vehicle_engine_no"] = $input["vehicle_engine_no"];
        $update["property_address"] = $input["property_address"];
        $update["agenc_id"] = $input["agenc_id"];
        
        if(isset($input["holder_id_proof_photo"])) {
        	
        	$upload_dir_path = public_path()."/uploads/holder_id_proof_photo";
        	$this->removeimage($upload_dir_path, $id);
        	
            $holder_id_proof_photo = $input["holder_id_proof_photo"];
            $filename = rand(0000,9999).$holder_id_proof_photo->getClientOriginalName();
            $upload_dir_path = public_path()."\uploads\holder_id_proof_photo";
            $holder_id_proof_photo->move($upload_dir_path, $filename );
            $update['holder_id_proof_photo'] = $filename;
        }
        
        if(isset($input["vehicle_photo"])) {
        	
        	$upload_dir_path = public_path()."/uploads/pvehicle_photo";
        	$this->removeimage($upload_dir_path, $id);
        	
            $vehicle_photo = $input["vehicle_photo"];
            $filename = rand(0000,9999).$vehicle_photo->getClientOriginalName();
            $upload_dir_path = public_path()."\uploads\pvehicle_photo";
            $vehicle_photo->move($upload_dir_path, $filename );
            $update['vehicle_photo'] = $filename;
        }
        
        if(isset($input["property_photo"])) {
        	
        	$upload_dir_path = public_path()."/uploads/property_photo";
        	$this->removeimage($upload_dir_path, $id);
        	
            $property_photo = $input["property_photo"];
            $filename = rand(0000,9999).$property_photo->getClientOriginalName();
            $upload_dir_path = public_path()."\uploads\property_photo";
            $property_photo->move($upload_dir_path, $filename );
            $update['property_photo'] = $filename;
        }
        
        if(isset($input["property_document_photo"])) {
        	
        	$upload_dir_path = public_path()."/uploads/property_document_photo";
        	$this->removeimage($upload_dir_path, $id);
        	
            $property_document_photo = $input["property_document_photo"];
            $filename = rand(0000,9999).$property_document_photo->getClientOriginalName();
            $upload_dir_path = public_path()."\uploads\property_document_photo";
            $property_document_photo->move($upload_dir_path, $filename );
            $update['property_document_photo'] = $filename;
        }
        
        $user = Client_policyplan::where('id', '=', $id)->update($update);
        return redirect('/agencypanel/manageclientpolicy/'.$input['client_id'])->with('success', 'Updated successfully.');

	}
    
    public function delete($id) {
        Client_policyplan::where('id','=',$id)->delete();
        return redirect('/agencypanel/manageclientpolicy/'.$input['client_id'])->with('success', 'Deleted successfully.');
    }
    
    public function ajaxGetPolicyByPolicytypeId(Request $request) {
        $input = $request->all();
        $data = Policyplan::where("policy_type_id", "=",  $input['id'])->where("agenc_id", "=", Auth::id())->pluck('title', 'id');
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
        $return['policy_type_id'] = 'required';
        $return['policy_id'] = 'required';
        $return['premium_price'] = 'required';
        $return['start_date'] = 'required';
        $return['next_premium_date'] = 'required';
        $return['holder_name'] = 'required';
        $return['holder_birth_date'] = 'required';
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
