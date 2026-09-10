@extends('layouts.app')
@section('title', 'Dashboard — IPO')

@section('content')
@php
  $u = auth()->user();
  $roleLabel = $u->role === 'admin' && $u->province_id === null ? 'Administrator' : ($u->role === 'operator' ? 'Operator' : ($u->role === 'admin' ? 'Admin' : 'User'));
  $katClass = ['Baik' => 'badge-baik', 'Cukup' => 'badge-cukup', 'Kurang' => 'badge-kurang', 'Sangat Kurang' => 'badge-sangat-kurang'][$kategori] ?? 'badge-neutral';
  $ringColor = $score >= 76 ? 'var(--green)' : ($score >= 51 ? 'var(--amber)' : 'var(--red)');
  $circ = 2 * pi() * 74;
  $off = $circ * (1 - min(max($score, 0), 100) / 100);
@endphp
<div class="anim-fade" style="display:flex;flex-direction:column;gap:16px">
  <div class="card" style="padding:20px">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px">
      <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3)">Selamat datang</div>
        <h1 style="font-size:21px;font-weight:800;margin-top:2px;color:var(--ink)">{{ $u->full_name }}</h1>
        <div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap">
          <span class="badge badge-blue">{{ $roleLabel }}</span>
          @if($u->province?->name)<span class="badge badge-neutral">📍 {{ $u->province->name }}</span>@endif
          <span class="badge" style="background:var(--brand-100);color:var(--brand-700)">Tahun {{ $year }}</span>
        </div>
      </div>
      <form method="GET" action="/dashboard" class="toolbar no-print">
        @if(count($provinces))
          <select name="province_id" class="input" style="width:auto;padding:8px 34px 8px 12px;font-size:12.5px" onchange="this.form.submit()">
            @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (int)$pid === (int)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
          </select>
        @endif
        <select name="year" class="input" style="width:auto;padding:8px 34px 8px 12px;font-size:12.5px" onchange="this.form.submit()">
          @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
        </select>
      </form>
    </div>
  </div>

  <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:24px;align-items:center">
    <div style="display:flex;gap:10px;align-items:center">
      <svg width="170" height="170" viewBox="0 0 170 170">
        <circle cx="85" cy="85" r="74" fill="none" class="ring-track" stroke-width="13"/>
        <circle cx="85" cy="85" r="74" fill="none" stroke="{{ $score >= 76 ? '#16A34A' : ($score >= 51 ? '#D97706' : '#DC2626') }}" stroke-width="13" stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}" transform="rotate(-90 85 85)"/>
        <text x="85" y="82" text-anchor="middle" font-size="30" font-weight="800" fill="var(--ink)">{{ number_format($score, $score == round($score) ? 0 : 2, ',', '.') }}</text>
        <text x="85" y="104" text-anchor="middle" font-size="12" fill="var(--ink-3)">/100</text>
      </svg>
      <div style="text-align:left">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3)">Indeks Pembangunan Olahraga</div>
        <div style="font-size:15px;font-weight:600;margin-top:2px;color:var(--ink-2)">{{ $provinceName }} — {{ $year }}</div>
        <div style="margin-top:10px"><span class="badge badge-dot {{ $katClass }}" style="font-size:13px;padding:6px 16px">{{ $kategori }}</span> <span style="font-size:12px;color:var(--ink-3)">Skala 0–100</span></div>
      </div>
    </div>
    <div style="display:flex;gap:28px;flex-wrap:wrap;justify-content:center;text-align:center">
      <div><div style="font-size:22px;font-weight:800;color:var(--brand-700)">{{ number_format($totalRecords, 0, ',', '.') }}</div><div style="font-size:10.5px;text-transform:uppercase;color:var(--ink-3)">Total Data</div></div>
      <div><div style="font-size:22px;font-weight:800;color:var(--green)">{{ count($dimensions) }}</div><div style="font-size:10.5px;text-transform:uppercase;color:var(--ink-3)">Dimensi</div></div>
      <div><div style="font-size:22px;font-weight:800;color:var(--green)">{{ count($trend) }}</div><div style="font-size:10.5px;text-transform:uppercase;color:var(--ink-3)">Tahun Tren</div></div>
    </div>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between">
    <h2 style="font-size:15px;font-weight:700;color:var(--ink)">Ringkasan Indikator</h2>
    <a href="/hitung?year={{ $year }}@if(count($provinces))&province_id={{ $pid }}@endif" style="font-size:12.5px;font-weight:600;color:var(--brand-600)">Perhitungan →</a>
  </div>
  @if(!empty($nasional))
  <div class="card" style="padding:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div><div style="font-size:11px;text-transform:uppercase;color:var(--ink-3)">{{ __('Rata-rata Nasional') }} {{ $year }} ({{ $nasional['jml'] }} provinsi)</div>
      <div style="font-size:26px;font-weight:800;color:var(--brand-700)">{{ number_format($nasional['rata'], 2, ',', '.') }}</div>
      @if(!empty($nasional['peringkat']))<div style="font-size:12.5px;color:var(--ink-2)">{{ __('Provinsi Anda peringkat') }} <b>#{{ $nasional['peringkat'] }}</b></div>@endif</div>
      <a href="/laporan?tab=ranking&year={{ $year }}" style="font-size:12.5px;font-weight:600;color:var(--brand-600)">{{ __('Peringkat') }} →</a>
    </div>
    @if(!empty($nasional['atas']) && count($nasional['atas']))
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;margin-top:10px">
      <div><div style="font-size:11px;font-weight:700;color:var(--green)">▲ {{ __('3 Teratas') }}</div>
        @foreach($nasional['atas'] as $t)<div style="font-size:12.5px">{{ $t->name }} · <b>{{ number_format($t->ipo_score * 100, 1, ',', '.') }}</b></div>@endforeach</div>
      <div><div style="font-size:11px;font-weight:700;color:var(--red)">▼ {{ __('3 Terbawah') }}</div>
        @foreach($nasional['bawah'] as $t)<div style="font-size:12.5px">{{ $t->name }} · <b>{{ number_format($t->ipo_score * 100, 1, ',', '.') }}</b></div>@endforeach</div>
    </div>
    @endif
  </div>
  @endif
  <div class="grid-cards stagger">
    @foreach($dimensions as $d)
      @php($barCls = $d['display'] >= 51 ? 'bar-green' : ($d['display'] >= 26 ? 'bar-amber' : 'bar-red'))
      <a href="/hitung?year={{ $year }}" class="card card-hover" style="padding:16px;text-decoration:none;display:block">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="font-size:13.5px;font-weight:600;color:var(--ink)">{{ $d['label'] }}</div>
          <div style="font-size:11.5px;color:var(--ink-3)">{{ $d['display'] }}%</div>
        </div>
        <div class="progress" style="margin-top:10px"><div class="{{ $barCls }}" data-w="{{ $d['display'] }}"></div></div>
      </a>
    @endforeach
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between">
    <h2 style="font-size:15px;font-weight:700;color:var(--ink)">Perkembangan 5 Tahun</h2>
    <a href="/grafik" style="font-size:12.5px;font-weight:600;color:var(--brand-600)">Grafik →</a>
  </div>
  <div class="card" style="padding:8px 16px">
    @foreach($trend as $t)
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 4px;border-bottom:1px solid #F1EFF6">
        <span style="font-size:13px;color:var(--ink-2)">{{ $t->year }}</span>
        <span style="font-size:14px;font-weight:800;color:var(--brand-700)">{{ (int) round($t->ipo_score * 100) }}</span>
      </div>
    @endforeach
  </div>
</div>
@endsection
