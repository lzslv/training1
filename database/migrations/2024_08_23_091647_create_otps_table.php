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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('otp_expires_at');
            $table->timestamps();

            $table->index('user_id', 'otps_user_idx');
            $table->foreign('user_id', 'otps_user_fk')->references('id')->on('users');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
