<?php

namespace App\Http\Controllers\agencypanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Agency;
use Validator;
use Illuminate\Pagination\Paginator;

class ClientController extends Controller
{
    private $pagination = 20;

    public function manage() {
    	$data = Client::where("agenc_id", "=", Auth::id())->paginate($this->pagination);
        return view('agencypanel.manageclient', compact('data'));
    }
    
    public function search(Request $request) {
        $input = $request->all();
        $qry = Client::where("agenc_id", "=", Auth::id()); 
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
        return view('agencypanel.manageclient', compact('data'));
    }
    
    public function add() {
        
        $data = array('type'=>'add', 'agenc_id'=>Auth::id());        
        return view('agencypanel.addclient', compact('data'));
    }
    
    public function save(Request $request) {
        $input = $request->all();
       
        $validator = Validator::make( $input, $this->getRules('Add', $input), $this->messages());
        if ($validator->fails()) { 
            $data = array('type'=>'add', 'input'=>$input, 'agenc_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('agencypanel.addclient', compact('data'));
            exit();            
        }
        
        $agency = Client::create($input);
        if($agency->id>0) {
            return redirect()->route('agencypanel.client.manage')->with('success', 'Created successfully.');
        } else {
            return redirect()->route('agencypanel.client.add')->withErrors(['Error creating record. Please try again.']);
        }
    }
    
    public function edit($id) {
        
        $input = Client::where('id', '=', $id)->get();
        $data = array('type'=>'edit', 'input'=>$input, 'agenc_id'=>Auth::id());
	    return view('agencypanel.addclient', compact('data'));
	}
	
	public function update(Request $request) {
		$input = $request->all();
        $id = $input['id'];
        $validator = Validator::make( $input, $this->getRules('Edit', $input), $this->messages()); 
        
        if ($validator->fails()) { 
            
            $data = array('type'=>'Edit', 'input'=>$input, 'agenc_id'=>Auth::id(), 'error'=>$validator->messages());
            return view('agencypanel.addclient', compact('data'));
            exit();            
        }
        $update = array();
        $update["fname"] = $input['fname'];
        $update["lname"] = $input['lname'];
        $update["email"] = $input['email'];
        $update["phone"] = $input['phone'];
        $update["address"] = $input['address'];
        $update["city"] = $input['city'];
        $update["state"] = $input['state'];
        $update["zip"] = $input['zip'];
        $update["country"] = $input['country'];
        $update["birth_date"] = $input['birth_date'];        
        $update["agenc_id"] = $input['agenc_id'];
        
        $agent = Client::where('id', '=', $id)->update($update);
        return redirect()->route('agencypanel.client.manage')->with('success', 'Updated successfully.');

	}
	
	public function delete($id) {
        
        Client::where('id','=',$id)->delete();
        return redirect()->route('agencypanel.client.manage')->with('success', 'Deleted successfully.');
    }
    
    private function getRules($type, $input) {
        $return = array();
        $return['fname'] = 'required|max:30';        
        $return['lname'] = 'required|max:30';        
        $return['phone'] = 'required|max:20';
        $return['address'] = 'required|max:200';
        $return['city'] = 'required|max:50';
        $return['state'] = 'required|max:50';
        $return['zip'] = 'required|max:10';
        $return['country'] = 'required|max:50';        
        $return['birth_date'] = 'required';        
        $return['agenc_id'] = 'required';
        if($type=="Edit") {
            $return['email'] = 'required|email|max:100';            
            
        } else {
            $return['email'] = 'required|email|max:100';                        
        }
        return $return;
    }

    private function messages() {
        return [
            'fname.required'  => $this->getRequiredMessage('first name'),
            'fname.max'  => $this->getGreaterMessage('first name', 30),
            'lname.required'  => $this->getRequiredMessage('last name'),
            'lname.max'  => $this->getGreaterMessage('last name', 30),            
            'phone.required'  => $this->getRequiredMessage('phone no.'),
            'phone.max'  => $this->getGreaterMessage('phone no.', 20),
            'address.required'  => $this->getRequiredMessage('address'),
            'address.max'  => $this->getGreaterMessage('address', 200),
            'agenc_id.required'  => $this->getRequiredMessage('agency'),
            'birth_date.required'  => $this->getRequiredMessage('birth date'),
        ];
    }

    private function getRequiredMessage($string) {
        return 'The ' . $string . ' field is required.';
    }

    private function getGreaterMessage($string, $maxchar) {
        return 'The ' . $string . ' may not be greater than ' . $maxchar . ' characters.';
    }
    
    
}
