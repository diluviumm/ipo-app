@extends('layouts.app')
@section('title', 'Tong Sampah — IPO')

@section('content')
<div class="anim-fade" style="display:flex;flex-direction:column;gap:14px">
  <div class="page-head">
    <div>
      <h1 style="font-size:18px;font-weight:700;color:var(--ink)">{{ __('Tong Sampah') }}</h1>
      <p style="font-size:12.5px;color:var(--ink-3)">{{ __('Data terhapus tersimpan 30 hari sebelum dibuang permanen.') }}</p>
    </div>
    <form method="POST" action="/sampah/kosongkan" class="no-print" onsubmit="return confirm('Buang permanen semua sampah >30 hari?')">@csrf<button class="btn btn-secondary btn-sm" type="submit">{{ __('Kosongkan lama') }}</button></form>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>{{ __('Dihapus') }}</th><th>{{ __('Dimensi') }}</th><th>ID</th><th>{{ __('Oleh') }}</th><th class="no-print">{{ __('Aksi') }}</th></tr></thead>
      <tbody>
        @forelse($rows as $s)
          <tr>
            <td style="white-space:nowrap">{{ \App\Support\Tgl::id($s->created_at) }}</td>
            <td>{{ $s->dim }}</td>
            <td>{{ $s->row_id }}</td>
            <td>{{ $s->dihapus_oleh ?? '—' }}</td>
            <td class="no-print" style="white-space:nowrap">
              <form method="POST" action="/sampah/{{ $s->id }}/pulih" style="display:inline">@csrf<button class="btn btn-secondary btn-sm" type="submit">{{ __('Pulihkan') }}</button></form>
              <form method="POST" action="/sampah/{{ $s->id }}" style="display:inline" onsubmit="return confirm('Hapus permanen? Tidak bisa kembali.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">{{ __('Permanen') }}</button></form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--ink-3);padding:28px">{{ __('Tong sampah kosong.') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>{{ $rows->links() }}</div>
</div>
@endsection