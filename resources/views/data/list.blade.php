@extends('layouts.app')
@section('title', $label . ' — IPO')

@section('content')
@php
  $u = auth()->user();
  $canWrite = in_array($u->role, ['admin', 'operator']);
  $cols = [
    'sdm' => [['k' => 'district_name', 'l' => 'Kecamatan'], ['k' => 'city_name', 'l' => 'Kota/Kab'], ['k' => 'jumlah_penduduk_5plus', 'l' => 'Penduduk 5+', 'n' => 1], ['k' => 'jumlah_sdm', 'l' => 'Jumlah SDM', 'n' => 1]],
    'ruang-terbuka' => [['k' => 'village_name', 'l' => 'Lokasi'], ['k' => 'district_name', 'l' => 'Kecamatan'], ['k' => 'luas_m2', 'l' => 'Luas (m²)', 'n' => 1], ['k' => 'jumlah_penduduk_5plus', 'l' => 'Penduduk 5+', 'n' => 1]],
    'literasi-fisik' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'pengetahuan', 'l' => 'Pengetahuan', 'd' => 1], ['k' => 'sikap', 'l' => 'Sikap', 'd' => 1], ['k' => 'perilaku', 'l' => 'Perilaku', 'd' => 1]],
    'partisipasi' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'frekuensi', 'l' => 'Frekuensi/mgg', 'n' => 1], ['k' => 'durasi', 'l' => 'Durasi (mnt)', 'n' => 1], ['k' => 'intensitas', 'l' => 'Intensitas', 'n' => 1]],
    'kebugaran' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'vo2max', 'l' => 'VO₂max', 'd' => 1], ['k' => 'kategori', 'l' => 'Kategori', 'b' => 1]],
    'kesehatan' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'fisik', 'l' => 'Fisik', 'd' => 1], ['k' => 'psikis', 'l' => 'Psikis', 'd' => 1]],
    'perkembangan-personal' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'resiliensi', 'l' => 'Resiliensi', 'd' => 1], ['k' => 'modal_sosial', 'l' => 'Modal Sosial', 'd' => 1]],
    'ekonomi' => [['k' => 'respondent_age', 'l' => 'Usia'], ['k' => 'respondent_gender', 'l' => 'JK'], ['k' => 'belanja_barang', 'l' => 'Brg (Rp)', 'r' => 1], ['k' => 'belanja_jasa', 'l' => 'Jasa (Rp)', 'r' => 1]],
    'performa' => [['k' => 'city_name', 'l' => 'Kota/Kab'], ['k' => 'province_name', 'l' => 'Provinsi'], ['k' => 'medali_emas', 'l' => 'Emas', 'n' => 1], ['k' => 'medali_perak', 'l' => 'Perak', 'n' => 1], ['k' => 'medali_perunggu', 'l' => 'Perunggu', 'n' => 1]],
    'responden' => [['k' => 'village_name', 'l' => 'Desa/Kelurahan'], ['k' => 'district_name', 'l' => 'Kecamatan'], ['k' => 'age', 'l' => 'Usia', 'n' => 1], ['k' => 'gender', 'l' => 'JK', 'b' => 1], ['k' => 'age_group', 'l' => 'Kelompok Usia']],
  ][$dim] ?? [];
  $fmt = function ($r, $c) {
    $v = $r->{$c['k']} ?? null;
    if ($v === null || $v === '') return '—';
    if (!empty($c['b'])) return '<span class="badge badge-neutral">' . e($v) . '</span>';
    if (!empty($c['n'])) return number_format($v, 0, ',', '.');
    if (!empty($c['d'])) return number_format($v, 2, ',', '.');
    if (!empty($c['r'])) return 'Rp ' . number_format($v, 0, ',', '.');
    return e($v);
  };
@endphp
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <div>
      <a href="/data" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
      <h1 style="font-size:18px;font-weight:700;color:var(--ink)">{{ $label }}</h1>
    </div>
    @if($canWrite)
      <a href="/data/{{ $dim }}/tambah?year={{ $year }}" class="btn btn-primary btn-sm no-print">+ Tambah</a>
      <a href="/data/{{ $dim }}/impor" class="btn btn-secondary btn-sm no-print">⭳ Impor CSV</a>
      <button type="submit" form="bulkForm" class="btn btn-danger btn-sm no-print" onclick="return confirm('Hapus semua baris terpilih?')">{{ __('Hapus terpilih') }}</button>
    @endif
  </div>

  <form method="GET" action="/data/{{ $dim }}" class="card no-print" style="padding:12px 14px;display:flex;gap:8px;flex-wrap:wrap">
    @if(count($provinces))
      <select name="province_id" class="input" style="width:auto" onchange="this.form.submit()">
        <option value="">Semua provinsi</option>
        @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (string)$provFilter === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
      </select>
    @endif
    <select name="year" class="input" style="width:auto" onchange="this.form.submit()">
      @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
    </select>
    <input name="q" class="input" style="max-width:220px" placeholder="Cari..." value="{{ $q }}">
    <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
  </form>

  <form id="bulkForm" method="POST" action="/data/{{ $dim }}/hapus-banyak">@csrf @method('DELETE')
  <div class="table-wrap">
    <table class="table">
      <thead><tr>@if($canWrite)<th class="no-print"><input type="checkbox" id="cekSemua" aria-label="Pilih semua"></th>@endif<th>#</th>@foreach($cols as $c)<th>@if(in_array($c['k'], $boleh))<a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => $c['k'], 'dir' => ($sort === $c['k'] && $dir === 'asc' ? 'desc' : 'asc')])) }}" style="color:inherit">{{ $c['l'] }}@if($sort === $c['k']){{ $dir === 'asc' ? ' ▲' : ' ▼' }}@endif</a>@else{{ $c['l'] }}@endif</th>@endforeach
      <tbody>
        @forelse($rows as $i => $r)
          <tr>
            @if($canWrite)<td class="no-print"><input type="checkbox" class="cekBaris" name="ids[]" value="{{ $r->id }}" aria-label="Pilih baris"></td>@endif
            <td>{{ $rows->firstItem() + $i }}</td>
            @foreach($cols as $c)<td>{!! $fmt($r, $c) !!}</td>@endforeach
            <td>@if(isset($r->indeks))<span class="badge badge-cukup">{{ (int) round($r->indeks * 100) }}</span>@else — @endif</td>
            @if($canWrite)
              <td class="no-print" style="white-space:nowrap">
                <a href="/data/{{ $dim }}/{{ $r->id }}/ubah" class="btn btn-secondary btn-sm">Ubah</a>
                <a href="/data/{{ $dim }}/{{ $r->id }}/riwayat" class="btn btn-secondary btn-sm" title="Riwayat perubahan">⏱</a>
                <button type="button" class="btn btn-danger btn-sm" onclick="askDelete('/data/{{ $dim }}/{{ $r->id }}')">Hapus</button>
              </td>
            @endif
          </tr>
        @empty
          <tr><td colspan="12" style="text-align:center;color:var(--ink-3);padding:28px">Belum ada data tahun {{ $year }}.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  </form>
  <div>{{ $rows->links() }}</div>
</div>

<div id="delModal" role="dialog" aria-modal="true" aria-label="Konfirmasi hapus" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:16px">
  <div style="position:absolute;inset:0;background:rgba(30,17,60,.55)" onclick="closeDelete()"></div>
  <div class="card anim-scale" style="position:relative;padding:24px;width:100%;max-width:380px">
    <h3 style="font-size:16px;font-weight:700;color:var(--ink)">Hapus data?</h3>
    <p style="font-size:13px;margin:8px 0 20px;color:var(--ink-2)">{{ __('Data masuk tong sampah dan bisa dipulihkan 30 hari.') }}</p>
    <div style="display:flex;gap:8px">
      <button type="button" class="btn btn-secondary" style="flex:1" onclick="closeDelete()">Batal</button>
      <form id="delForm" method="POST" style="flex:1;display:flex">@csrf @method('DELETE')
        <button type="submit" class="btn" style="flex:1;background:var(--red);color:#fff">Hapus</button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function askDelete(url) {
  document.getElementById('delForm').action = url;
  document.getElementById('delModal').style.display = 'flex';
  const f = document.querySelector('#delModal button[type=submit]');
  if (f) setTimeout(() => f.focus(), 60);
}
function closeDelete() { document.getElementById('delModal').style.display = 'none'; }
document.getElementById('cekSemua')?.addEventListener('change', function () {
  document.querySelectorAll('.cekBaris').forEach(c => { c.checked = this.checked; });
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDelete(); });
</script>
@endpush
