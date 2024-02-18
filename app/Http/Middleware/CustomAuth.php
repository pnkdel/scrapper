<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        
        if($request->bearerToken()) {
            $token = $request->bearerToken();
            if($this->verifyToken($token) == false ) {
                return redirect('api/login/1');
            } else {
                return $next($request);
            }
            
        } else {
            return redirect('api/login/0');
        }

        
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
