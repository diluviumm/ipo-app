<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_terkunci', function (Blueprint $t) {
            $t->integer('year')->primary();
        });
        DB::table('tahun_terkunci')->insert([
            ['year' => 2020], ['year' => 2021], ['year' => 2022], ['year' => 2023],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_terkunci');
    }
};
