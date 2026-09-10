<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $t) {
            $t->id();
            $t->string('username', 64);
            $t->string('ip', 45)->nullable();
            $t->string('agen', 255)->nullable();
            $t->boolean('berhasil')->default(false);
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
