@extends('layouts.app')
@section('title', 'Sistem — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">{{ __('Sistem & Cadangan') }}</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Ukuran basis data') }}: <b>{{ number_format($size / 1048576, 2, ',', '.') }} MB</b></p>
    <div class="table-wrap" style="margin-top:12px">
    <table class="table">
    <thead><tr><th>Tabel</th><th>Baris</th></tr></thead>
    <tbody>
    @foreach($jml as $t => $n)<tr><td>{{ $t }}</td><td>{{ number_format($n, 0, ',', '.') }}</td></tr>@endforeach
    </tbody>
    </table>
    </div>
    <a href="/sistem/backup" class="btn btn-primary btn-sm no-print" style="margin-top:12px">⭳ {{ __('Unduh Cadangan DB') }}</a>
    <form method="POST" action="/sistem/vakum" class="no-print" style="margin-top:8px" onsubmit="return confirm('Rapikan basis data sekarang?')">@csrf<button class="btn btn-secondary btn-sm" type="submit">Rapikan DB (VACUUM)</button></form>
  </div>
  <div class="card" style="padding:20px">
    <h2 style="font-size:15px;font-weight:800">{{ __('Kunci Tahun Arsip') }}</h2>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Tahun terkunci hanya bisa diubah superadmin.') }}</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
      @foreach($tahun as $th)
        @if(in_array($th, $terkunci))
          <form method="POST" action="/sistem/buka/{{ $th }}">@csrf<button class="btn btn-secondary btn-sm" type="submit">🔒 {{ $th }} (buka)</button></form>
        @else
          <form method="POST" action="/sistem/kunci/{{ $th }}">@csrf<button class="btn btn-secondary btn-sm" type="submit">🔓 {{ $th }} (kunci)</button></form>
        @endif
      @endforeach
    </div>
  <div class="card" style="padding:20px">
    <h2 style="font-size:15px;font-weight:800">{{ __('Status Sistem') }}</h2>
    <div class="table-wrap" style="margin-top:10px">
    <table class="table">
    <tbody>
    @foreach($status as $k => $v)<tr><td style="font-weight:600">{{ $k }}</td><td>{{ $v }}</td></tr>@endforeach
    </tbody>
    </table>
    </div>
  </div>
</div>
@endsection