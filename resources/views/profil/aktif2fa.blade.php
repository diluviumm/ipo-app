@extends('layouts.app')
@section('title', 'Aktifkan 2FA — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:520px;display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px;text-align:center">
    <h1 style="font-size:17px;font-weight:800">{{ __('Pindai Kode QR') }}</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Gunakan aplikasi authenticator.') }}</p>
    <div style="margin:14px auto;max-width:240px">{!! $qr !!}</div>
    <p style="font-size:12px;color:var(--ink-3)">{{ __('Atau masukkan manual:') }} <b style="color:var(--ink)">{{ $secret }}</b></p>
    @if($errors->any())
      <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="/profil/2fa/simpan" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      @csrf
      <div class="input-group">
        <label for="kode-2fa">{{ __('Kode 6 digit dari aplikasi') }} *</label>
        <input id="kode-2fa" class="input" name="kode" required inputmode="numeric" maxlength="6" style="font-size:20px;letter-spacing:6px;text-align:center">
      </div>
      <button class="btn btn-primary" type="submit">{{ __('Aktifkan') }}</button>
    </form>
  </div>
</div>
@endsection