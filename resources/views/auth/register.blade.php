<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#5B21B6">
<title>Daftar — IPO</title>
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="stylesheet" href="/app.css">
<script>try{if(localStorage.getItem('ipo-theme')==='dark'){document.documentElement.dataset.theme='dark';}}catch(e){}</script>
</head>
<body>
<button type="button" onclick="tukarTema()" aria-label="Ganti tema gelap/terang" title="Gelap/Terang"
  style="position:fixed;top:14px;right:14px;z-index:60;width:40px;height:40px;border-radius:12px;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.12);color:#fff;font-size:18px;cursor:pointer">🌙</button>
<script>
function tukarTema(){var h=document.documentElement;var d=h.dataset.theme==='dark'?'light':'dark';h.dataset.theme=d;try{localStorage.setItem('ipo-theme',d);}catch(e){}var b=document.querySelector('button[onclick="tukarTema()"]');if(b)b.textContent=d==='dark'?'☀️':'🌙';}
(function(){try{if(localStorage.getItem('ipo-theme')==='dark'){var b=document.querySelector('button[onclick="tukarTema()"]');if(b)b.textContent='☀️';}}catch(e){}})();
</script>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:linear-gradient(165deg,#2E1065 0%,#4C1D95 55%,#5B21B6 100%)">
  <div class="anim-fade-up" style="width:100%;max-width:380px">
    <div style="text-align:center;margin-bottom:24px">
      <h1 style="color:#fff;font-size:24px;font-weight:800">IPO</h1>
      <p style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.14em">Indeks Pembangunan Olahraga</p>
    </div>
    <div style="background:var(--white);border-radius:16px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.3)">
      <h2 style="font-size:17px;font-weight:700;text-align:center;margin-bottom:4px;color:var(--ink)">{{ __('Buat Akun') }}</h2>
      <p style="font-size:12px;text-align:center;margin-bottom:20px;color:var(--ink-3)">{{ __('Akun baru otomatis berperan User (lihat saja)') }}</p>
      @if($errors->any())
        <div role="alert" class="anim-fade" style="font-size:12.5px;font-weight:500;padding:10px 14px;border-radius:12px;margin-bottom:16px;background:var(--red-100);color:var(--red)">{{ $errors->first() }}</div>
      @endif
      <form method="POST" action="/register" style="display:flex;flex-direction:column;gap:14px">
        @csrf
        <div class="input-group"><label for="reg-name">{{ __('Nama lengkap') }}</label><input id="reg-name" class="input" name="full_name" placeholder="{{ __('Masukkan nama lengkap') }}" value="{{ old('full_name') }}"></div>
        <div class="input-group"><label for="reg-user">{{ __('Username') }}</label><input id="reg-user" class="input" name="username" placeholder="{{ __('Minimal 3 karakter') }}" value="{{ old('username') }}" autocomplete="username"></div>
        <div class="input-group"><label for="reg-pass">{{ __('Password') }}</label><input id="reg-pass" class="input" name="password" type="password" placeholder="{{ __('Minimal 6 karakter') }}" autocomplete="new-password"></div>
        <div style="margin-top:-6px">
        <div class="progress"><div id="pw-bar" data-w="0"></div></div>
        <div id="pw-teks" style="font-size:11.5px;color:var(--ink-3);margin-top:4px"></div>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="padding:12px;font-size:15px">{{ __('Daftar') }}</button>
      </form>
      <script>
      (function () {
        var i = document.getElementById('reg-pass');
        if (!i) return;
        i.addEventListener('input', function () {
          var v = i.value, s = 0;
          if (v.length > 5) { s = s + 1; }
          if (/\d/.test(v)) { s = s + 1; }
          var b = document.getElementById('pw-bar');
          b.style.width = (s * 33) + '%';
          b.className = s < 2 ? 'bar-red' : 'bar-green';
          var t = document.getElementById('pw-teks');
          if (t) { t.textContent = s < 2 ? 'Lemah' : 'Kuat'; }
        });
      })();
      </script>
      <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--ink-3)">{{ __('Sudah punya akun?') }} <a href="/login" style="color:var(--brand-600);font-weight:700">{{ __('Masuk') }}</a>
        <span style="margin:0 8px">·</span><a href="/bahasa/id" style="font-weight:700">ID</a> | <a href="/bahasa/en" style="font-weight:700">EN</a></div>
    </div>
  </div>
</div>
</body>
</html>
