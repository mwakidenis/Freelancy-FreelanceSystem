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

    public function update_description(Request $request){
        $user = $this->getUser();
        DB::beginTransaction();
        try{
            $profile = Profile::where('user_id',$user->id)->first();
            if (!$profile){
                $profile = new Profile();
            }
            $profile->user_id = $user->id;
            $profile->description = $request->input('description');
            $profile->save();
            DB::commit();
            $this->response = [
                'msg' => 'Profile description updated.',
                'status' => true,
                'status_code' => 'PROFILE_DESCRIPTION_UPDATED',
                'data' => $profile->description
            ];
            $this->response_code = 200;
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => $e->getMessage()
            ];
            return response()->json($this->response,$this->response_code);
        }
    }

    public function update_skills(Request $request){
        $user = $this->getUser();
        DB::beginTransaction();
        try{
            $profile = Profile::where('user_id',$user->id)->first();
            if (!$profile){
                $profile = new Profile();
            }
            $profile->user_id = $user->id;
            $profile->skills = json_encode(json_decode($request->input('skills'),true));
            $profile->save();
            DB::commit();
            $this->response = [
                'msg' => 'Profile skills updated.',
                'status' => true,
                'status_code' => 'PROFILE_SKILLS_UPDATED',
                'data' => $profile->skills
            ];
            $this->response_code = 200;
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => $e->getMessage()
            ];
            return response()->json($this->response,$this->response_code);
        }
    }

    public function update_educational_attainment(Request $request){
        $user = $this->getUser();
        DB::beginTransaction();
        try{
            $profile = Profile::where('user_id',$user->id)->first();
            if (!$profile){
                $profile = new Profile();
            }
            $profile->user_id = $user->id;
            $profile->educational_attainment = json_encode(json_decode($request->input('educational_attainment'),true));
            $profile->save();
            DB::commit();
            $this->response = [
                'msg' => 'Profile educational_attainment updated.',
                'status' => true,
                'status_code' => 'PROFILE_EDUCATIONAL_ATTAINMENT_UPDATED',
                'data' => $profile->educational_attainment
            ];
            $this->response_code = 200;
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => $e->getMessage()
            ];
            return response()->json($this->response,$this->response_code);
        }
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

    public function update_profile(Request $request){
        $user = $this->getUser();
        DB::beginTransaction();
        try{
            $type = $request->input('type');
            $profile = Profile::where('user_id',$user->id)->first();
            if (!$profile){
                $profile = new Profile();
                $profile->user_id = $user->id;
                switch($type){
                    case "description":
                        $profile->description = $request->input('tempEditInput');
                        break;
                    case "educational_attainment":
                        $profile->educational_attainment = json_encode(json_decode($request->input('tempEditInput'),true));
                        break;
                    case "work_experience":
                        $profile->work_experience = json_encode(json_decode($request->input('tempEditInput'),true));
                        break;
                    case "skills":
                        $profile->skills = json_encode(json_decode($request->input('tempEditInput'),true));
                        break;
                    default:
                        break;
                }
                $profile->save();
                DB::commit();
                $this->response = [
                    'msg' => 'Profile ' . str_replace("_", " ", $type) . ' has been updated.',
                    'status' => true,
                    'status_code' => 'PROFILE_' . strtoupper(str_replace("_"," ",$type)) . '_UPDATED.',
                    'data' => "" //switch
                ];
                $this->response_code = 200;
                return response()->json($this->response,$this->response_code);
            }
        } catch(\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => $e->getMessage()
            ];
            return response()->json($this->response,$this->response_code);
        }
    }
}
