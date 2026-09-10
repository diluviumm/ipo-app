@extends('layouts.app')
@section('title', 'Akses Ditolak — IPO')
@section('content')
<div class="anim-fade-up" style="max-width:480px;margin:40px auto;text-align:center">
  <div class="card" style="padding:32px 24px">
    <div style="font-size:44px">⛔</div>
    <p style="font-size:15px;font-weight:700;margin:8px 0 4px;color:var(--ink)">Akses ditolak</p>
    <p style="font-size:13px;color:var(--ink-2)">{{ $exception->getMessage() ?: 'Akun User hanya dapat melihat data, tidak dapat menambah atau mengubah.' }}</p>
    <a href="/dashboard" class="btn btn-primary" style="margin-top:16px">Kembali ke dashboard</a>
  </div>
</div>
@endsection
