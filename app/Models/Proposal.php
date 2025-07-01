<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{Freelance};

class Proposal extends Model
{
    use HasFactory,SoftDeletes;

    public function freelance(){
        return $this->belongsTo(Freelance::class,'freelance_id','id');
    }
}
