<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $t) {
            $t->id();
            $t->integer('province_id');
            $t->integer('year');
            $t->float('skor_lama');
            $t->float('skor_baru');
            $t->timestamp('created_at')->useCurrent();
            $t->index(['province_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};