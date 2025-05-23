<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;

class BroadcastController extends Controller
{
    public function __construct(){
        //
    }

    public function authenticate(Request $request){
        try {
            $user = $this->getUser();
            Auth::setUser($user);
            return Broadcast::auth($request);
        } catch (\Exception $e) {
            \Log::error('Error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

}
