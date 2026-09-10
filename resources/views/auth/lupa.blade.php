@extends('layouts.app')
@section('title', 'Lupa Password — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:480px;display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">{{ __('Lupa Password') }}</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Minta token reset ke Superadmin, lalu isi di bawah (berlaku 1 jam).') }}</p>
    @if($errors->any())
      <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="/lupa-password" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      @csrf
      <div class="input-group">
        <label for="lp-token">{{ __('Token reset') }} *</label>
        <input id="lp-token" class="input" name="token" required>
      </div>
      <div class="input-group">
        <label for="lp-baru">{{ __('Password baru (min. 6)') }} *</label>
        <input id="lp-baru" class="input" type="password" name="baru" required minlength="6">
      </div>
      <button class="btn btn-primary" type="submit">{{ __('Atur Ulang Password') }}</button>
    </form>
  </div>
</div>
@endsection