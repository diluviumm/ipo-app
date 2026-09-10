<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Skema IPO — port 1:1 dari schema SQLite app Node.js sebelumnya.
// 15 tabel: users, 4 referensi wilayah, respondents,
// 9 tabel dimensi, ipo_summary.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('name');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('name');
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('full_name');
            $table->string('role')->default('user');
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('age');
            $table->string('gender', 1);
            $table->string('age_group');
            $table->float('imt')->nullable();
            $table->string('imt_kategori')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['village_id', 'year']);
        });

        Schema::create('sdm_olahraga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('jumlah_penduduk_5plus');
            $table->integer('jumlah_sdm');
            $table->float('nilai_aktual')->nullable();
            $table->float('indeks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['district_id', 'year']);
        });

        Schema::create('ruang_terbuka', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('jumlah_penduduk_5plus');
            $table->float('luas_m2');
            $table->float('nilai_aktual')->nullable();
            $table->float('indeks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['village_id', 'year']);
        });

        foreach (['literasi_fisik', 'kebugaran', 'kesehatan', 'perkembangan_personal', 'ekonomi'] as $t) {
            Schema::create($t, function (Blueprint $table) use ($t) {
                $table->id();
                $table->foreignId('respondent_id')->constrained('respondents')->cascadeOnDelete();
                $table->integer('year');
                if ($t === 'literasi_fisik') {
                    $table->float('pengetahuan');
                    $table->float('sikap');
                    $table->float('perilaku');
                } elseif ($t === 'kebugaran') {
                    $table->float('vo2max');
                    $table->string('kategori')->nullable();
                } elseif ($t === 'kesehatan') {
                    $table->float('fisik');
                    $table->float('psikis');
                } elseif ($t === 'perkembangan_personal') {
                    $table->float('resiliensi');
                    $table->float('modal_sosial');
                } else {
                    $table->float('belanja_barang')->default(0);
                    $table->float('belanja_jasa')->default(0);
                    $table->float('total_belanja')->nullable();
                    $table->string('kategori_belanja')->nullable();
                }
                $table->float('nilai_aktual')->nullable();
                $table->float('indeks')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['respondent_id', 'year']);
            });
        }

        Schema::create('partisipasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_id')->constrained('respondents')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('frekuensi');
            $table->integer('durasi');
            $table->integer('intensitas');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['respondent_id', 'year']);
        });

        Schema::create('performa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('medali_emas')->default(0);
            $table->integer('medali_perak')->default(0);
            $table->integer('medali_perunggu')->default(0);
            $table->float('nilai_aktual')->nullable();
            $table->float('indeks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['city_id', 'year']);
        });

        Schema::create('ipo_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->integer('year');
            foreach (['d1_sdm', 'd2_ruang_terbuka', 'd3_literasi_fisik', 'd4_partisipasi', 'd5_kebugaran', 'd6_kesehatan', 'd7_perkembangan_personal', 'd8_ekonomi', 'd9_performa'] as $d) {
                $table->float($d)->default(0);
            }
            $table->float('ipo_score')->nullable();
            $table->string('kategori')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['province_id', 'year']);
            $table->index(['province_id', 'year']);
        });
    }

    public function down(): void
    {
        foreach (['ipo_summary', 'performa', 'partisipasi', 'ekonomi', 'perkembangan_personal', 'kesehatan', 'kebugaran', 'literasi_fisik', 'ruang_terbuka', 'sdm_olahraga', 'respondents', 'users', 'villages', 'districts', 'cities', 'provinces'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
