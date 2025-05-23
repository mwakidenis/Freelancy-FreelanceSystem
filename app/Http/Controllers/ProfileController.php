<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User,Profile};
use App\Http\Resources\{ProfileResource};

class ProfileController extends Controller
{
    public function __construct(){
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => 400
        ];
        $this->response_code = 400;
    }

    public function view($username = null){
        $user = User::where('username',$username)->first();
        if (!$user){
            $this->response = [
                'msg' => 'User not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $this->response = [
            'msg' => 'View Profile',
            'status' => true,
            'status_code' => 'VIEW_PROFILE', 
        ] + ProfileResource::make($user)->response()->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }
}
