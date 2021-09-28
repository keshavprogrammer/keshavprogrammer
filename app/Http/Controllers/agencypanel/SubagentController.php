<?php

namespace App\Http\Controllers\agencypanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subagent;
use App\Models\Agent;
use App\Models\Agency;
use App\Models\Sms_historie;
use Twilio\Rest\Client;
use Validator;
use Illuminate\Pagination\Paginator;

class SubagentController extends Controller
{
    private $pagination = 20;

    public function manage() {
        $data = Subagent::where("agenc_id", "=", Auth::id())->paginate($this->pagination);
        return view('agencypanel.managesubagent', compact('data'));
    }
    
    public function subagentsinglesms(Request $request) {
        $input = $request->all();
        $agntId = $input['receiverid'];
        $message = $input['msg'];

        $account_sid = env("TWILIO_SID");
        $auth_token = env("TWILIO_AUTH_TOKEN");
        $twilio_number = env("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);

        $agentData = Subagent::query()->where('id', '=', $agntId)->get();
        $phone_number = $agentData[0]->phone;

        $sentResponse = $client->messages->create($phone_number, 
            ['from' => $twilio_number, 'body' => $message] );
        
        if($sentResponse->sid != ''){
            $sms['sender_id'] = Auth::id();
            $sms['receiver_id'] = $agntId;
            $sms['sender_type'] = 'Agency';
            $sms['receiver_type'] = 'Subagent';
            $sms['sms_type'] = 'Single';
            $sms['sms_content'] = $message;

            $agency = Sms_historie::create($sms);

            return redirect()->route('agencypanel.subagent.manage')->with('success', 'SMS sent successfully.');
        }
        else{
            return redirect()->route('agencypanel.subagent.manage')->withErrors(['SMS is not sent']);
        }
    }

    public function search(Request $request) {
        $input = $request->all();
        $qry = Subagent::query(); 
        if(trim($input["search"])!="") {
            $search = $input["search"];
            $qry->where([
                ["name", "like", "%{$search}%"],
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
        return view('agencypanel.manageagency', compact('data'));
    }
    
    public function add() {
        
        $agent = Agent::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('name', 'id');     
        $data = array('type'=>'add', 'agenc_id'=>Auth::id(), 'agent'=>$agent);        
        return view('agencypanel.addsubagent', compact('data'));
    }
    
    public function save(Request $request) {
        $input = $request->all();
       
        $validator = Validator::make( $input, $this->getRules('Add', $input), $this->messages());
        if ($validator->fails()) { 
            
            $agent = Agent::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('name', 'id');
            $data = array('type'=>'add', 'input'=>$input, 'agenc_id'=>Auth::id(), 'agent'=>$agent, 'error'=>$validator->messages());
            return view('agencypanel.addsubagent', compact('data'));
            exit();            
        }
        $input['password'] = bcrypt($input['password']);
        if(isset($input["photo"])) {
            $photo = $input["photo"];
            $filename = rand(0000,9999).$photo->getClientOriginalName();
            $upload_dir_path = public_path()."\uploads\subagent_logo";
            $photo->move($upload_dir_path, $filename );
            $input['photo'] = $filename;
        }
        if(isset($input["w9_file"])) {
            $w9_file = $input["w9_file"];
            $filename = 'w9_file_'.rand(0000,9999).$w9_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $w9_file->move($upload_dir_path, $filename );
            $input['w9_file'] = $filename;
        }
        if(isset($input["license_file"])) {
            $license_file = $input["license_file"];
            $filename = 'license_file_'.rand(0000,9999).$license_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $license_file->move($upload_dir_path, $filename );
            $input['license_file'] = $filename;
        }
        if(isset($input["eno_file"])) {
            $eno_file = $input["eno_file"];
            $filename = 'eno_file_'.rand(0000,9999).$eno_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $eno_file->move($upload_dir_path, $filename );
            $input['eno_file'] = $filename;
        }
        
        $agency = Subagent::create($input);
        if($agency->id>0) {
            return redirect()->route('agencypanel.subagent.manage')->with('success', 'Created successfully.');
        } else {
            return redirect()->route('agencypanel.subagent.add')->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($id) {
        
        $agent = Agent::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('name', 'id');
        $input = Subagent::where('id', '=', $id)->get();
        $data = array('type'=>'edit', 'input'=>$input, 'agenc_id'=>Auth::id(), 'agent'=>$agent);
        
	    return view('agencypanel.addsubagent', compact('data'));
	}
    
    public function update(Request $request) {
	    $input = $request->all();
        $id = $input['id'];
        $validator = Validator::make( $input, $this->getRules('Edit', $input), $this->messages()); 
        
        if ($validator->fails()) { 
            
            $agent = Agent::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('name', 'id');
            $data = array('type'=>'Edit', 'input'=>$input, 'agenc_id'=>Auth::id(), 'agent'=>$agent, 'error'=>$validator->messages());
            return view('agencypanel.addsubagent', compact('data'));
            exit();            
        }
        $update = array();
        $update["name"] = $input['name'];
        $update["email"] = $input['email'];
        if($input['password']!="") {
            $update["password"] = bcrypt($input['password']);
        }
        $update["phone"] = $input['phone'];
        $update["address"] = $input['address'];
        $update["city"] = $input['city'];
        $update["state"] = $input['state'];
        $update["zip"] = $input['zip'];
        $update["country"] = $input['country'];
        $update["birth_date"] = $input['birth_date'];
        $update["join_date"] = $input['join_date'];
        $update["agenc_id"] = $input['agenc_id'];
        $update["agent_id"] = $input['agent_id'];
        $update["status"] = $input['status'];
        if(isset($input["photo"])) 
        {
        	$upload_dir_path = public_path()."/uploads/subagent_logo";
        	$this->removeimage($upload_dir_path, $id);
        	
            $photo = $input["photo"];
            $filename = rand(0000,9999).$photo->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/subagent_logo";
            $photo->move($upload_dir_path, $filename );
            $update['photo'] = $filename;
        }
        if(isset($input["w9_file"])) {
            $w9_file = $input["w9_file"];
            $filename = 'w9_file_'.rand(0000,9999).$w9_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $w9_file->move($upload_dir_path, $filename );
            $update['w9_file'] = $filename;
        }
        if(isset($input["license_file"])) {
            $license_file = $input["license_file"];
            $filename = 'license_file_'.rand(0000,9999).$license_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $license_file->move($upload_dir_path, $filename );
            $update['license_file'] = $filename;
        }
        if(isset($input["eno_file"])) {
            $eno_file = $input["eno_file"];
            $filename = 'eno_file_'.rand(0000,9999).$eno_file->getClientOriginalName();
            $upload_dir_path = public_path()."/uploads/sub_agent_files";
            $eno_file->move($upload_dir_path, $filename );
            $update['eno_file'] = $filename;
        }
        $agent = Subagent::where('id', '=', $id)->update($update);
        return redirect()->route('agencypanel.subagent.manage')->with('success', 'Updated successfully.');

	}
    
    public function delete($id) {
        $upload_dir_path = public_path()."/uploads/subagent_logo";
        $this->removeimage($upload_dir_path, $id);
        
        Subagent::where('id','=',$id)->delete();
        
        
        
        return redirect()->route('agencypanel.subagent.manage')->with('success', 'Deleted successfully.');
    }
    
   
    
    private function removeimage($imagepath, $id) {
        $user = Subagent::where('id', '=', $id)->get();
        if($user[0]->logo!=null && $user[0]->logo!="") {
            if(file_exists($imagepath.'/'.$user[0]->logo)) {
                unlink($imagepath.'/'.$user[0]->logo);
            }
        }
        return true;
    }
    
    private function getRules($type, $input) {
        $return = array();
        $return['name'] = 'required|max:30';        
        $return['phone'] = 'required|max:20';
        $return['address'] = 'required|max:200';
        $return['city'] = 'required|max:50';
        $return['state'] = 'required|max:50';
        $return['zip'] = 'required|max:10';
        $return['country'] = 'required|max:50';        
        $return['birth_date'] = 'required';
        $return['join_date'] = 'required';
        $return['agenc_id'] = 'required';
        $return['agent_id'] = 'required';
        if($type=="Edit") {
            $return['email'] = 'required|email|max:100';            
            if($input["password"]!="") {
                $return['password'] = 'min:6|max:20';
            }
        } else {
            $return['email'] = 'required|email|unique:agencies,email|max:100';            
            $return['password'] = 'required|min:6|max:20';
        }
        return $return;
    }

    private function messages() {
        return [
            'name.required'  => $this->getRequiredMessage('first name'),
            'name.max'  => $this->getGreaterMessage('first name', 30),            
            'phone.required'  => $this->getRequiredMessage('phone no.'),
            'phone.max'  => $this->getGreaterMessage('phone no.', 20),
            'address.required'  => $this->getRequiredMessage('address'),
            'address.max'  => $this->getGreaterMessage('address', 200),
            'agenc_id.required'  => $this->getRequiredMessage('agency'),
            'agent_id.required'  => $this->getRequiredMessage('agent'),
            'birth_date.required'  => $this->getRequiredMessage('birth date'),
            'join_date.required'  => $this->getRequiredMessage('join date'),
        ];
    }

    private function getRequiredMessage($string) {
        return 'The ' . $string . ' field is required.';
    }

    private function getGreaterMessage($string, $maxchar) {
        return 'The ' . $string . ' may not be greater than ' . $maxchar . ' characters.';
    }
    
}
