<?php

namespace App\Http\Controllers\agencypanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Subagent;
use App\Models\Agent;
use App\Models\Policyplan;
use App\Models\LineOfBusiness;
use Validator;
use Illuminate\Pagination\Paginator;

class LeadController extends Controller
{
    private $pagination = 20;

    public function manage() {
    	$data = Lead::where("agenc_id", "=", Auth::id())->where("mark_opportunity", "=", "0")->paginate($this->pagination);
        return view('agencypanel.managelead', compact('data'));
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
        return view('agencypanel.managelead', compact('data'));
    }
    
    public function add() {
        $policyType = LineOfBusiness::query()->pluck('name','id');
        
        $leadStatus = array(
            'New Business'=>'New Business',
            'Policy Change'=>'Policy Change',
            'Reinstate'=>'Reinstate',
            'Rewrite'=>'Rewrite',
            'Renewal'=>'Renewal',
            'Cancel'=>'Cancel',
            'Active'=>'Active',
            'Expired'=>'Expired',
        );
        $leadOwner = array('Agent'=>'Agent','Sub Agent'=>'Sub Agent');
        $agent = Agent::where('agenc_id',Auth::id())->pluck('name', 'id');
        $subAgents = array();
        $data = array('type'=>'add', 'agenc_id'=>Auth::id(), 'policyType'=>$policyType,'leadStatus'=>$leadStatus,'leadOwner'=>$leadOwner,'agents'=>$agent,'subAgents' => $subAgents);        
        return view('agencypanel.addlead', compact('data'));
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
        	$policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('title', 'id'); 
            $data = array('type'=>'add', 'input'=>$input, 'agenc_id'=>Auth::id(), 'policyplan'=>$policyplan, 'error'=>$validator->messages());
            return view('agencypanel.addlead', compact('data'));
            exit();            
        }
        
        
        $agency = Lead::create($input);
        if($agency->id>0) {
            return redirect()->route('agencypanel.lead.manage')->with('success', 'Created successfully.');
        } else {
            return redirect()->route('agencypanel.lead.add')->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($id) {
        $policyType = LineOfBusiness::query()->pluck('name','id');
        
        $leadStatus = array(
            'New Business'=>'New Business',
            'Policy Change'=>'Policy Change',
            'Reinstate'=>'Reinstate',
            'Rewrite'=>'Rewrite',
            'Renewal'=>'Renewal',
            'Cancel'=>'Cancel',
            'Active'=>'Active',
            'Expired'=>'Expired',
        );
        $input = Lead::where('id', '=', $id)->get();
        $agent = Agent::where('agenc_id',Auth::id())->pluck('name', 'id');
        $subAgents = Subagent::where('agent_id',$input[0]->agent_id)->pluck('name', 'id');
        $data = array('type'=>'edit', 'input'=>$input, 'agenc_id'=>Auth::id(),'policyType'=>$policyType,'leadStatus'=>$leadStatus,'agents'=>$agent,'subAgents'=>$subAgents);
	    return view('agencypanel.addlead', compact('data'));
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
            $policyplan = Policyplan::where("status", "=", "1")->where("agenc_id", "=", Auth::id())->pluck('title', 'id');
            $data = array('type'=>'Edit', 'input'=>$input,'policyplan'=>$policyplan, 'agenc_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('agencypanel.addlead', compact('data'));
            exit();            
        }
        
        $update["fname"] = $input['fname'];
        $update["lname"] = $input['lname'];
        $update["email"] = $input['email'];
        $update["phone"] = $input['phone'];
        $update["notes"] = $input['notes'];
        $update["agenc_id"] = $input['agenc_id'];
        $update["agent_id"] = $input['agent_id'];
        $update["subagent_id"] = $input['subagent_id'];
        $update["mark_opportunity"] = $input['mark_opportunity']??0;
        
        $lead = Lead::where('id', '=', $id)->update($update);
        return redirect()->route('agencypanel.lead.manage')->with('success', 'Updated successfully.');

	}
	


	public function delete($id) {
        
        Lead::where('id','=',$id)->delete();
        
        
        
        return redirect()->route('agencypanel.lead.manage')->with('success', 'Deleted successfully.');
    }
    
    public function ajaxSubAgentLoader(Request $request)
    {
        $sagent = Subagent::where('agent_id',$request->id)->get();
        $html = '';
        foreach ($sagent as $key => $value) {
            $html .= "<option value='$value->id'>$value->name</option>";
        }
        return $html;
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
