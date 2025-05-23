<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetUser
{
    protected function getUser()
    {
        $token = Cookie::get('auth_token');
    
        // Check all cookies if the token is missing
        if (!$token) {
            $cookies = Cookie::get();
            foreach ($cookies as $cookie) {
                if ($cookie->getName() == 'auth_token') {
                    $token = $cookie->getValue();
                    break;
                }
            }
        }
    
        // If still no token, return null instead of a response
        if (!$token) {
            return null; // Change from returning a response
        }
    
        try {
            $user = JWTAuth::setToken($token)->authenticate();
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }
}
