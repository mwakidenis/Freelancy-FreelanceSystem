<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Freelance};

class ClientController extends Controller
{
    public function __construct()
    {
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => 'BAD_REQUEST'
        ];
        $this->response_code = 400;
        $this->per_page = env('DEFAULT_PER_PAGE',10);
    }




}
