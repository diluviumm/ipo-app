<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sampah', function (Blueprint $t) {
            $t->id();
            $t->string('tabel', 64);
            $t->string('dim', 32);
            $t->integer('row_id');
            $t->text('data');
            $t->string('dihapus_oleh', 64)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sampah');
    }
};
