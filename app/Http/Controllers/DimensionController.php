<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use App\Support\Audit;
use App\Support\Dimensions;
use App\Support\Sampah;
use App\Support\TahunTerkunci;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DimensionController extends Controller
{
    private function cfg(string $dim): array
    {
        $c = Dimensions::get($dim);
        abort_if(!$c, 404, "Dimensi '{$dim}' tidak ditemukan");
        return $c;
    }

    private function latestYear(): int
    {
        return IpoCalculator::latestYear();
    }

    public function menu()
    {
        $segar = [];
        foreach (Dimensions::keys() as $k) {
            $cfg = Dimensions::get($k);
            if (!$cfg) continue;
            $segar[$k] = DB::table('audit_logs')->where('tabel', $cfg['table'])->max('created_at');
        }
        return view('data.menu', ['segar' => $segar]);
    }

    // Daftar baris + filter tahun/provinsi/pencarian, di-scope ke provinsi operator.
    public function index(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $t = $c['table'];
        $year = (int) ($request->query('year') ?: $this->latestYear());
        $u = $request->user();
        $provFilter = $u->province_id ?: ($request->query('province_id') ?: null);

        $q = DB::table("{$t} as x")->select('x.*');
        $this->applyJoins($q, $dim);
        $q->where('x.year', $year);
        $this->applyScope($q, $dim, $provFilter ? (int) $provFilter : null);
        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(function ($w) use ($s) {
                $w->where('d.name', 'like', "%{$s}%")->orWhere('c.name', 'like', "%{$s}%");
            });
        }
        $boleh = array_merge(['id'], $c['fields']);
        $sort = $request->query('sort', 'id');
        if (!in_array($sort, $boleh, true)) $sort = 'id';
        $dir = strtolower((string) $request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($request->has('sort')) {
            $request->session()->put("sortir_{$dim}", [$sort, $dir]);
        } elseif ($ingat = $request->session()->get("sortir_{$dim}")) {
            [$sort, $dir] = [$ingat[0], $ingat[1]];
        }
        $rows = $q->orderBy("x.{$sort}", $dir)->paginate(20)->withQueryString();

        return view('data.list', [
            'dim' => $dim, 'label' => $c['label'], 'rows' => $rows, 'year' => $year,
            'years' => $this->yearOptions(), 'provinces' => $this->provinceOptions($u),
            'provFilter' => $provFilter, 'q' => $request->query('q', ''),
            'sort' => $sort, 'dir' => $dir, 'boleh' => $boleh,
        ]);
    }

    public function create(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $u = $request->user();
        return view('data.form', [
            'dim' => $dim, 'label' => $c['label'], 'row' => null,
            'year' => (int) ($request->query('year') ?: $this->latestYear()),
            'opts' => $this->formOptions($dim, $u, $request->query('province_id')),
            'provFilter' => $u->province_id ?: $request->query('province_id'),
            'provinces' => $this->provinceOptions($u),
        ]);
    }

    public function store(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $data = $this->validateInput($request, $c);
        if (!TahunTerkunci::boleh($request->user(), (int) $data['year'])) {
            return $this->deny($request, 'Tahun ' . $data['year'] . ' terkunci (arsip). Hubungi superadmin.');
        }
        if (!Dimensions::assertOwnProvince($request->user(), $dim, $data)) {
            return $this->deny($request, 'Tidak bisa mengelola data provinsi lain');
        }
        foreach (Dimensions::derived($dim, $data) as $k => $v) $data[$k] = $v;
        if (in_array($dim, ['sdm', 'ruang-terbuka'], true)) $data['created_by'] = $request->user()->id;
        $id = DB::table($c['table'])->insertGetId($data);
        Audit::catat($request->user(), 'tambah', $c['table'], $id, ['dim' => $dim, 'data' => $data]);
        if ($request->expectsJson()) return response()->json(['id' => $id, 'message' => __('Data berhasil ditambahkan')]);
        return redirect("/data/{$dim}")->with('toast', ['type' => 'success', 'text' => __('Data berhasil ditambahkan.')]);
    }

    public function edit(Request $request, string $dim, int $id)
    {
        $c = $this->cfg($dim);
        $row = DB::table($c['table'])->where('id', $id)->first();
        abort_if(!$row, 404, 'Data tidak ditemukan');
        $u = $request->user();
        return view('data.form', [
            'dim' => $dim, 'label' => $c['label'], 'row' => (array) $row,
            'year' => $row->year,
            'opts' => $this->formOptions($dim, $u, null, (array) $row),
            'provFilter' => $u->province_id,
            'provinces' => $this->provinceOptions($u),
        ]);
    }

    public function update(Request $request, string $dim, int $id)
    {
        $c = $this->cfg($dim);
        $existing = DB::table($c['table'])->where('id', $id)->first();
        if (!$existing) {
            if ($request->expectsJson()) return response()->json(['error' => __('Data tidak ditemukan')], 404);
            abort(404, __('Data tidak ditemukan'));
        }
        $data = $this->validateInput($request, $c);
        $merged = array_merge((array) $existing, $data);
        if (!TahunTerkunci::boleh($request->user(), (int) $merged['year'])) {
            return $this->deny($request, 'Tahun ' . $merged['year'] . ' terkunci (arsip). Hubungi superadmin.');
        }
        if (!Dimensions::assertOwnProvince($request->user(), $dim, $merged)) {
            return $this->deny($request, 'Tidak bisa mengelola data provinsi lain');
        }
        foreach (Dimensions::derived($dim, $merged) as $k => $v) $data[$k] = $v;
        unset($data['created_by']);
        DB::table($c['table'])->where('id', $id)->update($data);
        Audit::catat($request->user(), 'ubah', $c['table'], $id, ['dim' => $dim, 'lama' => (array) $existing, 'baru' => $data]);
        if ($request->expectsJson()) return response()->json(['message' => __('Data berhasil diperbarui')]);
        return redirect("/data/{$dim}")->with('toast', ['type' => 'success', 'text' => __('Data berhasil diperbarui.')]);
    }

    public function riwayat(Request $request, string $dim, int $id)
    {
        $c = $this->cfg($dim);
        $logs = DB::table('audit_logs')->where('tabel', $c['table'])->where('row_id', $id)->orderByDesc('id')->limit(50)->get();
        if ($request->expectsJson()) return response()->json(['data' => $logs]);
        return view('data.riwayat', ['dim' => $dim, 'label' => $c['label'], 'id' => $id, 'logs' => $logs]);
    }

    public function impor(string $dim)
    {
        $c = $this->cfg($dim);
        return view('data.impor', ['dim' => $dim, 'label' => $c['label'], 'fields' => $c['fields']]);
    }

    public function contoh(string $dim)
    {
        $c = $this->cfg($dim);
        return response()->streamDownload(function () use ($c) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $c['fields'], ';');
            fclose($out);
        }, "contoh-{$dim}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    public function prosesImpor(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $request->validate(['file' => 'required|file|max:2048']);
        $h = fopen($request->file('file')->getRealPath(), 'r');
        [$rules, $messages] = self::aturanUntuk($c);
        $head = null;
        $ok = 0;
        $gagal = [];
        $tampung = [];
        $baris = 0;
        $pratinjau = $request->input('aksi') === 'pratinjau';
        while (($row = fgetcsv($h, 0, ';')) !== false) {
            if ($head === null) {
                $head = array_map('trim', $row);
                $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]);
                continue;
            }
            $kosong = true;
            foreach ($row as $v) if (trim((string) $v) !== '') $kosong = false;
            if ($kosong) continue;
            if (++$baris > 2000) {
                fclose($h);
                return back()->withErrors(['file' => __('Maksimal 2.000 baris per impor. Pecah file menjadi beberapa bagian.')]);
            }
            $no = $ok + count($gagal) + 2;
            $data = array_combine($head, array_slice(array_pad($row, count($head), ''), 0, count($head)));
            $v = Validator::make($data, $rules, $messages);
            if ($v->fails()) { $gagal[] = "Baris {$no}: " . $v->errors()->first(); continue; }
            $data = $v->validated();
            if (!TahunTerkunci::boleh($request->user(), (int) $data['year'])) { $gagal[] = "Baris {$no}: tahun {$data['year']} terkunci (arsip)"; continue; }
            if (!Dimensions::assertOwnProvince($request->user(), $dim, $data)) { $gagal[] = "Baris {$no}: di luar provinsi Anda"; continue; }
            foreach (Dimensions::derived($dim, $data) as $k => $vv) $data[$k] = $vv;
            if (in_array($dim, ['sdm', 'ruang-terbuka'], true)) $data['created_by'] = $request->user()->id;
            if ($pratinjau) { $tampung[] = $data; continue; }
            $id = DB::table($c['table'])->insertGetId($data);
            Audit::catat($request->user(), 'tambah', $c['table'], $id, ['dim' => $dim, 'impor' => true]);
            $ok++;
        }
        fclose($h);
        if ($pratinjau) {
            $request->session()->put("impor_{$dim}", array_slice($tampung, 0, 1000));
            return view('data.pratinjau', ['dim' => $dim, 'label' => $c['label'], 'fields' => $c['fields'], 'valid' => array_slice($tampung, 0, 20), 'jmlValid' => count($tampung), 'gagal' => $gagal]);
        }
        $teks = "Impor selesai: {$ok} berhasil";
        if ($gagal) $teks .= ', ' . count($gagal) . ' gagal: ' . implode(' | ', array_slice($gagal, 0, 3));
        else $teks .= '.';
        return redirect("/data/{$dim}")->with('toast', ['type' => $ok ? 'success' : 'error', 'text' => $teks]);
    }

    public function konfirmasiImpor(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $rows = $request->session()->pull("impor_{$dim}", []);
        $ok = 0;
        foreach ($rows as $data) {
            $id = DB::table($c['table'])->insertGetId($data);
            Audit::catat($request->user(), 'tambah', $c['table'], $id, ['dim' => $dim, 'impor' => true]);
            $ok++;
        }
        return redirect("/data/{$dim}")->with('toast', ['type' => 'success', 'text' => "Impor dikonfirmasi: {$ok} baris masuk."]);
    }

    public function hapusBanyak(Request $request, string $dim)
    {
        $c = $this->cfg($dim);
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
        $ids = array_slice(array_filter($ids), 0, 100);
        if (!$ids) {
            return redirect("/data/{$dim}")->with('toast', ['type' => 'error', 'text' => __('Tidak ada baris yang dipilih.')]);
        }
        $n = 0;
        foreach ($ids as $id) {
            $row = DB::table($c['table'])->where('id', $id)->first();
            if (!$row) continue;
            if (!TahunTerkunci::boleh($request->user(), (int) $row->year)) continue;
            if (!Dimensions::assertOwnProvince($request->user(), $dim, (array) $row)) continue;
            Sampah::buang($request->user(), $c['table'], $dim, $id, (array) $row);
            DB::table($c['table'])->where('id', $id)->delete();
            Audit::catat($request->user(), 'hapus', $c['table'], $id, ['dim' => $dim, 'massal' => true]);
            $n++;
        }
        return redirect("/data/{$dim}")->with('toast', ['type' => 'success', 'text' => "{$n} " . __('baris dihapus.')]);
    }

    public function destroy(Request $request, string $dim, int $id)
    {
        $c = $this->cfg($dim);
        $existing = DB::table($c['table'])->where('id', $id)->first();
        if (!$existing) {
            if ($request->expectsJson()) return response()->json(['error' => __('Data tidak ditemukan')], 404);
            abort(404, __('Data tidak ditemukan'));
        }
        if (!Dimensions::assertOwnProvince($request->user(), $dim, (array) $existing)) {
            return $this->deny($request, 'Tidak bisa mengelola data provinsi lain');
        }
        if (!TahunTerkunci::boleh($request->user(), (int) $existing->year)) {
            return $this->deny($request, 'Tahun ' . $existing->year . ' terkunci (arsip). Hubungi superadmin.');
        }
        Sampah::buang($request->user(), $c['table'], $dim, $id, (array) $existing);
        DB::table($c['table'])->where('id', $id)->delete();
        Audit::catat($request->user(), 'hapus', $c['table'], $id, ['dim' => $dim, 'data' => (array) $existing]);
        if ($request->expectsJson()) return response()->json(['message' => __('Data berhasil dihapus')]);
        return redirect("/data/{$dim}")->with('toast', ['type' => 'success', 'text' => __('Data berhasil dihapus.')]);
    }

    // JSON untuk dropdown dinamis form (kecamatan/desa/kota/responden).
    public function options(Request $request, string $kind)
    {
        $u = $request->user();
        $prov = $u->province_id ?: $request->query('province_id');
        switch ($kind) {
            case 'provinces':
                return response()->json(['data' => DB::table('provinces')->orderBy('name')->get()]);
            case 'years':
                $tables = ['sdm_olahraga', 'ruang_terbuka', 'literasi_fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan_personal', 'ekonomi', 'performa', 'respondents'];
                $set = [];
                foreach ($tables as $t) {
                    foreach (DB::table($t)->distinct()->pluck('year') as $y) $set[$y] = true;
                }
                $years = array_keys($set);
                rsort($years);
                return response()->json(['data' => $years ?: [$this->latestYear()]]);
            case 'districts':
                $q = DB::table('districts as d')->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->join('provinces as p', 'p.id', '=', 'c.province_id')
                    ->select('d.id', 'd.name', 'd.city_id', 'c.name as city_name', 'c.province_id', 'p.name as province_name');
                if ($prov) $q->where('c.province_id', $prov);
                if ($request->query('city_id')) $q->where('d.city_id', $request->query('city_id'));
                if ($s = $request->query('search')) $q->where(fn($w) => $w->where('d.name', 'like', "%{$s}%")->orWhere('c.name', 'like', "%{$s}%"));
                return response()->json(['data' => $q->orderBy('p.name')->orderBy('c.name')->orderBy('d.name')->limit(500)->get()]);
            case 'villages':
                $q = DB::table('villages as v')->join('districts as d', 'd.id', '=', 'v.district_id')
                    ->join('cities as c', 'c.id', '=', 'd.city_id')->join('provinces as p', 'p.id', '=', 'c.province_id')
                    ->select('v.id', 'v.name', 'v.district_id', 'd.name as district_name', 'd.city_id', 'c.name as city_name', 'c.province_id', 'p.name as province_name');
                if ($prov) $q->where('c.province_id', $prov);
                if ($request->query('district_id')) $q->where('v.district_id', $request->query('district_id'));
                if ($s = $request->query('search')) $q->where(fn($w) => $w->where('v.name', 'like', "%{$s}%")->orWhere('d.name', 'like', "%{$s}%"));
                return response()->json(['data' => $q->orderBy('p.name')->orderBy('c.name')->orderBy('d.name')->orderBy('v.name')->limit(500)->get()]);
            case 'cities':
                $q = DB::table('cities as c')->join('provinces as p', 'p.id', '=', 'c.province_id')
                    ->select('c.id', 'c.name', 'c.province_id', 'p.name as province_name');
                if ($prov) $q->where('c.province_id', $prov);
                if ($s = $request->query('search')) $q->where(fn($w) => $w->where('c.name', 'like', "%{$s}%")->orWhere('p.name', 'like', "%{$s}%"));
                return response()->json(['data' => $q->orderBy('p.name')->orderBy('c.name')->limit(500)->get()]);
            case 'respondents':
                $y = $request->query('year') ?: $this->latestYear();
                $q = DB::table('respondents as r')->join('villages as v', 'v.id', '=', 'r.village_id')
                    ->join('districts as d', 'd.id', '=', 'v.district_id')->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->join('provinces as p', 'p.id', '=', 'c.province_id')
                    ->select('r.id', 'r.age', 'r.gender', 'r.age_group', 'r.village_id', 'v.name as village_name', 'v.district_id', 'd.name as district_name', 'd.city_id', 'c.name as city_name', 'c.province_id', 'p.name as province_name')
                    ->where('r.year', $y);
                if ($prov) $q->where('c.province_id', $prov);
                if ($request->query('gender')) $q->where('r.gender', $request->query('gender'));
                if ($s = $request->query('search')) $q->where(fn($w) => $w->where('v.name', 'like', "%{$s}%")->orWhere('d.name', 'like', "%{$s}%")->orWhere('c.name', 'like', "%{$s}%"));
                return response()->json(['data' => $q->orderBy('p.name')->orderBy('c.name')->orderBy('d.name')->orderBy('v.name')->orderBy('r.id')->limit(500)->get()]);
        }
        abort(404);
    }

    private function deny(Request $request, string $msg)
    {
        if ($request->expectsJson()) return response()->json(['error' => $msg], 403);
        abort(403, $msg);
    }

    private function validateInput(Request $request, array $c): array
    {
        [$rules, $messages] = self::aturanUntuk($c);
        return $request->validate($rules, $messages);
    }

    public static function aturanUntuk(array $c): array
    {
        $rules = [];
        foreach ($c['fields'] as $f) {
            if ($f === 'year') $rules[$f] = 'required|integer|min:2000|max:2100';
            elseif ($f === 'district_id') $rules[$f] = 'required|integer|min:1|exists:districts,id';
            elseif ($f === 'village_id') $rules[$f] = 'required|integer|min:1|exists:villages,id';
            elseif ($f === 'city_id') $rules[$f] = 'required|integer|min:1|exists:cities,id';
            elseif ($f === 'respondent_id') $rules[$f] = 'required|integer|min:1|exists:respondents,id';
            elseif ($f === 'gender') $rules[$f] = 'required|in:L,P';
            elseif ($f === 'age') $rules[$f] = 'required|integer|min:10|max:60';
            elseif (in_array($f, $c['numerics'], true)) $rules[$f] = 'required|numeric|min:0|max:' . self::batasWajar($f);
            else $rules[$f] = 'required';
        }
        $messages = [
            'required' => 'Semua field wajib diisi', 'min' => 'Nilai tidak boleh negatif',
            'max' => 'Nilai di luar batas wajar — periksa kembali',
            'exists' => 'ID rujukan tidak ditemukan di data induk',
            'in' => 'Nilai tidak valid', 'integer' => 'Nilai harus bilangan bulat', 'numeric' => 'Nilai harus angka',
        ];
        return [$rules, $messages];
    }

    private static function batasWajar(string $f): int
    {
        return match ($f) {
            'jumlah_penduduk_5plus' => 20000000,
            'jumlah_sdm' => 500000,
            'luas_m2' => 100000000,
            'pengetahuan', 'sikap', 'perilaku', 'fisik', 'psikis', 'resiliensi', 'modal_sosial' => 5,
            'vo2max' => 90,
            'belanja_barang', 'belanja_jasa' => 100000000000,
            'medali_emas', 'medali_perak', 'medali_perunggu' => 10000,
            'frekuensi' => 100,
            'durasi' => 10000,
            'intensitas' => 100,
            default => 1000000000,
        };
    }

    private function applyJoins($q, string $dim): void
    {
        switch ($dim) {
            case 'sdm':
                $q->join('districts as d', 'd.id', '=', 'x.district_id')->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->addSelect('d.name as district_name', 'c.name as city_name');
                break;
            case 'ruang-terbuka':
                $q->join('villages as v', 'v.id', '=', 'x.village_id')->join('districts as d', 'd.id', '=', 'v.district_id')
                    ->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->addSelect('v.name as village_name', 'd.name as district_name', 'c.name as city_name');
                break;
            case 'performa':
                $q->join('cities as c', 'c.id', '=', 'x.city_id')->join('provinces as p', 'p.id', '=', 'c.province_id')
                    ->addSelect('c.name as city_name', 'p.name as province_name');
                break;
            case 'responden':
                $q->join('villages as v', 'v.id', '=', 'x.village_id')->join('districts as d', 'd.id', '=', 'v.district_id')
                    ->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->addSelect('v.name as village_name', 'd.name as district_name', 'c.name as city_name');
                break;
            default:
                $q->join('respondents as r', 'r.id', '=', 'x.respondent_id')
                    ->addSelect('r.age as respondent_age', 'r.gender as respondent_gender');
        }
    }

    private function applyScope($q, string $dim, ?int $prov): void
    {
        if (!$prov) return;
        if ($dim === 'responden') {
            $q->join('villages as v', 'v.id', '=', 'x.village_id')->join('districts as d', 'd.id', '=', 'v.district_id')
                ->join('cities as c', 'c.id', '=', 'd.city_id')->where('c.province_id', $prov);
        } elseif (in_array($dim, ['literasi-fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan-personal', 'ekonomi'], true)) {
            $q->whereIn('x.respondent_id', function ($sq) use ($prov) {
                $sq->select('r.id')->from('respondents as r')->join('villages as v', 'v.id', '=', 'r.village_id')
                    ->join('districts as d', 'd.id', '=', 'v.district_id')->join('cities as c', 'c.id', '=', 'd.city_id')
                    ->where('c.province_id', $prov);
            });
        } else {
            $q->where('c.province_id', $prov);
        }
    }

    private function yearOptions(): array
    {
        $tables = ['sdm_olahraga', 'ruang_terbuka', 'literasi_fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan_personal', 'ekonomi', 'performa', 'respondents'];
        $set = [];
        foreach ($tables as $t) {
            foreach (DB::table($t)->distinct()->pluck('year') as $y) $set[$y] = true;
        }
        $years = array_keys($set);
        rsort($years);
        return $years ?: [$this->latestYear()];
    }

    private function provinceOptions($u)
    {
        if ($u->province_id) return [];
        return DB::table('provinces')->orderBy('name')->get();
    }

    private function formOptions(string $dim, $u, $provFilter = null, array $row = []): array
    {
        $opts = [];
        $prov = $u->province_id ?: $provFilter;
        if (in_array($dim, ['sdm'], true)) {
            $q = DB::table('districts as d')->join('cities as c', 'c.id', '=', 'd.city_id')
                ->select('d.id', 'd.name', 'c.name as city_name');
            if ($prov) $q->where('c.province_id', $prov);
            $opts['districts'] = $q->orderBy('c.name')->orderBy('d.name')->limit(500)->get();
        }
        if (in_array($dim, ['ruang-terbuka', 'responden'], true)) {
            $q = DB::table('villages as v')->join('districts as d', 'd.id', '=', 'v.district_id')
                ->join('cities as c', 'c.id', '=', 'd.city_id')->select('v.id', 'v.name', 'd.name as district_name', 'c.name as city_name');
            if ($prov) $q->where('c.province_id', $prov);
            $opts['villages'] = $q->orderBy('c.name')->orderBy('d.name')->orderBy('v.name')->limit(500)->get();
        }
        if ($dim === 'performa') {
            $q = DB::table('cities as c')->join('provinces as p', 'p.id', '=', 'c.province_id')->select('c.id', 'c.name', 'p.name as province_name');
            if ($prov) $q->where('c.province_id', $prov);
            $opts['cities'] = $q->orderBy('p.name')->orderBy('c.name')->limit(500)->get();
        }
        if (in_array($dim, ['literasi-fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan-personal', 'ekonomi'], true)) {
            $y = $row['year'] ?? $this->latestYear();
            $q = DB::table('respondents as r')->join('villages as v', 'v.id', '=', 'r.village_id')
                ->join('districts as d', 'd.id', '=', 'v.district_id')->join('cities as c', 'c.id', '=', 'd.city_id')
                ->select('r.id', 'r.age', 'r.gender', 'v.name as village_name', 'c.name as city_name')->where('r.year', $y);
            if ($prov) $q->where('c.province_id', $prov);
            $opts['respondents'] = $q->orderBy('c.name')->orderBy('r.id')->limit(500)->get();
        }
        return $opts;
    }
}
