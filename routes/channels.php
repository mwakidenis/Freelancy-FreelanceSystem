<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

//the url here isnt literally url (chat.{id})! but instead is a private channel listed in the events
//broadcast is technically a tool that listens to events  
Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
    return (int) $user->id === (int) $user1 || (int) $user->id === (int) $user2;
});

Broadcast::channel('inbox.{userId}', function ($user,$userId){
    return (int) $user->id === (int) $userId;
});
