<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\{Freelance};

class EnsureOwnFreelanceProject extends GetUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->getUser();
        if (!$user){
            goto callback;
        } 
        $slug = $request->route('slug');
        $freelance = Freelance::where('slug',$slug)->first();
        if ($freelance->client_id != $user->id){
            callback:
            return response()->json(['msg'=>'Unauthorized'],401);
        }
        return $next($request);
    }
}
