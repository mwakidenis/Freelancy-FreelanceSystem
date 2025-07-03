<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\{User,Freelance,Proposal};
use App\Http\Resources\{FreelanceResource,ProjectResource};
use DB,Str;

class UserController extends Controller
{
    public function __construct(){
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => 'BAD_REQUEST',
        ];
        $this->response_code = 400;
        $this->per_page = env('DEFAULT_PER_PAGE',10);
    }

    public function getUserFreelances(){
        $user = $this->getUser();
        $freelances = Freelance::where('client_id',$user->id)->get();
        if ($freelances->isEmpty()){
            $this->response = [
                'msg' => 'No freelance projects yet',
                'status' => true,
                'status_code' => 'NO_FREELANCE_PROJECTS',
            ];
            $this->response_code = 200;
            goto callback;
        }
        $this->response = [
            'msg' => 'List of freelance projects',
            'status' => true,
            'status_code' => 'LIST_FREELANCE_PROJECTS'
        ] + FreelanceResource::collection(Freelance::where('client_id',$user->id)
                                                    ->paginate($this->per_page))
                                                    ->response()
                                                    ->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function getUserProposals(){
        $token = Cookie::get('auth_token');
        if(!$token){
            $cookies = Cookie::get();
            foreach($cookies as $cookie){
                if ($cookie->getName() == 'auth_token'){
                    $token = $cookie->getValue();
                    break;
                }
            }
        }
        $user = JWTAuth::setToken($token)->authenticate();
        $proposals = Proposal::where('freelancer_id',$user->id)->get();
        if ($proposals->isEmpty()){
            $this->response = [
                'msg' => 'No proposals yet',
                'status' => true,
                'status_code' => 'NO_PROPOSALS',
            ];
            $this->response_code = 200;
            goto callback;
        }
        $this->response = [
            'msg' => 'List of proposals',
            'status' => true,
            'status_code' => 'LIST_PROPOSALS'
        ] + ProjectResource::collection(Proposal::where('freelancer_id',$user->id)
                                                    ->paginate($this->per_page))
                                                    ->response()
                                                    ->getData(true);
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function getProposalsFromSingleFreelance($slug = null){
        $user = $this->getUser();
        $freelance = Freelance::where('slug',$slug)->first();
        $proposals = Proposal::where('freelance_id',$freelance->id)->paginate($this->per_page);
        if ($proposals->isEmpty()){
            $this->response = [
                'msg' => 'No proposals yet',
                'status' => true,
                'status_code' => 'NO_PROPOSALS'
            ];
            $this->response_code = 201;
            goto callback;
        }
        $this->response = [
            'msg' => 'List of Proposals',
            'status' => true,
            'status_code' => 'PROPOSAL_LIST'
        ] + ProjectResource::collection($proposals)
                            ->response()
                            ->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }
}
