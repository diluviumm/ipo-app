@extends('layouts.app')
@section('title', 'Perhitungan Indeks — IPO')

@section('content')
@php
  $u = auth()->user();
  $canRecalc = in_array($u->role, ['admin', 'operator']);
  $katBg = ['Sangat Kurang' => 'var(--orange-100)', 'Kurang' => 'var(--danger-light)', 'Cukup' => 'var(--warning-light)', 'Baik' => 'var(--success-light)'][$kategori] ?? 'var(--neutral-200)';
  $katFg = ['Sangat Kurang' => 'var(--orange)', 'Kurang' => 'var(--danger)', 'Cukup' => '#B45309', 'Baik' => 'var(--success)'][$kategori] ?? 'var(--ink-3)';
@endphp
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:16px">
  <div class="page-head">
    <div>
      <a href="/dashboard" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
      <h1 style="font-size:18px;font-weight:600;color:var(--ink)">Perhitungan Indeks</h1>
    </div>
    <form method="GET" action="/hitung" class="toolbar no-print">
      @if(count($provinces))
        <select name="province_id" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()" title="Pilih provinsi">
          @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (int)$pid === (int)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
        </select>
      @endif
      <select name="year" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()">
        @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select>
      @if($canRecalc)
        <button type="button" class="btn btn-primary btn-sm" onclick="openRecalc()">⟳ Hitung Ulang</button>
      @else
        <span style="font-size:11.5px;color:var(--ink-3)">Mode lihat saja</span>
      @endif
    </form>
  </div>
  @if($canRecalc)
    <div id="recalcModal" role="dialog" aria-modal="true" aria-label="Konfirmasi hitung ulang" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:16px">
      <div style="position:absolute;inset:0;background:rgba(30,17,60,.55)" onclick="closeRecalc()"></div>
      <div class="card anim-scale" style="position:relative;padding:24px;width:100%;max-width:380px">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink)">Hitung ulang?</h3>
        <p style="font-size:13px;margin:8px 0 20px;color:var(--ink-2)">Skor ringkasan akan ditulis ulang dari data sumber.</p>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn btn-secondary" style="flex:1" onclick="closeRecalc()">Batal</button>
          <form method="POST" action="/hitung/ulang?year={{ $year }}&province_id={{ $pid }}" style="flex:1;display:flex">@csrf
            <button type="submit" class="btn btn-primary" style="flex:1">Ya, hitung</button>
          </form>
        </div>
      </div>
    </div>
    <script>
    function openRecalc(){document.getElementById('recalcModal').style.display='flex';}
    function closeRecalc(){document.getElementById('recalcModal').style.display='none';}
    </script>
  @endif

  <div class="card" style="padding:20px">
    <div style="display:flex;gap:16px;align-items:center">
      <div style="width:64px;height:64px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:var(--accent-light);color:var(--accent);font-size:28px;flex-shrink:0">🧮</div>
      <div>
        <p style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3)">IPO Score — {{ $provinceName }} {{ $year }}</p>
        <p style="font-size:28px;font-weight:700;color:var(--ink)">{{ number_format($score, $score == round($score) ? 0 : 2, ',', '.') }} <span style="font-size:14px;font-weight:400;color:var(--ink-3)">/100</span></p>
        <span class="badge" style="margin-top:4px" data-bg="{{ $katBg }}" data-color="{{ $katFg }}">{{ $kategori }}</span>
      </div>
    </div>
    @if($tanpaData)<p style="font-size:12px;margin-top:12px;color:var(--ink-3)">Belum ada data sumber untuk provinsi &amp; tahun ini.</p>@endif
  </div>

  <div class="card" style="padding:20px">
    <h3 style="font-size:14px;font-weight:600;margin-bottom:16px;color:var(--ink)">Rincian Dimensi</h3>
    <div style="display:flex;flex-direction:column;gap:12px">
      @foreach($dims as $d)
        @php($barCls = $d['display'] >= 51 ? 'bar-green' : ($d['display'] >= 26 ? 'bar-amber' : 'bar-red'))
        <div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px">
            <span style="font-size:13px;color:var(--ink)">{{ $d['label'] }}</span>
            <span style="font-size:12px;color:var(--ink-2)">{{ $d['display'] }}%</span>
          </div>
          <div class="progress"><div class="{{ $barCls }}" data-w="{{ $d['display'] }}"></div></div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
