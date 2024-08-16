<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::findOrFail($chatId)->users->contains($user->id);
});

Broadcast::channel('home', function ($user) {
    return auth()->check();
});
