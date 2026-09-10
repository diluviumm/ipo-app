<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#5B21B6">
<title>Masuk — IPO</title>
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="stylesheet" href="/app.css">
<script>try{if(localStorage.getItem('ipo-theme')==='dark'){document.documentElement.dataset.theme='dark';}}catch(e){}</script>
</head>
<body>
<button type="button" onclick="tukarTema()" aria-label="Ganti tema gelap/terang" title="Gelap/Terang"
  style="position:fixed;top:14px;right:14px;z-index:60;width:40px;height:40px;border-radius:12px;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.12);color:#fff;font-size:18px;cursor:pointer">🌙</button>
<script>
function tukarTema(){var h=document.documentElement;var d=h.dataset.theme==='dark'?'light':'dark';h.dataset.theme=d;try{localStorage.setItem('ipo-theme',d);}catch(e){}var b=document.querySelector('button[onclick=\"tukarTema()\"]');if(b)b.textContent=d==='dark'?'☀️':'🌙';}
(function(){try{if(localStorage.getItem('ipo-theme')==='dark'){var b=document.querySelector('button[onclick=\"tukarTema()\"]');if(b)b.textContent='☀️';}}catch(e){}})();
</script>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;position:relative;overflow:hidden;background:linear-gradient(165deg,#2E1065 0%,#4C1D95 55%,#5B21B6 100%)">
  <div style="position:absolute;top:-96px;right:-96px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
  <div style="position:absolute;bottom:-96px;left:-96px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.04)"></div>
  <div class="anim-fade-up" style="position:relative;width:100%;max-width:380px">
    <div style="text-align:center;margin-bottom:28px">
      <div style="display:flex;justify-content:center;margin-bottom:12px">
        <div style="width:72px;height:72px;border-radius:20px;background:#fff;color:var(--brand-700);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:24px">IPO</div>
      </div>
      <h1 style="color:#fff;font-size:26px;font-weight:800;letter-spacing:-.5px">IPO</h1>
      <p style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.14em;margin-top:2px">Indeks Pembangunan Olahraga</p>
    </div>
    <div style="background:var(--white);border-radius:16px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.3)">
      <h2 style="font-size:17px;font-weight:700;text-align:center;margin-bottom:4px;color:var(--ink)">{{ __('Masuk ke Aplikasi') }}</h2>
      <p style="font-size:12px;text-align:center;margin-bottom:20px;color:var(--ink-3)">{{ __('Akses aman berdasarkan peran pengguna') }}</p>
      @if($errors->any())
        <div role="alert" class="anim-fade" style="display:flex;gap:8px;font-size:12.5px;font-weight:500;padding:10px 14px;border-radius:12px;margin-bottom:16px;background:var(--red-100);color:var(--red)">{{ $errors->first() }}</div>
      @endif
      <form method="POST" action="/login" style="display:flex;flex-direction:column;gap:16px">
        @csrf
        <div class="input-group">
          <label for="login-username">{{ __('Username') }}</label>
          <div style="position:relative">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--ink-3)">👤</span>
            <input id="login-username" class="input" name="username" placeholder="{{ __('Masukkan username') }}" value="{{ old('username') }}" autofocus autocomplete="username" style="padding-left:40px">
          </div>
        </div>
        <div class="input-group">
          <label for="login-password">{{ __('Password') }}</label>
          <div style="position:relative">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--ink-3)">🔒</span>
            <input id="login-password" class="input" name="password" placeholder="{{ __('Masukkan password') }}" type="password" autocomplete="current-password" style="padding-left:40px">
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ink-2);margin:2px 0 12px">
          <input type="checkbox" name="remember" value="1" style="width:16px;height:16px"> {{ __('Ingat saya') }}
        </label>
        <button type="submit" class="btn btn-primary btn-block" style="padding:12px;font-size:15px">{{ __('Masuk') }}</button>
      </form>
      <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--ink-3)">
        {{ __('Belum punya akun?') }} <a href="/register" style="color:var(--brand-600);font-weight:700">{{ __('Daftar') }}</a>
        <span style="margin:0 8px">·</span><a href="/bahasa/id" style="font-weight:700">ID</a> | <a href="/bahasa/en" style="font-weight:700">EN</a>
      </div>
      <div style="text-align:center;margin-top:20px;padding-top:16px;font-size:12px;color:var(--ink-3);border-top:1px solid var(--border)">{{ __('Lupa password?') }} <a href="/lupa-password" style="color:var(--brand-600);font-weight:700">Atur ulang</a></div>
    </div>
    <p style="text-align:center;color:rgba(255,255,255,.6);font-size:11px;margin-top:20px">© 2024–2026 IPO — Data Akurat, Olahraga Maju, Masyarakat Sehat!</p>
  </div>
</div>
</body>
</html>
