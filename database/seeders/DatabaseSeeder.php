<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: TrenSeeder memakai INSERT biasa — tolak jika DB sudah berisi
        // agar tidak duplikasi. Paksa dengan DB_SEED_FORCE=1 bila sadar risiko.
        $filled = DB::table('ipo_summary')->count();
        if ($filled > 0 && env('DB_SEED_FORCE') != '1') {
            $this->command->error("⛔ ipo_summary sudah berisi {$filled} baris. Seed penuh hanya untuk DB kosong.");
            $this->command->line('   Jalankan: php artisan migrate:fresh --seed  ATAU  DB_SEED_FORCE=1 php artisan db:seed');
            return;
        }
        // ~8000 baris insert: matikan fsync per-commit selama seed (10–50x lebih cepat).
        DB::statement('PRAGMA synchronous = OFF');
        try {
            DB::transaction(fn() => $this->call(SampleSeeder::class));
            DB::transaction(fn() => $this->call(Nasional2024Seeder::class));
            DB::transaction(fn() => $this->call(TrenSeeder::class));
            DB::transaction(fn() => $this->call(OperatorsSeeder::class));
        } finally {
            DB::statement('PRAGMA synchronous = FULL');
        }
        $sum = DB::table('ipo_summary')->count();
        $usr = DB::table('users')->count();
        $this->command->info("🎉 Seed terpadu selesai: {$sum} summary, {$usr} users.");
    }
}
