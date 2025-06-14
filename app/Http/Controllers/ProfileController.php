<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User,Profile};
use App\Http\Resources\{ProfileResource};
use App\Http\Requests\{EditWorkExperienceRequest};
use DB;

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

    public function update_work(Request $request){
        $user = $this->getUser();
        DB::beginTransaction();
        try {
            $profile = Profile::where('user_id',$user->id)->first();
            if (!$profile){
                $profile = new Profile();
            }
            $profile->user_id = $user->id;
            $profile->work_experience = json_encode(json_decode($request->input('work_experience'),true));
            $profile->save();
            DB::commit();
            $this->response = [
                'msg' => 'Work Experience updated',
                'status' => true,
                'status_code' =>  'WORK_EXPERIENCE_UPDATED',
                'data' => $profile->work_experience
            ];
            $this->response_code = 200;
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json($this->response,$this->response_code);
        } 
    }
}
