<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getUser(){
        $token = Cookie::get('auth_token');
        if (!$token){
            $cookies = Cookie::get();
            foreach($cookies as $cookie){
                if ($cookie->getName() == 'auth_token'){
                    $token = $cookie->getValue();
                    break;
                }
            }
        }
        $user = JWTAuth::setToken($token)->authenticate();
        if ($user){
            return $user;
        } else {
            $this->response = [
                'msg' => 'Unauthorized',
                'status' => false,
                'status_code' => "UNAUTHORIZED"
            ];
            $this->response_code = 401;
            return response()->json($this->response,$this->response_code);
        }
        
    }
}
