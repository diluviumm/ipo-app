@extends('layouts.app')
@section('title', 'Notifikasi — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <h1 style="font-size:18px;font-weight:700;color:var(--ink)">Notifikasi Skor</h1>
    <span style="font-size:12px;color:var(--ink-3)">Perubahan ≥ 3 poin</span>
  </div>
  @forelse($rows as $r)
    @php($naik = $r->skor_baru >= $r->skor_lama)
    <div class="card" style="padding:14px 16px;display:flex;gap:12px;align-items:center;{{ empty($r->dibaca) ? 'border-left:4px solid var(--brand-600);' : 'opacity:.65;' }}">
      <div style="font-size:22px">{{ $naik ? '📈' : '📉' }}</div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:13.5px">{{ $r->province_name }} · {{ $r->year }}</div>
        <div style="font-size:12.5px;color:var(--ink-3)">{{ $r->skor_lama }} → <b style="color:var(--ink)">{{ $r->skor_baru }}</b> ({{ $naik ? '+' : '' }}{{ round($r->skor_baru - $r->skor_lama, 2) }} poin)</div>
      </div>
      <div class="no-print" style="display:flex;gap:6px">
        @if(empty($r->dibaca))
        <form method="POST" action="/notifikasi/{{ $r->id }}/baca">@csrf<button class="btn btn-secondary btn-sm" type="submit">{{ __('Tandai dibaca') }}</button></form>
        @endif
        <form method="POST" action="/notifikasi/{{ $r->id }}" onsubmit="return confirm('Hapus notifikasi ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">{{ __('Hapus') }}</button></form>
      </div>
    </div>
  @empty
    <div class="card" style="padding:20px;text-align:center;color:var(--ink-3);font-size:13px">Belum ada perubahan besar. Notifikasi muncul saat hitung ulang mengubah skor ≥ 3 poin.</div>
  @endforelse
</div>
@endsection