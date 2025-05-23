<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|email).*$'); 

// Route::post('/broadcasting/auth', function (Request $request) {
//     try {
//         $token = null;
//         $cookies = Cookie::get();
//         foreach ($cookies as $cookie) {
//             if ($cookie->getName() == 'auth_token') {
//                 $token = $cookie->getValue();
//                 break;
//             }
//         }
//         \Log::info('Token',['token'=>$token]);

//         $user = JWTAuth::setToken($token)->authenticate();
//         Auth::setUser($user);

//         \Log::info('Authenticated user for broadcasting', ['id' => $user->id]);

//         return Broadcast::auth($request);
//     } catch (\Exception $e) {
//         \Log::error('Broadcast Auth Error', ['message' => $e->getMessage()]);
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }
// });



//regex 
// ^ - start of string
// (?!api|email)
// * matches all


// Route::post('/email/verify',['as'=>'verify.email','uses'=>'App\Http\Controllers\AuthController@verify_email']);