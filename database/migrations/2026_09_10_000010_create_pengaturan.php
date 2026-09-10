<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 64)->primary();
            $t->string('nilai', 255)->default('0');
        });
        DB::table('pengaturan')->insert(['kunci' => 'register_tutup', 'nilai' => '0']);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
