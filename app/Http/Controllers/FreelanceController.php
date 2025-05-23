<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Models\{Freelance,Proposal};
use App\Http\Resources\{FreelanceResource,ProposalResource};
use DB,Str;

class FreelanceController extends Controller
{
    public function __construct(){
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => "BAD_REQUEST"
        ];
        $this->response_code = 400;
        $this->per_page = env('DEFAULT_PER_PAGE',10);
    }
    
    protected function query($query,$sort){
        return Freelance::when(!empty($query), function($q) use ($query) {
            $q->where('title','LIKE',"%".$query."%");
        })
        ->when(in_array($sort, ['', '1']), function ($q) {
            $q->orderBy('created_at', 'desc'); // Sort by newest
        })
        ->when($sort === '2', function ($q) {
            $q->orderBy('created_at', 'asc'); // Sort by oldest
        })
        ->when($sort === '3', function ($q) {
            $q->orderBy('rate', 'asc'); // Sort by lowest rate
        })
        ->when($sort === '4', function ($q) {
            $q->orderBy('rate', 'desc'); // Sort by highest rate
        });
    }

    protected function generateSlug($title){
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;
        while(Freelance::where('slug',$slug)->exists()){
            $slug = $originalSlug . '-' . $count;
            $count++;  
        }
        return $slug;
    }

    public function index(Request $request){
        $query = Str::lower($request->input('query',''));
        $sort = Str::lower($request->input('sort','1'));
        if (!in_array($sort,['','1','2','3','4'])){
            $this->response = [
                'msg' => 'Bad request',
                'status' => false,
                'status_code' => 'BAD_REQUEST'
            ];
            $this->response_code = 400;
            goto callback;
        }
        $freelances = $this->query($query,$sort)->paginate($this->per_page);
        if ($freelances->isEmpty()){
            $this->response = [
                'msg' => 'No freelance projects yet', 
                'status' => true,
                'status_code' => 'NO_FREELANCE_PROJECTS',
            ];
            $this->response_code = 204;
            goto callback;
        }
        $this->response = [
            'msg' => 'Freelance List',
            'status' => true,
            'status_code' => "FREELANCE_LIST",
        ] + FreelanceResource::collection($freelances)->response()->getData(true); 
        //ALSO the + one is used since its automatically in data and ill just add the result (or data) to the response array
        //the response()->getData(true) is for meta sources like pagination link etc        
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function store(Request $request){
        DB::beginTransaction();
        try{
            $user = $this->getUser();
            $freelance = new Freelance();
            $freelance->client_id = $user->id;
            $freelance->title = Str::title($request->input('title'));
            $freelance->slug = $this->generateSlug($request->input('title'));
            $freelance->description = $request->input('description');
            $freelance->category = $request->input('category');
            $freelance->rate = $request->input('rate');
            $freelance->save();
            DB::commit();
            $this->response = [
                'msg' => 'Freelance project has been created successfully',
                'status' => true,
                'status_code' => "FREELANCE_CREATED",
            ];
            $this->response_code = 201;
        } catch(\Exception $e){
            DB::rollback();
            $this->response = [
                'msg' => "Server Error: {$e->getMessage()}",
                'status' => false,
                'status_code' => "SERVER_ERROR"
            ];
            $this->response_code = 500;
        }
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function show($slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $this->response = [
            'msg' => 'View Freelance',
            'status'=> true,
            'status_code' => 'VIEW_FREELANCE',
            'data' => new FreelanceResource($freelance)
        ];
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    //add middleware for checking if the user is same as creator of freelance
    public function updateInfo(Request $request,$slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $freelance->title = Str::title($request->input('title'),$freelance->title);
        $freelance->slug = $this->generateSlug($request->input('title'),$freelance->title);
        $freelance->category = $request->input('category',$freelance->category);
        $freelance->rate = $request->input('rate',$freelance->rate);
        $freelance->save();
        $this->response = [
            'msg' => 'Freelance information successfully updated',
            'status' => true,
            'status_code' => 'FREELANCE_INFO_UPDATED'
        ];
        $this->response_code = 200;
        callback:
        return response($this->response,$this->response_code);
    }
    
    //add middleware for checking if the user is same as creator of freelance
    public function updateStatus($slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $freelance->status = $freelance->status == 'active' ? 'inactive' : 'active';
        $freelance->save();
        $this->response = [
            'msg' => 'Freelance status successfully updated',
            'status' => true,
            'status_code' => 'FREELANCE_STATUS_UPDATED'
        ];
        $this->response_code = 200;
        callback:
        return response($this->response,$this->response_code);
    }

    public function showAllProposals($slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status' => false,
                'status_code' => 'FREELANCE_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $proposals = Proposal::where('freelance_id',$freelance->id)->paginate($this->per_page);
        if ($proposals->isEmpty()){
            $this->response = [
                'msg' => 'No proposals yet.',
                'status' => true,
                'status_code' => 'NO_PROPOSALS'
            ];
            $this->response_code = 204;
            goto callback;
        }
        $this->response = [
            'msg' => 'Proposal list',
            'status' => true,
            'status_code' => 'PROPOSAL_LIST',
        ] + ProposalResource::Collection($proposals)->response()->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }



}
