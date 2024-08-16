<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message');

            $table->timestamps();

            $table->index('user_id', 'messages_user_idx');
            $table->foreign('user_id', 'messages_user_fk')->references('id')->on('users');

            $table->index('chat_id', 'messages_chat_idx');
            $table->foreign('chat_id', 'messages_chat_fk')->references('id')->on('chats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
