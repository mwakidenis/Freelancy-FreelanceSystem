<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Models\{Freelance,Proposal};
use App\Http\Resources\{FreelanceFreelancerResource,ProjectResource,FreelanceClientResource,ProposalResource};
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
        ] + FreelanceFreelancerResource::collection($freelances)->response()->getData(true); 
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
            $freelance->status = 'active';
            $freelance->save();
            DB::commit();
            $this->response = [
                'msg' => 'Freelance project has been created successfully',
                'status' => true,
                'status_code' => "FREELANCE_CREATED",
                'data' => new FreelanceClientResource($freelance)
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
            'data' => new FreelanceFreelancerResource($freelance)
        ];
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function showProposals($slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance project not found',
                'status' => false,
                'status_code' => 'FREELANCE_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $proposals = Proposal::where('freelance_id',$freelance->id)
            // ->where('status','pending')
            ->get();
        $this->response = [
            'msg' => 'List of proposals for ' . $freelance->slug, 
            'status' => true,
            'status_code' => 'LIST_PROPOSAL_' . Str::upper($freelance->slug)
        ] + ProposalResource::collection($proposals)->response()->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function processProposal(Request $request,$id=null){
        $user = $this->getUser();
        $proposal = Proposal::where('id',$id)->first();
        if(!$proposal){
            $this->response = [
                'msg' => 'Proposal not found',
                'status' => false,
                'status_code' => 'PROPOSAL_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        $freelance = Freelance::where('id',$proposal->freelance_id)->first();
        if ($user->id != $freelance->client_id){
            $this->response = [
                'msg' => 'Unauthorized',
                'status' => false,
                'status_code' => 'UNAUTHORIZED'
            ];
            $this->response_code = 401;
            goto callback;
        }

        $type = Str::lower($request->input('type')); //either accept or reject
        $status = $type == 'accept' ? 'in progress' : 'declined';

        DB::beginTransaction();
        try {
            $proposal->status = $status;
            $proposal->remarks = $request->input('remarks');
            $proposal->save();

            if ($type == 'accept'){
                $proposals = Proposal::where('freelance_id',$proposal->freelance_id)
                                    ->where('status', 'pending')
                                    ->where('id','<>',$proposal->id)
                                    ->get();

                foreach ($proposals as $proposal) {
                    $proposal->status = 'declined';
                    $proposal->remarks = 'Other proposal has been accepted. Thank you for applying.';
                    $proposal->save();
                }

                $freelance->status = 'in progress';
                $freelance->save();
            }
            
            DB::commit();
            $msgStatus = $type == 'accept' ? 'accepted' : 'declined';
            $this->response = [
                'msg' => `Proposal has been ${msgStatus}`,
                'status' => true,
                'status_code' => Str::upper(`PROPOSAL_${msgStatus}`)
            ];
            $this->response_code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $this->response = [
                'msg' => $e->getMessage(),
                'status' => false,
                'status_code' => 'ERROR'
            ];
            $this->response_code = 500;
        }

        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function update(Request $request, $slug=null){
        $freelance = Freelance::where('slug',$slug)->first();
        $type = $request->input('type');
        if (!$freelance){
            $this->response = [
                'msg' => 'Freelance not found',
                'status' => false,
                'status_code' => 'NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        if ($type == 'status'){
            $freelance->status = $freelance->status == 'active' ? 'inactive' : 'active';
        } else {
            $freelance->title = Str::title($request->input('title'));
            $freelance->slug = $this->generateSlug($request->input('title'));
            $freelance->category = $request->input('category');
            $freelance->rate = $request->input('rate');
            $freelance->description = $request->input('description');
        }
        $freelance->save();
        $this->response = [
            'msg' => `Freelance ${type} successfully updated`,
            'status' => true,
            'status_code' => 'FREELANCE_' . strtoupper($type) . '_UPDATED',
            'data' => new FreelanceClientResource($freelance)
        ];
        $this->response_code = 200;
        callback:
        return response($this->response,$this->response_code);
    }

    public function getClientFreelanceProjects(Request $request){
        $user = $this->getUser();
        $query = Str::lower($request->input('query',''));
        $date = $request->input('date','DESC');
        $alphabetical = $request->input('alphabetical','');

        $freelances = Freelance::where('client_id',$user->id)
                        ->when(!empty($query), function ($q) use ($query){
                            $q->where('title','LIKE',"%".$query."%")
                                ->orWhere('status',$query);
                        })
                        ->when(!empty($alphabetical), function($q) use ($alphabetical) {
                            $q->orderBy('title',$alphabetical);
                        })
                        ->orderBy('created_at',$date)
                        ->get();

        $this->response = [
            'msg' => 'Client list of freelance projects',
            'status' => true,
            'status_code' => 'CLIENT_LIST_FREELANCE_PROJECTS'
        ] + FreelanceClientResource::collection($freelances)->response()->getData(true);
        $this->response_code = 200;

        return response()->json($this->response,$this->response_code);
    }

    public function showAllProposals($slug=null){ //delete
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
        ] + ProposalResource::collection($proposals)->response()->getData(true);
        $this->response_code = 200;
        callback:
        return response()->json($this->response,$this->response_code);
    }
}
