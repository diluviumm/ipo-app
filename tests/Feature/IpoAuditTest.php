<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Port audit 51 cek app Node.js — kontrak yang diadaptasi ke Laravel:
// - validasi gagal → 422 JSON (bukan 400), - tamu → redirect /login (bukan 401),
// - guard peran → 403, - resource hilang → 404.
class IpoAuditTest extends TestCase
{
    private int $passed = 0;

    private function t(string $name, bool $cond): void
    {
        $this->assertTrue($cond, "GAGAL: {$name}");
        $this->passed++;
    }

    private function admin()
    {
        return User::where('username', 'admin')->first();
    }

    private function acehOp()
    {
        return User::where('username', 'operator_aceh')->first();
    }

    private function jatimOp()
    {
        return User::where('username', 'operator_jatim')->first();
    }

    private function biasa()
    {
        return User::where('username', 'user_biasa')->first();
    }

    public function test_auth_dan_register(): void
    {
        DB::table('users')->where('username', 'tesreg1')->delete(); // bersih sisa run gagal
        // Tamu diarahkan ke login
        $this->get('/dashboard')->assertRedirect('/login');
        $this->t('tamu redirect login', true);

        // Login salah
        $this->post('/login', ['username' => 'admin', 'password' => 'salah'], ['Accept' => 'application/json'])
            ->assertStatus(401);
        $this->t('login salah 401', true);

        // Login benar (web)
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
            ->assertRedirect('/dashboard');
        $this->t('login admin redirect dashboard', true);

        // Login benar (JSON)
        $this->post('/logout')->assertRedirect('/login');
        $r = $this->post('/login', ['username' => 'admin', 'password' => 'admin123'], ['Accept' => 'application/json'])
            ->assertOk();
        $this->t('login JSON 200 + user', isset($r->json()['user']));
        $this->t('me JSON', $this->actingAs($this->admin())->getJson('/me')->assertOk()->json('user.username') === 'admin');

        // Register dikunci ke user (sebagai tamu: logout dulu)
        $this->post('/logout')->assertRedirect('/login');
        $r = $this->post('/register', ['username' => 'tesreg1', 'password' => 'tes12345', 'full_name' => 'Tes Reg', 'role' => 'admin'], ['Accept' => 'application/json'])->assertCreated();
        $id = $r->json('id');
        $this->t('register paksa user', DB::table('users')->where('id', $id)->value('role') === 'user');

        // Register duplikat
        $this->post('/register', ['username' => 'tesreg1', 'password' => 'x', 'full_name' => 'X'], ['Accept' => 'application/json'])->assertStatus(422);
        $this->t('register duplikat 422', true);

        // Register kosong
        $this->post('/register', [], ['Accept' => 'application/json'])->assertStatus(422);
        $this->t('register kosong 422', true);

        DB::table('users')->where('id', $id)->delete();
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_guard_peran(): void
    {
        // user_biasa: tanpa menu tulis
        $this->actingAs($this->biasa())->postJson('/data/sdm', [])->assertStatus(403);
        $this->t('user POST 403', true);
        $this->actingAs($this->biasa())->get('/users')->assertForbidden();
        $this->t('user GET users 403', true);
        $this->actingAs($this->biasa())->get('/hitung')->assertOk();
        $this->t('user lihat hitung OK', true);
        $this->actingAs($this->biasa())->get('/data/sdm')->assertOk();
        $this->t('user lihat list OK', true);

        // operator: users 403
        $this->actingAs($this->acehOp())->get('/users')->assertForbidden();
        $this->t('operator users 403', true);
        $this->actingAs($this->acehOp())->post('/hitung/ulang?year=2024')->assertRedirect();
        $this->t('operator hitung ulang OK', true);

        // Lintas provinsi: operator Aceh tidak bisa ubah data Jatim
        $jatimRow = DB::table('sdm_olahraga as s')->join('districts as d', 'd.id', '=', 's.district_id')
            ->join('cities as c', 'c.id', '=', 'd.city_id')->where('c.province_id', 15)->select('s.id')->first();
        $this->actingAs($this->acehOp())
            ->putJson("/data/sdm/{$jatimRow->id}", ['district_id' => 1, 'year' => 2024, 'jumlah_penduduk_5plus' => 100, 'jumlah_sdm' => 5])
            ->assertStatus(403);
        $this->t('lintas provinsi 403', true);

        // Lintas provinsi baca riwayat
        $this->actingAs($this->acehOp())->getJson('/hitung/riwayat/15')->assertStatus(403);
        $this->t('riwayat lintas 403', true);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_crud_semua_dimensi(): void
    {
        $admin = $this->admin();
        $cases = [
            'sdm' => ['district_id' => 1, 'year' => 2024, 'jumlah_penduduk_5plus' => 50000, 'jumlah_sdm' => 150],
            'ruang-terbuka' => ['village_id' => 1, 'year' => 2024, 'jumlah_penduduk_5plus' => 10000, 'luas_m2' => 20000],
            'literasi-fisik' => ['respondent_id' => 1, 'year' => 2024, 'pengetahuan' => 4, 'sikap' => 4, 'perilaku' => 4],
            'partisipasi' => ['respondent_id' => 1, 'year' => 2024, 'frekuensi' => 4, 'durasi' => 60, 'intensitas' => 3],
            'kebugaran' => ['respondent_id' => 1, 'year' => 2024, 'vo2max' => 40],
            'kesehatan' => ['respondent_id' => 1, 'year' => 2024, 'fisik' => 4, 'psikis' => 4],
            'perkembangan-personal' => ['respondent_id' => 1, 'year' => 2024, 'resiliensi' => 4, 'modal_sosial' => 4],
            'ekonomi' => ['respondent_id' => 1, 'year' => 2024, 'belanja_barang' => 1500000, 'belanja_jasa' => 500000],
            'performa' => ['city_id' => 1, 'year' => 2024, 'medali_emas' => 2, 'medali_perak' => 1, 'medali_perunggu' => 0],
        ];
        foreach ($cases as $dim => $payload) {
            // Validasi: kosong
            $this->actingAs($admin)->postJson("/data/{$dim}", [])->assertStatus(422);
            // Validasi: negatif
            $neg = $payload;
            $numKey = array_keys(array_filter($payload, 'is_numeric'))[2] ?? null;
            if ($numKey) {
                $neg[$numKey] = -5;
                $this->actingAs($admin)->postJson("/data/{$dim}", $neg)->assertStatus(422);
            }
            // Simpan OK
            $id = $this->actingAs($admin)->postJson("/data/{$dim}", $payload)->assertOk()->json('id');
            $this->t("{$dim} simpan", (bool) $id);
            // Ubah OK
            $this->actingAs($admin)->putJson("/data/{$dim}/{$id}", $payload)->assertOk();
            // Ubah ID ngawur 404
            $this->actingAs($admin)->putJson("/data/{$dim}/999999", $payload)->assertStatus(404);
            $this->t("{$dim} 404", true);
            // Hapus OK
            $this->actingAs($admin)->deleteJson("/data/{$dim}/{$id}")->assertOk();
            $this->actingAs($admin)->deleteJson("/data/{$dim}/{$id}")->assertStatus(404);
        }

        // Dimensi tak dikenal 404
        $this->actingAs($admin)->get('/data/ngawur')->assertNotFound();
        $this->t('dimensi ngawur 404', true);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_hitung_dan_laporan(): void
    {
        $admin = $this->admin();
        // Hitung ulang default tahun terbaru
        $this->actingAs($admin)->post('/hitung/ulang')->assertRedirect();
        $this->t('hitung ulang OK', true);
        // Tanpa data (tahun kosong) tidak bikin baris sampah
        $before = DB::table('ipo_summary')->count();
        $this->actingAs($admin)->post('/hitung/ulang?year=1999')->assertRedirect();
        $this->t('tanpa data tanpa baris', DB::table('ipo_summary')->count() === $before);
        // Halaman hitung/grafik/laporan
        $this->actingAs($admin)->get('/hitung?year=2024')->assertOk();
        $this->t('hitung 200', true);
        $this->actingAs($admin)->get('/grafik')->assertOk();
        $this->t('grafik 200', true);
        foreach (['index', 'ranking', 'trend', 'dimensions'] as $tab) {
            $this->actingAs($admin)->get("/laporan?tab={$tab}&year=2024")->assertOk();
        }
        $this->t('4 tab laporan', true);
        // PDF terunduh
        $r = $this->actingAs($admin)->get('/laporan/pdf?tab=ranking&year=2024')->assertOk();
        $this->t('PDF ranking', str_starts_with($r->headers->get('content-type'), 'application/pdf'));
        $r = $this->actingAs($admin)->get('/laporan/pdf?tab=index&year=2024&province_id=1')->assertOk();
        $this->t('PDF index', str_starts_with($r->headers->get('content-type'), 'application/pdf'));
        // Ranking 38 provinsi, terurut
        $rows = DB::table('ipo_summary')->where('year', 2024)->orderByDesc('ipo_score')->get();
        $this->t('ranking 38', $rows->count() === 38);
        $this->t('ranking urut', $rows->first()->ipo_score >= $rows->last()->ipo_score);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_users_dan_options(): void
    {
        $admin = $this->admin();
        // Buat + duplikat + ubah + hapus
        $id = $this->actingAs($admin)->postJson('/users', ['username' => 'tesopr1', 'password' => 'tes12345', 'full_name' => 'Tes', 'role' => 'operator', 'province_id' => 1])->assertOk()->json('id');
        $this->t('user dibuat', (bool) $id);
        $this->actingAs($admin)->postJson('/users', ['username' => 'tesopr1', 'password' => 'x', 'full_name' => 'X'])->assertStatus(422);
        $this->t('user duplikat 422', true);
        $this->actingAs($admin)->putJson("/users/{$id}", ['full_name' => 'Tes Ubah', 'role' => 'user', 'province_id' => null])->assertOk();
        $this->t('user diubah', DB::table('users')->where('id', $id)->value('role') === 'user');
        // Hapus diri sendiri ditolak
        $this->actingAs($admin)->deleteJson("/users/{$admin->id}")->assertStatus(400);
        $this->t('hapus diri 400', true);
        $this->actingAs($admin)->deleteJson("/users/{$id}")->assertOk();
        $this->t('user dihapus', DB::table('users')->where('id', $id)->doesntExist());
        $this->actingAs($admin)->deleteJson('/users/999999')->assertStatus(404);
        $this->t('user 404', true);

        // Options JSON
        foreach (['provinces', 'years', 'districts', 'villages', 'cities', 'respondents'] as $k) {
            $this->actingAs($admin)->getJson("/api/options/{$k}")->assertOk()->assertJsonStructure(['data']);
        }
        $this->t('6 options', true);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_fitur_batch_a(): void
    {
        $admin = $this->admin();
        $origHash = $admin->password_hash;
        // Password lama salah ditolak
        $this->actingAs($admin)->post('/profil/password', ['lama' => 'salah', 'baru' => 'baru1234', 'baru_confirmation' => 'baru1234'])->assertSessionHasErrors('lama');
        $this->t('password lama salah ditolak', true);
        // Ganti berhasil + hash baru valid
        $this->actingAs($admin)->post('/profil/password', ['lama' => 'admin123', 'baru' => 'baru1234', 'baru_confirmation' => 'baru1234'])->assertStatus(302);
        $this->t('password diganti', Hash::check('baru1234', $admin->fresh()->password_hash));
        DB::table('users')->where('id', $admin->id)->update(['password_hash' => $origHash]);
        // CSV ranking
        $csv = $this->actingAs($admin)->get('/laporan/csv?tab=ranking&year=2024');
        $csv->assertOk();
        $this->t('csv 200 + header', str_contains($csv->headers->get('Content-Type'), 'text/csv') && str_contains($csv->streamedContent(), 'Bali'));
        // Batas wajar: vo2max 999 ditolak
        $this->actingAs($admin)->postJson('/data/kebugaran', ['respondent_id' => 1, 'year' => 2024, 'vo2max' => 999])->assertStatus(422);
        $this->t('vo2max absurd 422', true);
        // Cari user + filter ranking
        $this->actingAs($admin)->get('/users?q=jatim')->assertOk()->assertSee('operator_jatim');
        $this->t('cari user', true);
        $this->actingAs($admin)->get('/laporan?tab=ranking&year=2024&q=Bali')->assertOk()->assertSee('Bali');
        $this->t('filter ranking', true);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }

    public function test_fitur_batch_bcd(): void
    {
        $admin = $this->admin();
        // Audit tercatat saat CRUD dimensi
        $id = $this->actingAs($admin)->postJson('/data/sdm', ['district_id' => 1, 'year' => 2024, 'jumlah_penduduk_5plus' => 50000, 'jumlah_sdm' => 100])->json('id');
        $this->t('audit tambah', DB::table('audit_logs')->where('tabel', 'sdm_olahraga')->where('row_id', $id)->where('aksi', 'tambah')->exists());
        $this->actingAs($admin)->putJson("/data/sdm/{$id}", ['district_id' => 1, 'year' => 2024, 'jumlah_penduduk_5plus' => 50000, 'jumlah_sdm' => 120])->assertOk();
        $this->t('audit ubah + riwayat', DB::table('audit_logs')->where('tabel', 'sdm_olahraga')->where('row_id', $id)->where('aksi', 'ubah')->exists());
        $this->actingAs($admin)->get("/data/sdm/{$id}/riwayat")->assertOk()->assertSee('120');
        $this->t('halaman riwayat', true);
        $this->actingAs($admin)->deleteJson("/data/sdm/{$id}")->assertOk();
        // Impor CSV: 1 baris baik + 1 baris rusak
        $csv = "district_id;year;jumlah_penduduk_5plus;jumlah_sdm\n1;2024;40000;90\n1;2024;-5;10\n";
        $tmp = tempnam(sys_get_temp_dir(), 'impor') . '.csv';
        file_put_contents($tmp, $csv);
        $r = $this->actingAs($admin)->post('/data/sdm/impor', [], ['HTTP_ACCEPT' => 'text/html']);
        unset($r);
        $resp = $this->actingAs($admin)->call('POST', '/data/sdm/impor', [], [], ['file' => new \Illuminate\Http\UploadedFile($tmp, 'x.csv', 'text/csv', UPLOAD_ERR_OK, true)]);
        $resp->assertRedirect('/data/sdm');
        $this->t('impor 1 ok 1 gagal', DB::table('sdm_olahraga')->where('jumlah_sdm', 90)->where('year', 2024)->exists());
        DB::table('sdm_olahraga')->where('jumlah_sdm', 90)->where('year', 2024)->delete();
        @unlink($tmp);
        // Banding + notifikasi + bahasa
        $this->actingAs($admin)->get('/banding?p[]=1&p[]=15&year=2024')->assertOk()->assertSee('Bali');
        $this->t('banding', true);
        $this->actingAs($admin)->get('/notifikasi')->assertOk();
        $this->t('notifikasi 200', true);
        $this->get('/bahasa/en')->assertRedirect();
        $this->t('ganti bahasa', session('locale') === 'en');
        $this->get('/bahasa/id');
        // CSV semua tab
        foreach (['index&province_id=1', 'trend&province_id=1', 'dimensions'] as $q) {
            $this->actingAs($admin)->get("/laporan/csv?tab={$q}&year=2024")->assertOk();
        }
        $this->t('csv 4 tab', true);
        echo "\nPASSED(error-free): {$this->passed}\n";
    }
}
