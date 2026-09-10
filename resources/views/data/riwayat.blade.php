@extends('layouts.app')
@section('title', 'Riwayat Baris — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div>
    <a href="/data/{{ $dim }}" class="btn btn-secondary btn-sm">← Kembali</a>
  </div>
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">Riwayat: {{ $label }} #{{ $id }}</h1>
    @forelse($logs as $l)
      <div style="border-top:1px solid var(--border);padding:10px 0;font-size:12.5px">
        <div><b>{{ $l->username ?? '—' }}</b> · {{ $l->aksi }} · {{ $l->created_at }}</div>
        @if($l->detail)<div style="color:var(--ink-3);word-break:break-word">{{ substr($l->detail, 0, 400) }}</div>@endif
      </div>
    @empty
      <p style="font-size:13px;color:var(--ink-3)">Belum ada riwayat untuk baris ini.</p>
    @endforelse
  </div>
</div>
@endsection