<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\StoreChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index()
    {
        $chats = Auth::user()->chats()->with('users', 'messages')->get();
        return ChatResource::collection($chats);
    }

    public function store(StoreChatRequest $request): ChatResource
    {
        $chat = $this->chatService->storeChat($request->validated());

        return new ChatResource($chat);
    }

    public function sendMessage(SendMessageRequest $request, $chatId): MessageResource
    {
        $message = $this->chatService->sendMessage($chatId, $request->validated());
        broadcast(new MessageSent($message))->toOthers();
        return new MessageResource($message);
    }
}
