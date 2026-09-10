@extends('layouts.app')
@section('title', 'Dokumentasi API — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:640px;display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">Dokumentasi API Publik v1</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">Tanpa login · dibatasi 60 request/menit · format JSON.</p>
    @php($eps = [
      ['GET /api/v1/provinsi', 'Daftar 38 provinsi (id, name).'],
      ['GET /api/v1/ranking?year=2024', 'Peringkat + skor + kategori.'],
      ['GET /api/v1/tren/15', 'Tren tahunan satu provinsi (id).'],
    ])
    @foreach($eps as $e)
    <h3 style="font-size:13px;font-weight:800;margin-top:12px">{{ $e[0] }}</h3>
    <p style="font-size:12.5px;color:var(--ink-3)">{{ $e[1] }}</p>
    @endforeach
  </div>
</div>
@endsection