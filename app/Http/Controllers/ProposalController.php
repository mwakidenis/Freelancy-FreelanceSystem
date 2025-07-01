<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\{Proposal,Freelance};
use App\Http\Resources\{ProposalResource};
use Illuminate\Http\Request;
use DB,Str;

class ProposalController extends Controller
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

    protected function freelancerCanApply($id=null)
    {
        $user = $this->getUser();
        return Proposal::where('freelance_id',$id)
                        ->where('freelancer_id',$user->id)
                        ->whereIn('status',['pending','completed'])
                        ->count() ? false : true;
    }

    public function checkIfFreelancerCanApply($slug=null)
    {     
        $freelance = Freelance::where('slug',$slug)->first();
        if(!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status_code' => 'NO_FREELANCE_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $canApply = $this->freelancerCanApply($freelance->id);  
        if (!$canApply){
            $this->response = [
                'msg' => 'You cannot send multiple proposal to a similar freelance project',
                'status' => false,
                'status_code' => 'MULTIPLE_PROPOSALS'
            ];
            $this->response_code = 409;
            goto callback;
        }
        $this->response = [
            'msg' => 'User can apply to this project.',
            'status' => true,
            'status_code' => 'NO_EXISTING_PROPOSAL',
        ];
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,$slug = null)
    {
        $user = $this->getUser();
        $freelance = Freelance::where('slug',$slug)->first();
        if(!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status_code' => 'NO_FREELANCE_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $canApply = $this->freelancerCanApply($freelance->id);
        if (!$canApply){
            $this->response = [
                'msg' => 'You cannot send multiple proposal to a similar freelance project',
                'status' => false,
                'status_code' => 'MULTIPLE_PROPOSALS'
            ];
            $this->response_code = 409;
            goto callback;
        }
        DB::beginTransaction();
        try{
            $proposal = new Proposal();
            $proposal->freelance_id = $freelance->id;
            $proposal->freelancer_id = $user->id;
            $proposal->description = $request->input('description');
            $proposal->status = 'pending';
            $proposal->save();
            DB::commit();
            $this->response = [
                'msg' => 'Proposal successfully submitted.',
                'status' => true,
                'status_code' => 'PROPOSAL_CREATED',
            ];
            $this->response_code = 201;
        } catch(\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => "Server Error: ${$e->getMessage()}",
                'status' => false,
                'status_code' => 'SERVER_ERROR'
            ];
            $this->response_code = 500;
        }
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function show($id=null){
        $proposal = Proposal::find($id);
        if (!$proposal){
            $this->response = [
                'msg' => 'Proposal not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $this->response = [
            'msg' => 'View Proposal',
            'status'=> true,
            'status_code' => 'VIEW_PROPOSAL',
            'data' => new ProposalResource($proposal)
        ];
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function getFreelancerProposals(Request $request)
    {
        $user = $this->getUser();
        $query = $request->input('query','');
        $proposals = Proposal::with(['freelance'])
            ->when(!empty($query) , function ($q) use ($query) {
                $q->whereHas('freelance', function($q) use ($query) {
                    $q->where('title','LIKE',"%".$query."%");
                })
                ->orWhere('status','LIKE',"%".$query."%");
            })
            ->where('freelancer_id',$user->id)
            ->paginate($this->per_page);
        $this->response = [
            'msg' => 'List of proposals',
            'status' => true,
            'status_code' => 'LIST_OF_PROPOSALS'
        ] + ProposalResource::collection($proposals)->response()->getData(true);
        $this->response_code = 200;
        return response()->json($this->response,$this->response_code);
    }

    public function updateProposalStatus(Request $request,$id=null)
    {
        $user = $this->getUser();
        $proposal = Proposal::where('freelancer_id',$user->id)
                    ->where('id',$id)
                    ->first();
        if (!$proposal){
            $this->response = [
                'msg' => 'Proposal not found',
                'status' => false,
                'status_code' => 'PROPOSAL_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $type = Str::lower($request->input('type'));
        DB::beginTransaction();
        try {
            $proposal->status = $type == 'withdraw' ? 'withdrawn' : 'done';
            $proposal->save();
            DB::commit();
            $this->response = [
                'msg' => `Proposal has been marked as ${type}`,
                'status' => true,
                'status_code' => 'PROPOSAL_' . Str::upper($type),
                'data' => new ProposalResource($proposal)
            ];
            $this->response_code = 200;
            callback:
            return response()->json($this->response,$this->response_code);
        } catch (\Exception $e){
            $this->response = [
                'msg' => $e->getMessage(),
                'status' => false,
                'status_code' => 'ERROR'
            ];
            $this->response_code = 404;
            return response()->json($this->response,$this->response_code);
        }
    }
}
