@extends('layouts.app')
@section('title', 'Pratinjau Impor — IPO')

@section('content')
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">Pratinjau: {{ $label }}</h1>
    <p style="font-size:13px;color:var(--ink-3)">{{ $jmlValid }} baris valid, {{ count($gagal) }} bermasalah. Belum ada data masuk.</p>
    @if($gagal)
    <div class="alert-err">{{ implode(' | ', array_slice($gagal, 0, 5)) }}</div>
    @endif
    <form method="POST" action="/data/{{ $dim }}/impor/konfirmasi" style="display:flex;gap:8px;margin-top:12px">
      @csrf
      <a href="/data/{{ $dim }}/impor" class="btn btn-secondary" style="flex:1">Batal</a>
      <button class="btn btn-primary" type="submit" style="flex:1" {{ $jmlValid ? '' : 'disabled' }}>Ya, masukkan {{ $jmlValid }} baris</button>
    </form>
  </div>
</div>
@endsection