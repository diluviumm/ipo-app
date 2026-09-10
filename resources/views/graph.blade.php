@extends('layouts.app')
@section('title', 'Grafik Indeks — IPO')

@section('content')
@php
  $katClass = ['Baik' => 'badge-baik', 'Cukup' => 'badge-cukup', 'Kurang' => 'badge-kurang', 'Sangat Kurang' => 'badge-sangat-kurang'];
  $katOf = fn($s) => $s >= 76 ? 'Baik' : ($s >= 51 ? 'Cukup' : ($s >= 26 ? 'Kurang' : 'Sangat Kurang'));
@endphp
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:16px">
  <div class="page-head">
    <div>
      <a href="/dashboard" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
      <h1 style="font-size:18px;font-weight:600;color:var(--ink)">Grafik Indeks</h1>
    </div>
    <form method="GET" action="/grafik" class="toolbar no-print">
      @if(count($provinces))
        <select name="province_id" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()" title="Pilih provinsi">
          @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (int)$pid === (int)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
        </select>
      @endif
      <select name="year" class="input" style="width:auto;padding:5px 30px 5px 8px;font-size:12px" onchange="this.form.submit()">
        @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select>
    </form>
  </div>

  <div class="card" style="padding:20px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
      <h3 style="font-size:14px;font-weight:600;color:var(--ink)">Tren Indeks 5 Tahun — {{ $provinceName }}</h3>
      <span style="margin-left:auto;font-size:11px;color:var(--ink-3)">Tahun {{ $year - 4 }}–{{ $year }}</span>
    </div>
    <div style="height:320px"><canvas id="trendChart"></canvas></div>
  </div>

  @if(count($trend))
    <div class="card" style="padding:20px">
      <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--ink)">Detail Skor per Tahun</h3>
      <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($trend as $t)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:10px;background:var(--paper)">
            <span style="font-size:13px;color:var(--ink)">📅 {{ $t['year'] }}</span>
            <span><span class="badge {{ $katClass[$katOf($t['score'])] }}">{{ $katOf($t['score']) }}</span>
            <span style="font-size:14px;font-weight:700;color:var(--primary)">{{ $t['score'] }}</span></span>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="/vendor/chart.umd.js"></script>
<script type="application/json" id="trend-data">@json($trend)</script>
<script>
(function () {
  var trend = JSON.parse(document.getElementById('trend-data').textContent);
  var cv = document.getElementById('trendChart');
  function pal() {
    var dark = document.documentElement.dataset.theme === 'dark';
    return { tick: dark ? '#C9C5DE' : '#4B5563', grid: dark ? 'rgba(201,197,222,.14)' : 'rgba(107,114,128,.15)', pt: dark ? '#211E33' : '#fff' };
  }
  var c = pal();
  var chart = new Chart(cv, {
    type: 'line',
    data: { labels: trend.map(function (t) { return t.year; }), datasets: [{
      label: 'IPO Score', data: trend.map(function (t) { return t.score; }),
      borderColor: '#6D28D9', backgroundColor: 'rgba(109,40,217,.10)',
      borderWidth: 2.5, pointRadius: 5, pointBackgroundColor: c.pt,
      pointBorderColor: '#6D28D9', pointBorderWidth: 2.5, pointHoverRadius: 7, fill: true, tension: 0.35 }]},
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#312E81', callbacks: { label: function (x) { return 'Score: ' + x.parsed.y + '/100'; } } } },
      scales: { x: { grid: { display: false }, ticks: { color: c.tick, font: { size: 11 } } },
        y: { min: 0, max: 100, grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 11 }, stepSize: 25 } } } }
  });
  window.refreshChartTheme = function () {
    var p = pal();
    chart.data.datasets[0].pointBackgroundColor = p.pt;
    chart.options.scales.x.ticks.color = p.tick;
    chart.options.scales.y.ticks.color = p.tick;
    chart.options.scales.y.grid.color = p.grid;
    chart.update();
  };
})();
</script>
@endpush
