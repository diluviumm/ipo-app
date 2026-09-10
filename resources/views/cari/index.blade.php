@extends('layouts.app')
@section('title', 'Pencarian — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:16px 18px">
    <h1 style="font-size:16px;font-weight:800">{{ __('Hasil untuk') }} "{{ $q }}"</h1>
  </div>
  @if(count($hasil['provinsi']))
  <div class="card" style="padding:16px 18px">
    <h2 style="font-size:14px;font-weight:700">{{ __('Provinsi') }}</h2>
    @foreach($hasil['provinsi'] as $p)
      <div style="padding:6px 0;border-top:1px solid var(--border);font-size:13.5px"><a href="/dashboard?province_id={{ $p->id }}" style="font-weight:600;color:var(--brand-600)">{{ $p->name }}</a></div>
    @endforeach
  </div>
  @endif
  @if(count($hasil['menu']))
  <div class="card" style="padding:16px 18px">
    <h2 style="font-size:14px;font-weight:700">{{ __('Menu Data') }}</h2>
    @foreach($hasil['menu'] as $m)
      <div style="padding:6px 0;border-top:1px solid var(--border);font-size:13.5px"><a href="/data/{{ $m['key'] }}" style="font-weight:600;color:var(--brand-600)">{{ $m['label'] }}</a></div>
    @endforeach
  </div>
  @endif
  @if(count($hasil['user']))
  <div class="card" style="padding:16px 18px">
    <h2 style="font-size:14px;font-weight:700">{{ __('Pengguna') }}</h2>
    @foreach($hasil['user'] as $x)
      <div style="padding:6px 0;border-top:1px solid var(--border);font-size:13.5px"><a href="/users?q={{ urlencode($x->username) }}" style="font-weight:600;color:var(--brand-600)">{{ $x->username }}</a> <span style="color:var(--ink-3)">· {{ $x->full_name }}</span></div>
    @endforeach
  </div>
  @endif
  @if(!count($hasil['provinsi']) && !count($hasil['menu']) && !count($hasil['user']))
    <div class="card" style="padding:20px;text-align:center;color:var(--ink-3);font-size:13px">{{ __('Tidak ditemukan. Minimal 2 huruf.') }}</div>
  @endif
</div>
@endsection
