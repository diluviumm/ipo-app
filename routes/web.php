<?php

use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BandingController;
use App\Http\Controllers\CalculateController;
use App\Http\Controllers\SistemController;
use App\Http\Controllers\SampahController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\CariController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DimensionController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/provinsi', [ApiController::class, 'provinsi']);
    Route::get('/ranking', [ApiController::class, 'ranking']);
    Route::get('/tren/{province}', [ApiController::class, 'tren'])->where('province', '[0-9]+');
});

Route::get('/', fn() => redirect('/dashboard'));
Route::get('/api-dok', fn() => view('api.dok'));

Route::get('/bahasa/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en'], true)) session(['locale' => $locale]);
    return back();
})->where('locale', '[a-z]+');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/lupa-password', [AuthController::class, 'lupa']);
    Route::post('/lupa-password', [AuthController::class, 'aturUlang'])->middleware('throttle:10,1');
    Route::get('/verifikasi-2fa', [AuthController::class, 'kode2fa']);
    Route::post('/verifikasi-2fa', [AuthController::class, 'cek2fa']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/sesi/perpanjang', [AuthController::class, 'perpanjang']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/profil/password', [ProfilController::class, 'password']);
    Route::post('/profil/password', [ProfilController::class, 'updatePassword']);
    Route::get('/profil/2fa/mulai', [ProfilController::class, 'mulai2fa']);
    Route::post('/profil/2fa/simpan', [ProfilController::class, 'simpan2fa']);
    Route::post('/profil/2fa/mati', [ProfilController::class, 'mati2fa']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/cari', [CariController::class, 'cari']);

    Route::get('/data', [DimensionController::class, 'menu']);
    Route::get('/data/{dim}', [DimensionController::class, 'index'])->where('dim', '[a-z-]+');
    Route::get('/data/{dim}/{id}/riwayat', [DimensionController::class, 'riwayat'])->where(['dim' => '[a-z-]+', 'id' => '[0-9]+']);
    Route::middleware('admin')->group(function () {
        Route::get('/data/{dim}/tambah', [DimensionController::class, 'create'])->where('dim', '[a-z-]+');
        Route::get('/sampah', [SampahController::class, 'index']);
        Route::post('/sampah/{id}/pulih', [SampahController::class, 'pulih'])->where('id', '[0-9]+');
        Route::delete('/sampah/{id}', [SampahController::class, 'hapus'])->where('id', '[0-9]+');
        Route::post('/sampah/kosongkan', [SampahController::class, 'kosongkan']);
        Route::get('/data/{dim}/impor', [DimensionController::class, 'impor'])->where('dim', '[a-z-]+');
        Route::get('/data/{dim}/contoh', [DimensionController::class, 'contoh'])->where('dim', '[a-z-]+');
        Route::post('/data/{dim}/impor', [DimensionController::class, 'prosesImpor'])->where('dim', '[a-z-]+');
        Route::post('/data/{dim}/impor/konfirmasi', [DimensionController::class, 'konfirmasiImpor'])->where('dim', '[a-z-]+');
        Route::post('/data/{dim}', [DimensionController::class, 'store'])->where('dim', '[a-z-]+');
        Route::get('/data/{dim}/{id}/ubah', [DimensionController::class, 'edit'])->where(['dim' => '[a-z-]+', 'id' => '[0-9]+']);
        Route::put('/data/{dim}/{id}', [DimensionController::class, 'update'])->where(['dim' => '[a-z-]+', 'id' => '[0-9]+']);
        Route::delete('/data/{dim}/hapus-banyak', [DimensionController::class, 'hapusBanyak'])->where('dim', '[a-z-]+');
        Route::delete('/data/{dim}/{id}', [DimensionController::class, 'destroy'])->where(['dim' => '[a-z-]+', 'id' => '[0-9]+']);
    });

    Route::get('/hitung', [CalculateController::class, 'show']);
    Route::middleware('admin')->group(function () {
        Route::post('/hitung/ulang', [CalculateController::class, 'recalculate']);
    });
    Route::get('/hitung/riwayat/{provinceId}', [CalculateController::class, 'history'])->where('provinceId', '[0-9]+');

    Route::get('/grafik', [GraphController::class, 'show']);
    Route::get('/banding', [BandingController::class, 'index']);
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->where('id', '[0-9]+');
    Route::delete('/notifikasi/{id}', [NotifikasiController::class, 'hapus'])->where('id', '[0-9]+');

    Route::get('/laporan', [ReportsController::class, 'index']);
    Route::get('/laporan/pdf', [ReportsController::class, 'pdf']);
    Route::get('/laporan/csv', [ReportsController::class, 'csv'])->middleware('throttle:60,1');
    Route::get('/laporan/xlsx', [ReportsController::class, 'xlsx'])->middleware('throttle:60,1');

    Route::middleware('superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/aktivitas', [AktivitasController::class, 'index']);
        Route::get('/aktivitas/ekspor', [AktivitasController::class, 'ekspor']);
        Route::post('/aktivitas/bersih', [AktivitasController::class, 'bersih']);
        Route::get('/sistem', [SistemController::class, 'index']);
        Route::get('/sistem/backup', [SistemController::class, 'backup']);
        Route::post('/sistem/kunci/{year}', [SistemController::class, 'kunci'])->where('year', '[0-9]+');
        Route::post('/sistem/buka/{year}', [SistemController::class, 'buka'])->where('year', '[0-9]+');
        Route::post('/sistem/vakum', [SistemController::class, 'vakum']);
        Route::get('/users/tambah', [UserController::class, 'create']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}/ubah', [UserController::class, 'edit'])->where('id', '[0-9]+');
        Route::put('/users/{id}', [UserController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->where('id', '[0-9]+');
        Route::post('/users/{id}/reset', [UserController::class, 'tokenReset'])->where('id', '[0-9]+');
        Route::post('/users/register-toggle', [UserController::class, 'toggleRegister']);
        Route::get('/users/ekspor', [UserController::class, 'ekspor']);
    });

    Route::get('/bantuan', fn() => view('help'));
    Route::get('/api/options/{kind}', [DimensionController::class, 'options']);
});
