<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#5B21B6">
<meta name="description" content="IPO — Indeks Pembangunan Olahraga. Data akurat, olahraga maju, masyarakat sehat!">
<title>@yield('title', 'IPO — Indeks Pembangunan Olahraga')</title>
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="stylesheet" href="/app.css">
<script>try{if(localStorage.getItem('ipo-theme')==='dark'){document.documentElement.dataset.theme='dark';}}catch(e){}</script>
<style>
  .layout { min-height: 100vh; }
  .sidebar { position: fixed; inset: 0 auto 0 0; width: 248px; z-index: 50; max-height: 100dvh; overflow-y: auto;
    background: linear-gradient(180deg, #2E1065 0%, #4C1D95 100%); color: #fff;
    display: flex; flex-direction: column; padding: 20px 14px; }
  .brand { display: flex; align-items: center; gap: 10px; padding: 2px 8px 16px; }
  .brand-mark { width: 38px; height: 38px; border-radius: 12px; background: #fff; color: var(--brand-700);
    display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; }
  .nav { display: flex; flex-direction: column; gap: 2px; margin-top: 6px; overflow-y: auto; min-height: 0; flex: 1 1 auto; }
  .nav a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px;
    color: rgba(255,255,255,.82); text-decoration: none; font-size: 13.5px; font-weight: 600; }
  .nav a small { display: block; font-size: 10.5px; font-weight: 400; color: rgba(255,255,255,.55); }
  .nav a:hover { background: rgba(255,255,255,.1); color: #fff; }
  .nav a.active { background: #fff; color: var(--brand-800); }
  .nav a.active small { color: var(--ink-3); }
  .nav svg { flex-shrink: 0; }
  .side-foot { margin-top: auto; display: flex; flex-direction: column; gap: 10px; }
  .user-chip { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,.1);
    border-radius: 12px; padding: 9px 11px; }
  .avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--green); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; }
  .topbar { display: none; }
  .content { margin-left: 248px; padding: 26px 32px 48px; box-sizing: border-box; }
  .drawer-scrim { display: none; }
  .icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px;
    border: 0; border-radius: 10px; background: rgba(255,255,255,.14); color: #fff; font-size: 19px; cursor: pointer; }
  .icon-btn:hover { background: rgba(255,255,255,.24); }
  @media (max-width: 1023px) {
    .sidebar { display: flex; transform: translateX(-105%); transition: transform .25s ease; box-shadow: none;
      width: min(300px, 84vw); }
    .sidebar.open { transform: none; box-shadow: 0 0 60px rgba(0,0,0,.45); }
    .drawer-scrim.show { display: block; position: fixed; inset: 0; z-index: 45; background: rgba(20,8,50,.5); }
    .content { margin-left: 0; padding: 14px 14px 96px; }
    .topbar { display: flex; align-items: center; gap: 10px; position: sticky; top: 0; z-index: 30;
      background: linear-gradient(120deg, #2E1065, #5B21B6); color: #fff; padding: 10px 14px; }
  }
  .page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
  .grid-cards { display: grid; grid-template-columns: 1fr; gap: 12px; }
  @media (min-width: 640px) { .grid-cards { grid-template-columns: 1fr 1fr; } }
  @media (min-width: 1024px) { .grid-cards { grid-template-columns: 1fr 1fr 1fr; } }
  @media (min-width: 1500px) { .grid-cards { grid-template-columns: repeat(4, 1fr); } }
  .toolbar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
  .pager { display: flex; align-items: center; justify-content: center; gap: 10px; margin: 16px 0 4px; }
  .pager-btn { display: inline-block; padding: 7px 16px; border-radius: 10px; font-size: 13px; font-weight: 700;
    background: var(--brand-700); color: #fff; text-decoration: none; }
  .pager-btn:hover { background: var(--brand-800); }
  .pager-btn.is-disabled { background: #e4e0f0; color: #8d87a3; }
  .pager-info { font-size: 12.5px; font-weight: 600; color: var(--ink-3); }
  .alert-err { background: var(--red-100); color: var(--red); border-radius: 12px; padding: 10px 14px; font-size: 13px; font-weight: 600; margin-bottom: 12px; }
  .bar-green { background: var(--green); } .bar-amber { background: var(--amber); } .bar-red { background: var(--red); }
  .toast-card { padding: 12px 16px; margin-bottom: 12px; border-left: 4px solid var(--green); }
  .toast-card.err { border-left-color: var(--red); }
</style>
@stack('head')
</head>
<body>
@php
  $u = auth()->user();
  $isAdmin = $u && in_array($u->role, ['admin','operator']);
  $isSuper = $u && $u->role === 'admin' && $u->province_id === null;
  $seg = request()->segment(1);
  $active = fn($k) => $seg === $k ? 'active' : '';
  $guestName = $u->full_name ?? 'Tamu';
  $guestRole = !$u ? 'Tamu' : ($u->role === 'admin' && $u->province_id === null ? 'Superadmin' : ucfirst($u->role));
  $guestInitial = strtoupper(substr($u->full_name ?? '?', 0, 1));
  $notifCount = 0;
  if ($u) {
    try {
      $nq = \Illuminate\Support\Facades\DB::table('notifikasi')->where('created_at', '>=', date('Y-m-d H:i:s', time() - 7 * 86400))->where('dibaca', false);
      if ($u->province_id) $nq->where('province_id', $u->province_id);
      $notifCount = $nq->count();
    } catch (Throwable $e) { $notifCount = 0; }
  }
@endphp
<div class="layout">
  <aside class="sidebar no-print">
    <div class="brand">
      <div class="brand-mark">IPO</div>
      <div><div style="font-weight:800;font-size:15px">IPO</div><div style="font-size:10.5px;color:rgba(255,255,255,.65)">Indeks Pembangunan Olahraga</div></div>
    </div>
    <nav class="nav">
      <a href="/dashboard" class="{{ $active('dashboard') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l9-9 9 9"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg><span>{{ __('Dashboard') }}<small>{{ __('Ringkasan indeks') }}</small></span></a>
      <a href="/data" class="{{ $active('data') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg><span>{{ __('Pengelolaan Data') }}<small>{{ __('Kelola data indikator') }}</small></span></a>
      @if($isAdmin)
      <a href="/hitung" class="{{ $active('hitung') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 7h8M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/></svg><span>{{ __('Perhitungan Indeks') }}<small>{{ __('Hitung nilai indeks') }}</small></span></a>
      @endif
      <a href="/grafik" class="{{ $active('grafik') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 5-8"/></svg><span>{{ __('Grafik Indeks') }}<small>{{ __('Lihat perkembangan') }}</small></span></a>
      <a href="/banding" class="{{ $active('banding') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg><span>{{ __('Banding') }}<small>{{ __('Antar provinsi') }}</small></span></a>
      <a href="/laporan" class="{{ $active('laporan') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h7M9 17h7"/></svg><span>{{ __('Laporan') }}<small>{{ __('Cetak & ekspor') }}</small></span></a>
      <a href="/notifikasi" class="{{ $active('notifikasi') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg><span>{{ __('Notifikasi') }}@if($notifCount)<b style="background:#fff;color:#5B21B6;border-radius:99px;font-size:10px;padding:1px 7px;margin-left:6px">{{ $notifCount }}</b>@endif<small>{{ __('Perubahan skor') }}</small></span></a>
      <a href="/bantuan" class="{{ $active('bantuan') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.4 2.3c-.8.3-.9 1-.9 1.7"/><path d="M12 17h.01"/></svg><span>{{ __('Bantuan') }}<small>{{ __('Tips & bantuan') }}</small></span></a>
      @if($isSuper)
      <a href="/users" class="{{ $active('users') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M16 4a4 4 0 0 1 0 8M22 21c0-3-1.5-5-4-5.5"/></svg><span>{{ __('Manajemen User') }}<small>{{ __('Kelola akun pengguna') }}</small></span></a>
      @endif
      @if($isSuper)
      <a href="/aktivitas" class="{{ $active('aktivitas') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg><span>{{ __('Aktivitas') }}<small>{{ __('Jejak perubahan') }}</small></span></a>
      @endif
      @if($isSuper)
      <a href="/sistem" class="{{ $active('sistem') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg><span>{{ __('Sistem') }}<small>{{ __('Cadangan DB') }}</small></span></a>
      @endif
      @if($isAdmin)
      <a href="/sampah" class="{{ $active('sampah') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg><span>{{ __('Tong Sampah') }}<small>{{ __('Pulihkan data') }}</small></span></a>
      @endif
      @auth
      <a href="/profil/password" class="{{ $active('profil') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.06-.4.1-.8.1-1.2z"/></svg><span>{{ __('Profil') }}<small>{{ __('Ganti password') }}</small></span></a>
      @endauth
    </nav>
    <div class="side-foot">
      <div class="user-chip">
        <div class="avatar">{{ $guestInitial }}</div>
        <div style="min-width:0"><div style="font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $guestName }}</div><div style="font-size:11px;color:rgba(255,255,255,.65)">{{ $guestRole }}</div></div>
      </div>
      @auth
      <form method="POST" action="/logout">@csrf
        <button class="btn btn-block" style="background:rgba(255,255,255,.14);color:#fff" type="submit">{{ __('Keluar') }}</button>
      </form>
      @else
      <a href="/login" class="btn btn-block" style="background:rgba(255,255,255,.14);color:#fff;text-decoration:none">{{ __('Masuk') }}</a>
      @endauth
      <button type="button" class="btn btn-block" style="background:rgba(255,255,255,.14);color:#fff;margin-top:8px" onclick="tukarTema()" aria-label="Ganti tema gelap atau terang">🌙 <span data-tema-label data-id="Mode gelap" data-en="Dark mode">{{ __('Mode gelap') }}</span></button>
      <div style="display:flex;gap:8px;margin-top:8px">
        <a href="/bahasa/id" class="btn btn-sm" style="flex:1;background:rgba(255,255,255,{{ app()->getLocale() === 'id' ? '.28' : '.12' }});color:#fff;text-decoration:none;text-align:center">ID</a>
        <a href="/bahasa/en" class="btn btn-sm" style="flex:1;background:rgba(255,255,255,{{ app()->getLocale() === 'en' ? '.28' : '.12' }});color:#fff;text-decoration:none;text-align:center">EN</a>
      </div>
    </div>
  </aside>

  <div class="topbar no-print">
    <button class="icon-btn" id="drawerBtn" aria-label="Buka menu navigasi">☰</button>
    <div class="brand-mark" style="width:32px;height:32px;font-size:14px;border-radius:10px">IPO</div>
    <div style="font-weight:800">IPO</div>
    <div style="margin-left:auto;display:flex;align-items:center;gap:8px">
      <form method="GET" action="/cari" class="no-print" style="display:flex" role="search">
        <input name="q" class="input" placeholder="{{ __('Cari') }}…" value="{{ request('q') }}" aria-label="Pencarian global" style="width:110px;height:34px;font-size:12.5px;border-radius:10px;border:0">
      </form>
      <a href="/notifikasi" aria-label="Notifikasi" style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.14);color:#fff;text-decoration:none;font-size:17px">🔔@if($notifCount)<span style="position:absolute;top:-4px;right:-4px;background:#fff;color:#5B21B6;border-radius:99px;font-size:9px;font-weight:800;padding:1px 5px">{{ $notifCount }}</span>@endif</a>
      <button class="icon-btn" style="width:34px;height:34px;background:rgba(255,255,255,.14);border:0;color:#fff" onclick="tukarTema()" aria-label="Ganti tema gelap atau terang">🌙</button>
      <div style="font-size:12px;color:rgba(255,255,255,.8)">{{ $guestName }}</div>
    </div>
  </div>
  <div class="drawer-scrim no-print" id="drawerScrim"></div>

  <main class="content">
    @if(session('toast'))
      <div class="card anim-fade toast-card {{ session('toast.type') === 'success' ? '' : 'err' }}">
        <span style="font-size:13.5px;font-weight:600">{{ session('toast.text') }}</span>
      </div>
    @endif
    @auth
    <div id="sesiBox" class="card no-print" style="display:none;padding:12px 16px;margin-bottom:12px;border-left:4px solid var(--amber);font-size:13px">
      <b>{{ __('Sesi hampir habis') }} (<span id="sesiSisa">5:00</span>).</b> {{ __('Simpan pekerjaan Anda, lalu') }}
      <form method="POST" action="/sesi/perpanjang" style="display:inline">@csrf<button class="btn btn-secondary btn-sm" type="submit">{{ __('Perpanjang sesi') }}</button></form>
    </div>
    <script>
    (function () {
      {{-- Samakan dengan SetSesiAktif::BATAS: admin 24 jam, lainnya 8 jam --}}
      var maks = {{ auth()->user() && auth()->user()->role === 'admin' ? 86400 : 28800 }};
      var sisa = maks - ({{ time() }} - {{ (int) session('sesi_mulai', time()) }});
      var box = document.getElementById('sesiBox'), el = document.getElementById('sesiSisa');
      function tik() {
        sisa -= 30;
        if (sisa <= 300 && sisa > 0 && box) {
          box.style.display = 'block';
          var m = Math.floor(sisa / 60), d = sisa % 60;
          if (el) el.textContent = m + ':' + (d < 10 ? '0' : '') + d;
        }
      }
      setInterval(tik, 30000);
    })();
    </script>
    @endauth
    @if($errors->any())
      <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    @yield('content')
  </main>

  <nav class="tabbar no-print" aria-label="Navigasi utama">
    <div class="tabbar-inner">
      <a href="/dashboard" class="tabbar-item {{ $active('dashboard') }}">⌂<span>{{ __('Home') }}</span></a>
      <a href="/data" class="tabbar-item {{ $active('data') }}">▤<span>{{ __('Data') }}</span></a>
      <a href="/grafik" class="tabbar-item {{ $active('grafik') }}">↗<span>{{ __('Grafik') }}</span></a>
      <a href="/laporan" class="tabbar-item {{ $active('laporan') }}">▦<span>{{ __('Laporan') }}</span></a>
    </div>
  </nav>
  <button id="keAtas" class="no-print" aria-label="Kembali ke atas" style="display:none;position:fixed;right:16px;bottom:76px;z-index:40;width:44px;height:44px;border-radius:99px;border:none;background:var(--brand-700);color:#fff;font-size:18px;box-shadow:0 4px 14px rgba(0,0,0,.3);cursor:pointer">↑</button>
  <button id="pasangApp" class="no-print" style="display:none;position:fixed;left:16px;bottom:76px;z-index:40;padding:10px 16px;border-radius:99px;border:none;background:var(--green);color:#fff;font-size:13px;font-weight:700;box-shadow:0 4px 14px rgba(0,0,0,.3);cursor:pointer">⭳ Pasang aplikasi</button>
</div>
@stack('scripts')
<script>
document.querySelectorAll('.progress > div[data-w]').forEach(function (el) {
  el.style.width = Math.min(100, Number(el.dataset.w) || 0) + '%';
});
document.querySelectorAll('[data-bg]').forEach(function (el) { el.style.background = el.dataset.bg; });
document.querySelectorAll('[data-color]').forEach(function (el) { el.style.color = el.dataset.color; });
(function () {
  var b = document.getElementById('keAtas');
  addEventListener('scroll', function () { if (b) b.style.display = scrollY > 600 ? 'block' : 'none'; }, { passive: true });
  if (b) b.addEventListener('click', function () { scrollTo({ top: 0, behavior: 'smooth' }); });
})();
(function () {
  var tunda = null, btn = document.getElementById('pasangApp');
  addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); tunda = e; if (btn) btn.style.display = 'block'; });
  if (btn) btn.addEventListener('click', function () { if (tunda) { tunda.prompt(); tunda = null; btn.style.display = 'none'; } });
  addEventListener('appinstalled', function () { if (btn) btn.style.display = 'none'; });
})();
// Tema gelap/terang: tersimpan di localStorage, berlaku semua halaman
function tukarTema() {
  var h = document.documentElement;
  var d = h.dataset.theme === 'dark' ? 'light' : 'dark';
  h.dataset.theme = d;
  try { localStorage.setItem('ipo-theme', d); } catch (e) {}
  var bs = document.querySelectorAll('[onclick="tukarTema()"]');
  for (var i = 0; i < bs.length; i++) { paintBtn(bs[i], d); }
  if (window.refreshChartTheme) { window.refreshChartTheme(); }
}
function paintBtn(b, d) {
  var moon = (d !== 'dark');
  var t = b.firstChild;
  if (t) { b.replaceChild(document.createTextNode(moon ? '🌙 ' : '☀️ '), t); }
  var lb = b.querySelector('[data-tema-label]');
  if (lb) { lb.textContent = moon ? (lb.dataset.id || 'Mode gelap') : (lb.dataset.en || 'Mode terang'); }
}
(function () {
  var cur = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
  var bs = document.querySelectorAll('[onclick="tukarTema()"]');
  for (var i = 0; i < bs.length; i++) { paintBtn(bs[i], cur); }
})();
(function () {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
  }
})();
function cetakBagian(id) {
  var src = document.getElementById(id);
  if (!src) return;
  var w = window.open('', '_blank');
  if (!w) return;
  w.document.write('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Cetak</title>');
  w.document.write('<link rel="stylesheet" href="/app.css"></head>');
  w.document.write('<body style="background:#fff"><div style="max-width:720px;margin:0 auto;padding:8px">');
  w.document.write(src.innerHTML);
  w.document.write('</div></body></html>');
  w.document.close();
  w.focus();
  setTimeout(function () { w.print(); }, 400);
}
(function () {
  var kotor = false;
  document.addEventListener('input', function (e) {
    var f = e.target.closest ? e.target.closest('form') : null;
    if (f && (f.method || 'get').toLowerCase() !== 'get') kotor = true;
  });
  document.addEventListener('submit', function () { kotor = false; });
  window.addEventListener('beforeunload', function (e) {
    if (kotor) { e.preventDefault(); e.returnValue = ''; }
  });
})();
(function () {
  var side = document.querySelector('.sidebar'), scrim = document.getElementById('drawerScrim'),
      btn = document.getElementById('drawerBtn');
  if (!side || !scrim || !btn) return;
  function open() { side.classList.add('open'); scrim.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function close() { side.classList.remove('open'); scrim.classList.remove('show'); document.body.style.overflow = ''; }
  btn.addEventListener('click', open);
  scrim.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  side.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', close); });
})();
</script>
</body>
</html>
