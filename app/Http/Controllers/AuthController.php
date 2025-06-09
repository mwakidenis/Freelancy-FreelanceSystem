<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{User};
// use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use App\Mail\{CustomEmailVerification};
use App\Http\Resources\{ProfileResource};
use DB,Str,Mail;

class AuthController extends Controller
{
    public function __construct(){
        $this->response = [
            'msg' => 'Invalid',
            'status' => false,
            'status_code' => 'INVALID'
        ];
        $this->response_code = 400;
    }

    public function auth(){
        $credentials = request(['email','password']);
        
        if (!$token = JWTAuth::attempt($credentials)){
            // return response()->json(['message'=>'YOURE HERE'],200);
            return response()->json(['message'=>'Wrong Credentials'],401);
        }

        /* if i want to implement multi-table authentication (the problem is! how can i specify from request which user???)
        > its such a hassle to put which role is it right? and if ever ill query 3 sql commands to check where the email exists right? 
        1.

        */

        // return response()->json($token);

        // $test = $this->respondWithToken($token);
        // return response()->json($test,200);
        // $user = User::where('email',request('email'))->first();
        // return response()->json($user,200);
        // return response()->json("you are logged in");

        //the parameters are the ff: cookie(cookie_name,cookie_value,expiration,path accessbility,domain,secure,http only)
        $cookie = cookie('auth_token',$token,60,'/',null,true,true); 
        return response()->json(['message'=>'Login successfully'])->withCookie($cookie);
    }

    public function getUser()
    {   
        try {
            $token = Cookie::get('auth_token'); 
            $user = JWTAuth::setToken($token)->authenticate(); //setToken manually sets the token (in this case from cookie)
            $this->response = [
                'msg' => 'User Profile',
                'status' => true,
                'status_code' => 'USER_PROFILE',
            ] + ProfileResource::make($user)->response()->getData(true);
            $this->response_code = 200;
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            return response()->json(['message'=>"Unauthorized"],401);
        }
    }

    public function logout(Request $request){
        try {
            // return response()->json(['message'=>'I am clicked',200]);
            $token = $request->cookie('auth_token');
            JWTAuth::setToken($token)->invalidate();
            return response()->json(['message'=>'Successfully Logout'],200)
                ->withCookie(cookie()->forget('auth_token'));
        } catch (\Exception $e){
            return response()->json(['message'=>"Unauthorized"],401);
        }
    }

    public function register(Request $request){
        $email = Str::lower($request->input('email'));
        $email = User::where('email',$email)->first();
        if($email){
            $this->response = [
                'msg' => 'Email already used',
                'status' => false,
                'status_code' => 'EMAIL_EXISTS'
            ];
            $this->response_code = 409;
            goto callback;
        }
        DB::beginTransaction();
        try{
            $user = new User();
            $user->name = $request->input('name');
            $user->email = Str::lower($request->input('email'));
            $user->role = Str::lower($request->input('role'));
            $user->password = bcrypt($request->input('password'));
            //sha256 is 64 bit encryption + Str random for 40 unique characters + jumble from APP_KEY for more security
            $verification_token = hash_hmac('sha256', Str::random(40), env('APP_KEY')); 
            $user->verification_token = $verification_token;
            // $token = hash_hmac('sha256', Str::random(40), env('APP_KEY'));
            $user->save();
            DB::commit();
            // event(new Registered($user)); //Laravel built in Email verification
            Mail::to($user->email)->send(new CustomEmailVerification($user));
            return response()->json(['message'=>'Successfully registered'], 201);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json(['message'=>$e->getMessage()],400);
        }
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function verify_email(Request $request){
        // return response()->json(['message'=>"i received it ah"],200);
        // dd("im here");
        /*
        url from request is: {{url}}/verify/email/{{verification_token}}
        1. Retrieve the token from request
        2. Find the user with the similar token
        3. If none, return error else return success
        */
        $token = $request->token;
        if(!$token){
            return response()->json(['message'=>'Token is invalid.'],401);
        }
        $user = User::where('verification_token',$token)
                ->where('email_verified_at',null)
                ->first();
        if(!$user){
            return response()->json(['message'=>'Token is invalid.'],401);
        }
        $user->email_verified_at = now();
        $user->save();
        // return redirect()->route("/login");
        return response()->json(['message'=>'Email is verified'],200);
    }
}
