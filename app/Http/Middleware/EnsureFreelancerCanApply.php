<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\{Freelance,Proposal};
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureFreelancerCanApply
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$slug=null): Response
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
        $freelance = Freelance::where('slug',$slug)->first();
        $exists = Proposal::where('freelance_id',$freelance->id)
                        ->where('freelancer_id',$user->id)
                        ->where('status','pending')
                        ->exists();
        if ($exists){
            return response()->json(['message'=>'You have pending application to this job'],409);
        }
        return $next($request);
    }
}
