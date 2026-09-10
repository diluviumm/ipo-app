@extends('layouts.app')
@section('title', 'Ganti Password — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:520px;display:flex;flex-direction:column;gap:14px">
  <div>
    <a href="/dashboard" class="btn btn-secondary btn-sm">← Kembali</a>
  </div>
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">Ganti Password</h1>
    @if($errors->any())
      <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="/profil/password" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      @csrf
      <div class="input-group">
        <label for="pw-lama">Password lama *</label>
        <input id="pw-lama" class="input" type="password" name="lama" required>
      </div>
      <div class="input-group">
        <label for="pw-baru">Password baru (min. 6) *</label>
        <input id="pw-baru" class="input" type="password" name="baru" required minlength="6">
      </div>
      <div class="input-group">
        <label for="pw-konf">Ulangi password baru *</label>
        <input id="pw-konf" class="input" type="password" name="baru_confirmation" required>
      </div>
      <button class="btn btn-primary" type="submit">Simpan Password</button>
    </form>
    <div style="margin-top:4px">
      <div class="progress"><div id="pw-bar" data-w="0"></div></div>
      <div id="pw-teks" style="font-size:11.5px;color:var(--ink-3);margin-top:4px"></div>
    </div>
    <script>
    (function () {
      var i = document.getElementById('pw-baru');
      if (!i) return;
      i.addEventListener('input', function () {
        var v = i.value, s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        var pct = [0, 20, 40, 60, 80, 100][s];
        var b = document.getElementById('pw-bar');
        b.style.width = pct + '%';
        b.className = s < 2 ? 'bar-red' : (s < 4 ? 'bar-amber' : 'bar-green');
        document.getElementById('pw-teks').textContent = s < 2 ? 'Lemah' : (s < 4 ? 'Sedang' : 'Kuat');
      });
    })();
    </script>
  </div>
  @if(in_array(auth()->user()->role, ['admin', 'operator'], true))
  <div class="card" style="padding:20px">
    <h2 style="font-size:15px;font-weight:800">{{ __('Verifikasi 2 Langkah (2FA)') }}</h2>
    @if(!empty($me->totp_aktif))
      <p style="font-size:12.5px;color:var(--ink-3)">Status: <b style="color:var(--green)">Aktif</b></p>
      <form method="POST" action="/profil/2fa/mati" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
        @csrf
        <div class="input-group">
          <label for="off-pw">{{ __('Password saat ini (konfirmasi matikan)') }} *</label>
          <input id="off-pw" class="input" type="password" name="lama" required>
        </div>
        <button class="btn btn-danger" type="submit">{{ __('Matikan 2FA') }}</button>
      </form>
    @else
      <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Tambah lapis kode 6 digit dari aplikasi authenticator setiap masuk.') }}</p>
      <a href="/profil/2fa/mulai" class="btn btn-secondary" style="margin-top:12px;display:inline-block">{{ __('Aktifkan 2FA') }}</a>
    @endif
  </div>
  @endif
</div>
@endsection