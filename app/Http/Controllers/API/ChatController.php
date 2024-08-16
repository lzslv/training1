<?php
namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return Auth::user()->chats()->with('users', 'messages')->get();
    }

    public function store(Request $request)
    {
        $chat = new Chat();
        $chat->save();

        $chat->users()->attach(Auth::id());
        $chat->users()->attach($request->user_id);

        return response()->json(['chat_id' => $chat->id]);
    }

    public function sendMessage(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (!$chat->users->contains(Auth::id())) {
            return response()->json(['error' => 'Not a participant'], 403);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }
}
