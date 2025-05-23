<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TestRequest;
use App\Models\News;
use DB;

class TestController extends Controller
{

    protected $response = [];
    protected $response_code;
    protected $per_page;

    public function __construct(){
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => 'BAD_REQUEST'
        ];
        $this->response_code = 400;
        $this->per_page = env('DEFAULT_PER_PAGE',10);
    }

    public function test(){
        return response()->json($this->response,400);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = "BADING AKO";
        return response()->json($data,200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TestRequest $request)
    {
        DB::beginTransaction();
        $news = new News();
        $news->title = $request->input('title');
        $news->description = $request->input('description');
        $news->save();
        DB::commit();
        $response_code = 200;
        $data = [
            'response_message' => 'data has been created',
        ];
        return response()->json($data,$response_code);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
