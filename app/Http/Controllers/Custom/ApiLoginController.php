<?php

namespace App\Http\Controllers\Custom;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiLoginController extends Controller
{
    //

    public function login(Request $request)
    {
       
       $user_data  =  $this->checkUserlogin($request->username,$request->password); 
       return json_encode($user_data); 

    }

    public function checkUserlogin($username,$password)
    {   
        
        $user = User::where('username',$username)->where('status','active')->first();

        if(isset($user)){

            if (Hash::check($password, $user->password)) {   

                $token = Str::random(60);
                $user->forceFill([
                    'api_token' => $token
                    //Hash::make($token) 
                    //hash('sha256', $token
                ,
                ])->save();

                return [
                        'token' => $token, 
                        'user_id'=>$user->id,
                        'surname'=>$user->surname,
                        'first_name'=>$user->first_name,
                        'last_name'=>$user->last_name,
                        'email'=>$user->email];                
            }
        }

        abort(403, 'Invalid Username or Password');
    }

    public function logout(Request $request)
    {   
        //return $request->all();
        return $this->userLogout($request->user_id, $request->api_token); 
       // return ($user_data); 
    }

    public function userLogout($user_id, $api_token)
    {

        $user = User::where('id',$user_id)->first();
        

        if(isset($user)){
                        
                if ($api_token == $user->api_token) { 
                $user->forceFill([
                    'api_token' => null,
                ])->save();
                
                 return  response()->json(['status' => 'DONE'], 200);

            }else{
                abort(403, 'Invalid API token');
            }
        }

        abort(403, 'Invalid user id');
    }


}
