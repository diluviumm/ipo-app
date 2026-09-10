@extends('layouts.app')
@section('title', 'Banding Provinsi — IPO')

@section('content')
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <div>
      <a href="/dashboard" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
      <h1 style="font-size:18px;font-weight:600;color:var(--ink)">Banding Provinsi</h1>
    </div>
    <form method="GET" action="/banding" class="toolbar no-print card" style="padding:12px">
      @for($i = 0; $i < 4; $i++)
        <select name="p[]" class="input" style="width:auto;max-width:170px">
          <option value="">—</option>
          @foreach($provinces as $p)
            <option value="{{ $p->id }}" {{ in_array($p->id, $pilih) && array_search($p->id, $pilih) === $i ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      @endfor
      <select name="year" class="input" style="width:auto">
        @foreach($years as $y)<option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select>
      <button class="btn btn-primary btn-sm" type="submit">Bandingkan</button>
    </form>
    <div class="no-print" style="display:flex;gap:8px">
      <a class="btn btn-secondary btn-sm" href="/banding?p[]={{ implode('&p[]=', $pilih) }}&year={{ $year }}&ekspor=pdf">Ekspor PDF</a>
    </div>
  </div>
  @if(count($dims))
  <div class="card" style="padding:20px">
    <div style="height:280px"><canvas id="bandingChart"></canvas></div>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Dimensi</th>@foreach($dims as $d)<th>{{ $d['nama'] }}</th>@endforeach</tr></thead>
      <tbody>
        @foreach($labels as $k => $label)
          <tr><td><b>{{ $label }}</b></td>@foreach($dims as $d)<td>{{ $d['skor'][$k] }}</td>@endforeach</tr>
        @endforeach
        <tr><td><b>IPO</b></td>@foreach($dims as $d)<td style="font-weight:800;color:var(--brand-700)">{{ $d['ipo'] }}</td>@endforeach</tr>
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="/vendor/chart.umd.js"></script>
<script type="application/json" id="banding-data">@json($dims)</script>
<script>
(function () {
  var cv = document.getElementById('bandingChart');
  if (!cv) return;
  var rows = JSON.parse(document.getElementById('banding-data').textContent);
  var dark = document.documentElement.dataset.theme === 'dark';
  var tick = dark ? '#C9C5DE' : '#4B5563';
  var chart = new Chart(cv, { type: 'bar',
    data: { labels: rows.map(function (r) { return r.nama; }),
      datasets: [{ label: 'IPO {{ $year }}', data: rows.map(function (r) { return r.ipo; }),
        backgroundColor: '#6D28D9', borderRadius: 8 }]},
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { ticks: { color: tick, font: { size: 10 } } },
        y: { min: 0, max: 100, ticks: { color: tick, stepSize: 25 } } } } });
  window.refreshChartTheme = function () {
    var dk = document.documentElement.dataset.theme === 'dark';
    var tk = dk ? '#C9C5DE' : '#4B5563';
    chart.options.scales.x.ticks.color = tk;
    chart.options.scales.y.ticks.color = tk;
    chart.update();
  };
})();
</script>
@endpush