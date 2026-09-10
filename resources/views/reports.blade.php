@extends('layouts.app')
@section('title', 'Laporan — IPO')

@section('content')
@php
  $tabs = ['index' => 'Indeks IPO', 'ranking' => 'Peringkat Provinsi', 'trend' => 'Tren Indeks', 'dimensions' => 'Analisis Dimensi'];
  $katClass = ['Baik' => 'badge-baik', 'Cukup' => 'badge-cukup', 'Kurang' => 'badge-kurang', 'Sangat Kurang' => 'badge-sangat-kurang'];
  $q = http_build_query(array_filter(['tab' => $tab, 'year' => $year, 'province_id' => $pid]));
@endphp
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <div>
      <a href="/dashboard" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
      <h1 style="font-size:18px;font-weight:600;color:var(--ink)">Laporan</h1>
    </div>
    <div class="toolbar no-print">
      @if(count($provinces))
        <form method="GET" action="/laporan" style="display:inline"><input type="hidden" name="tab" value="{{ $tab }}"><input type="hidden" name="year" value="{{ $year }}">
          <select name="province_id" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()" title="Pilih provinsi">
            @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (int)$pid === (int)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
          </select>
        </form>
      @endif
      <form method="GET" action="/laporan" style="display:inline"><input type="hidden" name="tab" value="{{ $tab }}"><input type="hidden" name="province_id" value="{{ $pid }}">
        <select name="year" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()">
          @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
        </select>
      </form>
      <a href="/laporan/pdf?{{ $q }}" class="btn btn-primary btn-sm">Ekspor PDF</a>
      <a href="/laporan/csv?{{ $q }}" class="btn btn-secondary btn-sm">Ekspor CSV</a>
      <a href="/laporan/xlsx?{{ $q }}" class="btn btn-secondary btn-sm">{{ __('Ekspor Excel') }}</a>
      <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">Cetak</button>
    </div>
  </div>

  <div class="card no-print" style="padding:8px;display:flex;gap:6px;flex-wrap:wrap">
    @foreach($tabs as $k => $label)
      <a href="/laporan?tab={{ $k }}&year={{ $year }}&province_id={{ $pid }}" class="btn btn-sm {{ $tab === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
    @endforeach
  </div>

  @if($tab === 'index' && isset($row))
    <div class="card" style="padding:24px;text-align:center" id="ctk-indeks">
      <div class="no-print" style="text-align:right"><button type="button" class="btn btn-secondary btn-sm" onclick="cetakBagian('ctk-indeks')">🖨 Bagian</button></div>
      <div style="font-size:44px;font-weight:800;color:var(--brand-700)">{{ number_format($score, $score == round($score) ? 0 : 2, ',', '.') }}<span style="font-size:16px;color:var(--ink-3)">/100</span></div>
      <h2 style="font-size:16px;font-weight:700;margin-top:6px;color:var(--ink)">Laporan Indeks IPO — {{ $provinceName }}</h2>
      <p style="font-size:12.5px;color:var(--ink-3)">Tahun {{ $year }}</p>
      <p style="margin-top:8px"><span class="badge {{ $katClass[$row->kategori] ?? 'badge-neutral' }}">{{ $row->kategori }}</span></p>
    </div>
  @elseif($tab === 'ranking' && isset($ranking))
    <div class="card" style="padding:20px" id="ctk-ranking">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
      <h2 style="font-size:15px;font-weight:700;color:var(--ink)">Peringkat Provinsi</h2>
      <button type="button" class="btn btn-secondary btn-sm no-print" onclick="cetakBagian('ctk-ranking')">🖨 Bagian</button>
      </div>
      <p style="font-size:12px;color:var(--ink-3)">Tahun {{ $year }}</p>
      <form method="GET" action="/laporan" class="toolbar no-print" style="margin-top:10px">
        <input type="hidden" name="tab" value="ranking">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="text" name="q" class="input" style="max-width:220px" placeholder="Filter provinsi…" value="{{ $rq ?? '' }}">
        <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
      </form>
      <div class="table-wrap" style="margin-top:12px;border:none">
        <table class="table">
          <thead><tr><th>NO</th><th><a href="/laporan?tab=ranking&year={{ $year }}&q={{ urlencode($rq) }}&urut=nama" style="color:inherit">PROVINSI @if($urut === 'nama')▲@endif</a></th><th><a href="/laporan?tab=ranking&year={{ $year }}&q={{ urlencode($rq) }}" style="color:inherit">SKOR @if($urut === 'skor')▼@endif</a></th><th>KATEGORI</th></tr></thead>
          <tbody>
            @foreach($ranking as $i => $r)
              <tr><td>{{ $i + 1 }}</td><td>{{ $r->province_name }}</td><td style="font-weight:700">{{ (int) round($r->ipo_score * 100) }}</td><td><span class="badge {{ $katClass[$r->kategori] ?? 'badge-neutral' }}">{{ $r->kategori }}</span></td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @elseif($tab === 'trend' && isset($trend))
    <div class="card" style="padding:20px" id="ctk-tren">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
      <h2 style="font-size:15px;font-weight:700;color:var(--ink)">Tren Indeks — {{ $trendLabel }}</h2>
      <button type="button" class="btn btn-secondary btn-sm no-print" onclick="cetakBagian('ctk-tren')">🖨 Bagian</button>
      </div>
      <div class="table-wrap" style="margin-top:12px;border:none">
        <table class="table">
          <thead><tr><th>TAHUN</th><th>SKOR</th><th>KATEGORI</th></tr></thead>
          <tbody>
            @foreach($trend as $t)
              @php($s = (int) round($t->ipo_score * 100))
              @php($k = $s >= 76 ? 'Baik' : ($s >= 51 ? 'Cukup' : ($s >= 26 ? 'Kurang' : 'Sangat Kurang')))
              <tr><td>{{ $t->year }}</td><td style="font-weight:700">{{ $s }}</td><td><span class="badge {{ $katClass[$k] }}">{{ $k }}</span></td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @elseif($tab === 'dimensions' && isset($dims))
    <div class="card" style="padding:20px" id="ctk-dimensi">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
      <h2 style="font-size:15px;font-weight:700;color:var(--ink)">Analisis Dimensi — Tahun {{ $year }}</h2>
      <button type="button" class="btn btn-secondary btn-sm no-print" onclick="cetakBagian('ctk-dimensi')">🖨 Bagian</button>
      </div>
      <div class="table-wrap" style="margin-top:12px;border:none">
        <table class="table">
          <thead><tr><th>PROVINSI</th><th>SDM</th><th>RT</th><th>LIT</th><th>PART</th><th>BUGAR</th><th>KES</th><th>PP</th><th>EKO</th><th>PERF</th><th>SKOR</th></tr></thead>
          <tbody>
            @foreach($dims as $d)
              <tr><td>{{ $d->province_name }}</td>
              @foreach(['d1_sdm','d2_ruang_terbuka','d3_literasi_fisik','d4_partisipasi','d5_kebugaran','d6_kesehatan','d7_perkembangan_personal','d8_ekonomi','d9_performa'] as $k)<td>{{ (int) round($d->$k * 100) }}</td>@endforeach
              <td style="font-weight:700">{{ (int) round($d->ipo_score * 100) }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection
