<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatService
{

    public function storeChat(array $data)
    {
        $chat = Chat::create();
        $chat->users()->attach([Auth::id(), $data['user_id']]);

        return $chat;
    }

    public function sendMessage($chatId, array $data){

        $chat = Chat::findOrFail($chatId);

        if (!$chat->users->contains(Auth::id())) {
            response()->json(['error' => 'Not a participant'], 403)->send();
            exit();
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'message' => $data['message']
        ]);

        return $message;
    }

}
