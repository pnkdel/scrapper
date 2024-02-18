<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
class AuthController extends Controller
{
    
    public function login($code) {

        if($code == 0) {
            return response()->json([
                'message' => 'Access denied',
                'error_code' => 0,
                'error_type' => 'User authentication required'
            ]);
        } elseif ($code == 1) {
            return response()->json([
                'message' => 'Token authentication failure',
                'error_code' => 1,
                'error_type' => 'User authentication failed'
             ]); 
        }
    }


    public function register(Request $request) {
        $name =  $request->input("userName");
        $email =  $request->input("userEmail");
        $password =  $request->input("userPassword");
        $md5_password = md5($password);
        $apiToken = Str::random(60);
        $user = new User;
        $user->name =  $name ;
        $user->email = $email;
        $user->password = $md5_password;
        $user->api_token = $apiToken;
        $user->api_token = $apiToken;
        $user->created_at = date("Y-m-d H:i:s");
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        return response()->json([
            'message' => 'User created successfully',
            'access_token' => $apiToken,
            'token_type' => 'Bearer',
            'name' => $name,
            'email' => $email
        ]);
    }


    public function demouser() {
        $name =  "Test User";
        $email =  "pnkdel@gmail.com";
        $password = "jhon!@#von10" ;
        $md5_password = md5($password);
        $apiToken = "HYKQZAd29AIaq0xpfcAq1pmWNgbgXlFxUNnA4VjA0LAUTblsFXthpAfXQI6O";
        $user = new User;
        $user->name =  $name ;
        $user->email = $email;
        $user->password = $md5_password;
        $user->api_token = $apiToken;
        $user->api_token = $apiToken;
        $user->created_at = date("Y-m-d H:i:s");
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        return response()->json([
            'message' => 'User created successfully',
            'access_token' => $apiToken,
            'token_type' => 'Bearer',
            'name' => $name,
            'email' => $email
        ]);
    }

   
    
    public function authorization(Request $request)    {
 
        //$email =  "pnkdel@gmail.com" ; 
        //$password = "jhon!@#von10" ;
        $email =  $request->input("userEmail");
        $password =  $request->input("userPassword");

        $md5_password = md5($password);
        $apiToken = Str::random(60);

        $userDetails =  $this->verifyUser($email, $md5_password);

    
       if(!isset($userDetails[0]->email)) {

         return response()->json([
            'message' => 'Access denied',
            'error_code' => 0,
            'error_type' => 'User authentication required'
        ]);
         
        } else {
          
            return response()->json([
                'access_token' => $userDetails[0]->api_token,
                'token_type' => 'Bearer',
                'name' => $userDetails[0]->name,
                'email' => $userDetails[0]->email
            ]);
        }

      
    }


    public function verifyUser($email, $pswd){
        $userDetails =  User::where('email', '=', $email)
                        ->where('password', '=', $pswd)
                        ->get();
        return $userDetails;

    }


    public static function verifyToken($token){
        $userDetails =  User::where('api_token', '=', $token)->get();
        if(!isset($userDetails[0]->email)) {
          return false;
        } else {
          return true;  
        }
      
    }



}


