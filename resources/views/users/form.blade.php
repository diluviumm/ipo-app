@extends('layouts.app')
@section('title', ($row ? 'Ubah' : 'Tambah') . ' User — IPO')

@section('content')
<div class="anim-fade-up" style="max-width:560px;display:flex;flex-direction:column;gap:14px">
  <div>
    <a href="/users" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
    <h1 style="font-size:18px;font-weight:700;color:var(--ink)">{{ $row ? 'Ubah' : 'Tambah' }} User</h1>
  </div>
  <form method="POST" action="{{ $row ? "/users/{$row['id']}" : '/users' }}" class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
    @csrf
    @if($row) @method('PUT') @endif
    <div class="input-group"><label for="u-name">Nama lengkap</label><input id="u-name" name="full_name" class="input" value="{{ old('full_name', $row['full_name'] ?? '') }}" required></div>
    <div class="input-group"><label for="u-user">Username</label><input id="u-user" name="username" class="input" value="{{ old('username', $row['username'] ?? '') }}" {{ $row ? 'disabled' : 'required' }}></div>
    <div class="input-group"><label for="u-pass">Password {{ $row ? '(kosongkan jika tidak diubah)' : '' }}</label><input id="u-pass" name="password" type="password" class="input" {{ $row ? '' : 'required' }}></div>
    <div class="input-group"><label for="u-role">Peran</label>
      <select id="u-role" name="role" class="input" required>
        @foreach(['admin' => 'Admin', 'operator' => 'Operator', 'user' => 'User'] as $v => $l)<option value="{{ $v }}" {{ old('role', $row['role'] ?? 'user') === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
      </select>
    </div>
    <div class="input-group"><label for="u-prov">Provinsi (kosongkan untuk Superadmin)</label>
      <select id="u-prov" name="province_id" class="input">
        <option value="">Semua Provinsi (Superadmin)</option>
        @foreach($provinces as $p)<option value="{{ $p->id }}" {{ (string) old('province_id', $row['province_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
    </form>
    <div style="margin-top:8px">
    <div class="progress"><div id="pw-bar" data-w="0"></div></div>
    <div id="pw-teks" style="font-size:11.5px;color:var(--ink-3);margin-top:4px"></div>
    </div>
    <script>
    (function () {
      var i = document.getElementById('u-pass');
      if (!i) return;
      i.addEventListener('input', function () {
        var v = i.value, s = 0;
        if (v.length > 5) { s = s + 1; }
        if (v.length > 9) { s = s + 1; }
        var campur = /[A-Z]/.test(v) && /[a-z]/.test(v);
        if (campur) { s = s + 1; }
        if (/\d/.test(v)) { s = s + 1; }
        var b = document.getElementById('pw-bar');
        b.style.width = (s * 20) + '%';
        b.className = s < 2 ? 'bar-red' : 'bar-green';
        document.getElementById('pw-teks').textContent = s < 2 ? 'Lemah' : 'Kuat';
      });
    })();
    </script>
</div>
@endsection
