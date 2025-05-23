<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{Proposal};

class Freelance extends Model
{
    use HasFactory,SoftDeletes;

    public function getNumberOfProposalAttribute(){
        return Proposal::where('freelance_id',$this->id)
                        ->count();
    }

    public function getNumberOfPendingAttribute(){
        return Proposal::where('freelance_id',$this->id)
                        ->where('status','pending')
                        ->count();
    }

    public function getNumberOfDeclinedAttribute(){
        return Proposal::where('freelance_id',$this->id)
                        ->where('status','declined')
                        ->count();
    }

    public function getNumberOfProjectsAttribute(){
        return Freelance::where('client_id',$this->client_id)
                        ->count();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','client_id','id');
    }
}
