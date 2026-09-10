@extends('layouts.app')
@section('title', 'Impor CSV — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:560px;display:flex;flex-direction:column;gap:14px">
  <div>
    <a href="/data/{{ $dim }}" class="btn btn-secondary btn-sm">← Kembali</a>
  </div>
  <div class="card" style="padding:20px">
    <h1 style="font-size:17px;font-weight:800">Impor CSV: {{ $label }}</h1>
    <p style="font-size:12.5px;color:var(--ink-3)">Pisahkan kolom dengan <b>;</b>. Baris pertama wajib header persis seperti di bawah. <a href="/data/{{ $dim }}/contoh">Unduh contoh</a>.</p>
    <p style="font-size:12px;margin-top:8px"><b>Kolom:</b> {{ implode(', ', $fields) }}</p>
    @if($errors->any())<div class="alert-err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="/data/{{ $dim }}/impor" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
      @csrf
      <input type="file" name="file" accept=".csv,.txt" required class="input">
      <div style="display:flex;gap:8px">
        <button class="btn btn-secondary" type="submit" name="aksi" value="pratinjau" style="flex:1">Pratinjau Dulu</button>
        <button class="btn btn-primary" type="submit" name="aksi" value="impor" style="flex:1">Langsung Impor</button>
      </div>
    </form>
  </div>
</div>
@endsection