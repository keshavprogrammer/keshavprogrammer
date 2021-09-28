<?php

namespace App\Http\Controllers\partneruserpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Sms_historie;
use Twilio\Rest\Client;
use App\Models\Policyplan;

use Validator;
use Illuminate\Pagination\Paginator;

class LeadController extends Controller
{
    private $pagination = 20;

    public function manage() {
    	$data = Lead::where("channel_partner_user_id", "=", Auth::id())->where("mark_opportunity", "=", "0")->paginate($this->pagination);
        return view('partneruserpanel.managelead', compact('data'));
    }
    
    public function search(Request $request) {
        $input = $request->all();
        $qry = Lead::query(); 
        if(trim($input["search"])!="") {
            $search = $input["search"];
            $qry->where([
                ["fname", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["lname", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["email", "like", "%{$search}%"],
            ]);
            $qry->orwhere([
                ["phone", "like", "%{$search}%"],
            ]);
        }
        $data = $qry->paginate($this->pagination);
        $data->appends($input);
        return view('partneruserpanel.managelead', compact('data'));
    }
    
    public function add() {
    	
    	$user = Auth::user();
    	$agenc_id = $user->agenc_id;
    	$channel_partner_id = $user->channel_partner_id;
    	
        $policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", $agenc_id)->pluck('title', 'id');
        $data = array('type'=>'add', 'agenc_id'=>$agenc_id, 'channel_partner_id'=>$channel_partner_id,'channel_partner_user_id'=>Auth::id(), 'policyplan'=>$policyplan);        
        return view('partneruserpanel.addlead', compact('data'));
    }
    
    public function save(Request $request) {
        $input = $request->all();
        
        if(isset($input["policyplan_id"])) {
            $input["policyplan_id"] = implode(',', $input["policyplan_id"]);
        } else {
            $input["policyplan_id"] = "";
        }
        $validator = Validator::make( $input, $this->getRules('Add', $input), $this->messages());
        if ($validator->fails()) {
        	
        	$user = Auth::user();
    		$agenc_id = $user->agenc_id;
        	$channel_partner_id = $user->channel_partner_id;
        	
        	$policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", $agenc_id)->pluck('title', 'id'); 
            $data = array('type'=>'add', 'input'=>$input, 'agenc_id'=>$agenc_id, 'channel_partner_id'=>$channel_partner_id, 'channel_partner_user_id'=>Auth::id(), 'policyplan'=>$policyplan, 'error'=>$validator->messages());
            return view('partneruserpanel.addlead', compact('data'));
            exit();            
        }
        
        
        
        $agency = Lead::create($input);
        
        if($agency->id>0) {
            return redirect()->route('partneruserpanel.lead.manage')->with('success', 'Created successfully.');
        } else {
            return redirect()->route('partneruserpanel.lead.add')->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($id) {
        
        $user = Auth::user();
        $agenc_id = $user->agenc_id;
        $channel_partner_id = $user->channel_partner_id;
    	
        $input = Lead::where('id', '=', $id)->get();
        $policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", $agenc_id)->pluck('title', 'id');
        $data = array('type'=>'edit', 'input'=>$input, 'agenc_id'=>$agenc_id, 'channel_partner_id'=>$channel_partner_id,'channel_partner_user_id'=>Auth::id(),'policyplan'=>$policyplan);
	    return view('partneruserpanel.addlead', compact('data'));
	}
    
    public function update(Request $request) {
		$input = $request->all();
		
		$id = $input['id'];
        $update = array();
        if(isset($input["policyplan_id"])) {
            $update["policyplan_id"] = implode(',', $input["policyplan_id"]);
            $input["policyplan_id"] = implode(',', $input["policyplan_id"]);
        } else {
            $update["policyplan_id"] = "";
            $input["policyplan_id"] = "";
        }
        
        $validator = Validator::make( $input, $this->getRules('Edit', $input), $this->messages()); 
        
        if ($validator->fails()) { 
        
        	$user = Auth::user();
    		$agenc_id = $user->agenc_id;
            $channel_partner_id = $user->channel_partner_id;
            
            $policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", $agenc_id)->pluck('title', 'id');
            $data = array('type'=>'Edit', 'input'=>$input,'policyplan'=>$policyplan, 'agenc_id'=>$agenc_id, 'channel_partner_id'=>$channel_partner_id, 'channel_partner_user_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('partneruserpanel.addlead', compact('data'));
            exit();            
        }
        
        $update["fname"] = $input['fname'];
        $update["lname"] = $input['lname'];
        $update["email"] = $input['email'];
        $update["phone"] = $input['phone'];
        $update["notes"] = $input['notes'];
        $update["agenc_id"] = $input['agenc_id'];        
        $update["channel_partner_id"] = $input['channel_partner_id'];
        $update["channel_partner_user_id"] = $input['channel_partner_user_id'];
        //$update["mark_opportunity"] = $input['mark_opportunity'];
        
        $lead = Lead::where('id', '=', $id)->update($update);
        print_r($lead);
        return redirect()->route('partneruserpanel.lead.manage')->with('success', 'Updated successfully.');

	}
	
	public function delete($id) {
        Lead::where('id','=',$id)->delete();
        return redirect()->route('partneruserpanel.lead.manage')->with('success', 'Deleted successfully.');
    }
    
    public function leadsinglesms(Request $request) {
        $input = $request->all();
        $leadId = $input['receiverid'];
        $message = $input['msg'];

        $account_sid = env("TWILIO_SID");
        $auth_token = env("TWILIO_AUTH_TOKEN");
        $twilio_number = env("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);

        $leadData = Lead::query()->where('id', '=', $leadId)->get();
        $phone_number = $leadData[0]->phone;

        $sentResponse = $client->messages->create($phone_number, 
            ['from' => $twilio_number, 'body' => $message] );
        
        if($sentResponse->sid != ''){
            $sms['sender_id'] = Auth::id();
            $sms['receiver_id'] = $agntId;
            $sms['sender_type'] = 'Admin';
            $sms['receiver_type'] = 'Agent';
            $sms['sms_type'] = 'Single';
            $sms['sms_content'] = $message;

            $agency = Sms_historie::create($sms);

            return redirect()->route('partneruserpanel.lead.manage')->with('success', 'SMS sent successfully.');
        }
        else{
            return redirect()->route('partneruserpanel.lead.manage')->withErrors(['SMS is not sent']);
        }
    } 
    
    private function getRules($type, $input) {
        $return = array();
        $return['fname'] = 'required|max:30';
        $return['lname'] = 'required|max:30';        
        $return['phone'] = 'required|max:20';        
        if($type=="Edit") {
            $return['email'] = 'required|email|max:100';                        
        } else {
            $return['email'] = 'required|email|unique:agencies,email|max:100';                        
        }
        return $return;
    }

    private function messages() {
        return [
            'fname.required'  => $this->getRequiredMessage('first name'),
            'fname.max'  => $this->getGreaterMessage('first name', 30),
            'lname.required'  => $this->getRequiredMessage('last name'),
            'lname.max'  => $this->getGreaterMessage('last name', 30),            
            'email.required'  => $this->getRequiredMessage('email'),
            'email.max'  => $this->getGreaterMessage('email', 100),
            'phone.required'  => $this->getRequiredMessage('phone no.'),
            'phone.max'  => $this->getGreaterMessage('phone no.', 20),
        ];
    }

    private function getRequiredMessage($string) {
        return 'The ' . $string . ' field is required.';
    }

    private function getGreaterMessage($string, $maxchar) {
        return 'The ' . $string . ' may not be greater than ' . $maxchar . ' characters.';
    }
    
    
}
