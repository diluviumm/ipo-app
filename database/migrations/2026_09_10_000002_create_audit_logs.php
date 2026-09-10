<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id')->nullable();
            $t->string('username', 50)->nullable();
            $t->string('aksi', 20);
            $t->string('tabel', 40);
            $t->integer('row_id')->nullable();
            $t->text('detail')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['tabel', 'row_id']);
            $t->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};