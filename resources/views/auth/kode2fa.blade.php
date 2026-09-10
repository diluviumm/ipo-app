@extends('layouts.app')
@section('title', 'Verifikasi 2FA — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:480px;display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">{{ __('Kode Keamanan') }}</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Buka aplikasi authenticator lalu masukkan 6 digit.') }}</p>
    @if($errors->any())
      <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="/verifikasi-2fa" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      @csrf
      <div class="input-group">
        <label for="kode2fa">{{ __('Kode 6 digit') }} *</label>
        <input id="kode2fa" class="input" name="kode" required inputmode="numeric" autocomplete="one-time-code" maxlength="6" style="font-size:22px;letter-spacing:6px;text-align:center">
      </div>
      <button class="btn btn-primary" type="submit">{{ __('Verifikasi') }}</button>
    </form>
  </div>
</div>
@endsection