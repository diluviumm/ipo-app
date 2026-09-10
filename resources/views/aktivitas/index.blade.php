@extends('layouts.app')
@section('title', 'Aktivitas — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <h1 style="font-size:18px;font-weight:700;color:var(--ink)">Aktivitas Pengguna</h1>
    <form method="POST" action="/aktivitas/bersih" class="no-print">
      @csrf
      <button class="btn btn-secondary btn-sm" type="submit">Bersihkan &gt;90 hari</button>
    </form>
  </div>
  <form method="GET" action="/aktivitas" class="toolbar no-print">
    <select name="aksi" class="input" style="width:auto" onchange="this.form.submit()">
      <option value="">Semua aksi</option>
      @foreach(['tambah','ubah','hapus','hitung'] as $a)
        <option value="{{ $a }}" {{ request('aksi') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
      @endforeach
    </select>
    <input type="text" name="q" class="input" style="max-width:200px" placeholder="Cari username…" value="{{ request('q') }}">
    <input type="date" name="dari" class="input" style="width:auto" value="{{ request('dari') }}" aria-label="Dari tanggal">
    <input type="date" name="sampai" class="input" style="width:auto" value="{{ request('sampai') }}" aria-label="Sampai tanggal">
    <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
    <a class="btn btn-secondary btn-sm" href="/aktivitas/ekspor?{{ http_build_query(request()->only(['aksi','q','dari','sampai'])) }}">⭳ {{ __('Ekspor CSV') }}</a>
  </form>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Tabel</th><th>ID</th><th>Detail</th></tr></thead>
      <tbody>
        @forelse($logs as $l)
          <tr>
            <td style="white-space:nowrap">{{ \App\Support\Tgl::id($l->created_at) }}</td>
            <td>{{ $l->username ?? '—' }}</td>
            <td><span class="badge badge-neutral">{{ $l->aksi }}</span></td>
            <td>{{ $l->tabel }}</td>
            <td>{{ $l->row_id ?? '—' }}</td>
            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px">{{ $l->detail ? substr($l->detail, 0, 90) : '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;color:var(--ink-3)">Belum ada aktivitas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>{{ $logs->links() }}</div>
  <h2 style="font-size:15px;font-weight:700;color:var(--ink);margin-top:8px">{{ __('Riwayat Masuk (30 terakhir)') }}</h2>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Waktu</th><th>User</th><th>Status</th><th>IP</th><th>Peramban</th></tr></thead>
      <tbody>
        @forelse($masuk as $m)
          <tr>
            <td style="white-space:nowrap">{{ \App\Support\Tgl::id($m->created_at) }}</td>
            <td>{{ $m->username }}</td>
            <td>@if($m->berhasil)<span class="badge badge-baik">Berhasil</span>@else<span class="badge badge-kurang">Gagal</span>@endif</td>
            <td>{{ $m->ip ?? '—' }}</td>
            <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px">{{ $m->agen ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--ink-3)">{{ __('Belum ada riwayat masuk.') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection