<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureIsFreelancer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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
        if ($user->role == 'freelancer'){
            return $next($request);
        } else {
            return response()->json(['message'=>'Unauthorized'],401);
        }
    }
}
