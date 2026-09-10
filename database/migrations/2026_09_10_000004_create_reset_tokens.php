<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_tokens', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('token', 64)->unique();
            $t->timestamp('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_tokens');
    }
};