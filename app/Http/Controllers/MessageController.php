<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Message,User};
use App\Events\{MessageSent};
use App\Http\Resources\{UserInboxResource,UserMessageResource,RecentContactsResource};
use DB;
use Illuminate\Support\Facades\Cookie;

class MessageController extends Controller
{
    public function __construct(){
        $this->response = [
            'msg' => 'Bad Request',
            'status' => false,
            'status_code' => 'BAD_REQUEST'
        ];
        $this->response_code = 400;
        $this->per_page = env('DEFAULT_PER_PAGE',10);
    }

    public function inbox(){
        $user = $this->getUser();                    
        $inbox = DB::table('messages as m1')
                ->where(function($query) use ($user) {
                    $query->where('m1.sender_id', $user->id)
                        ->orWhere('m1.recipient_id', $user->id);
                })
                ->selectRaw('
                    LEAST(m1.sender_id, m1.recipient_id) as user1, 
                    GREATEST(m1.sender_id, m1.recipient_id) as user2,
                    m1.message,
                    m1.created_at
                ')
                ->whereRaw('m1.created_at = (
                    SELECT MAX(m2.created_at)
                    FROM messages m2
                    WHERE LEAST(m1.sender_id, m1.recipient_id) = LEAST(m2.sender_id, m2.recipient_id)
                    AND GREATEST(m1.sender_id, m1.recipient_id) = GREATEST(m2.sender_id, m2.recipient_id)
                )')
                ->orderByDesc('m1.created_at')
                ->paginate($this->per_page);

        $this->response = [
            'msg' => 'Inbox',
            'status' => true,
            'status_code' => 'INBOX',
        ] + UserInboxResource::collection($inbox)->response()->getData(true);
        $this->response_code = 200;
 
        return response()->json($this->response,$this->response_code);
    }

    public function retrieveMessages($username=null){
        $user = $this->getUser();
        $recipient = User::where('username',$username)->first();

        if (!$recipient){
            $this->response = [
                'msg' => 'User not found',
                'status' => false,
                'status_code' => 'USER_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }

        $messages = Message::where(function($query) use ($user,$recipient){
                $query->where('sender_id',$user->id) 
                    ->where('recipient_id',$recipient->id);
                })
                ->orWhere(function($query) use($user,$recipient){
                    $query->where('sender_id',$recipient->id)
                        ->where('recipient_id',$user->id);
                })
                ->get();

        $this->response = [
            'msg' => 'List of messages with ' . $username,
            'status' => true,
            'status_code' => 'LIST_OF_MESSAGES'
        ] + UserMessageResource::collection($messages)->response()->getData(true);
        $this->response_code = 200;
        
        callback:
        return response()->json($this->response,$this->response_code);
    }

    public function sendMessage(Request $request,$username=null){
        $sender = $this->getUser();
        $recipient = User::where('username',$username)->first();
        if (!$recipient){
            $this->response = [
                'msg' => 'User not found.',
                'status' => false,
                'status_code' => 'USER_NOT_FOUND'
            ];
            $this->response_code = 404;
            goto callback;
        }
        DB::beginTransaction();
        try{
            $message = new Message();
            $message->sender_id = $sender->id;
            $message->recipient_id = $recipient->id;
            $message->message = $request->input('message');
            $message->save();
            DB::commit();
            $this->response = [
                'msg' => 'Message sent',
                'status' => true,
                'status_code' => 'MESSAGE_SENT',
                'message'=> $message->toArray()
            ];
            $this->response_code = 201;
            broadcast(new MessageSent($message))->toOthers();
            goto callback;
        } catch (\Exception $e){
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

    public function retrieveRecentContacts(){
        $user = $this->getUser();                    
        $inbox = DB::table('messages as m1')
                ->where(function($query) use ($user) {
                    $query->where('m1.sender_id', $user->id)
                        ->orWhere('m1.recipient_id', $user->id);
                })
                ->selectRaw('
                    LEAST(m1.sender_id, m1.recipient_id) as user1, 
                    GREATEST(m1.sender_id, m1.recipient_id) as user2,
                    m1.message,
                    m1.created_at
                ')
                ->whereRaw('m1.created_at = (
                    SELECT MAX(m2.created_at)
                    FROM messages m2
                    WHERE LEAST(m1.sender_id, m1.recipient_id) = LEAST(m2.sender_id, m2.recipient_id)
                    AND GREATEST(m1.sender_id, m1.recipient_id) = GREATEST(m2.sender_id, m2.recipient_id)
                )')
                ->orderByDesc('m1.created_at')
                ->paginate($this->per_page);

        $this->response = [
            'msg' => 'Recent Contacts',
            'status' => true,
            'status_code' => 'RECENT_CONTACTS'
        ] + RecentContactsResource::collection($inbox)->response()->getData(true);
        $this->response_code = 200;

        return response()->json($this->response,$this->response_code);
    }
}
