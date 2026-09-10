@extends('layouts.app')
@section('title', ($row ? 'Ubah ' : 'Tambah ') . $label . ' — IPO')

@section('content')
@php
  $isEdit = (bool) $row;
  $hints = [
    'vo2max' => 'Hasil Tes MFT (Multistage Fitness Test)',
    'perilaku' => 'Praktik aktivitas fisik / psikomotor (1–5)',
    'frekuensi' => 'Minimal 3x/minggu = aktif',
    'pengetahuan' => 'Skala 1–5', 'sikap' => 'Skala 1–5', 'fisik' => 'Skala 1–5', 'psikis' => 'Skala 1–5',
    'resiliensi' => 'Skala 1–5', 'modal_sosial' => 'Skala 1–5',
    'belanja_barang' => 'Rupiah per tahun', 'belanja_jasa' => 'Rupiah per tahun',
    'jumlah_sdm' => 'Pelatih, guru PJOK, instruktur, relawan', 'luas_m2' => 'Total luas dalam m²',
  ];
  $labels = [
    'district_id' => 'Kecamatan', 'village_id' => 'Lokasi (Desa/Kelurahan)', 'city_id' => 'Kota/Kabupaten',
    'respondent_id' => 'Responden', 'year' => 'Tahun', 'age' => 'Usia (tahun)', 'gender' => 'Jenis Kelamin',
    'age_group' => 'Kelompok Usia', 'jumlah_penduduk_5plus' => 'Jumlah Penduduk 5+', 'jumlah_sdm' => 'Jumlah SDM',
    'luas_m2' => 'Luas (m²)', 'pengetahuan' => 'Pengetahuan', 'sikap' => 'Sikap', 'perilaku' => 'Perilaku',
    'frekuensi' => 'Frekuensi/minggu', 'durasi' => 'Durasi (menit)', 'intensitas' => 'Intensitas',
    'vo2max' => 'VO₂max', 'fisik' => 'Fisik', 'psikis' => 'Psikis', 'resiliensi' => 'Resiliensi',
    'modal_sosial' => 'Modal Sosial', 'belanja_barang' => 'Belanja Barang (Rp)', 'belanja_jasa' => 'Belanja Jasa (Rp)',
    'medali_emas' => 'Medali Emas', 'medali_perak' => 'Medali Perak', 'medali_perunggu' => 'Medali Perunggu',
  ];
  $fields = [
    'sdm' => ['district_id', 'year', 'jumlah_penduduk_5plus', 'jumlah_sdm'],
    'ruang-terbuka' => ['village_id', 'year', 'jumlah_penduduk_5plus', 'luas_m2'],
    'literasi-fisik' => ['respondent_id', 'year', 'pengetahuan', 'sikap', 'perilaku'],
    'partisipasi' => ['respondent_id', 'year', 'frekuensi', 'durasi', 'intensitas'],
    'kebugaran' => ['respondent_id', 'year', 'vo2max'],
    'kesehatan' => ['respondent_id', 'year', 'fisik', 'psikis'],
    'perkembangan-personal' => ['respondent_id', 'year', 'resiliensi', 'modal_sosial'],
    'ekonomi' => ['respondent_id', 'year', 'belanja_barang', 'belanja_jasa'],
    'performa' => ['city_id', 'year', 'medali_emas', 'medali_perak', 'medali_perunggu'],
    'responden' => ['village_id', 'year', 'age', 'gender', 'age_group'],
  ][$dim] ?? [];
  $val = fn($k) => old($k, $row[$k] ?? ($k === 'year' ? $year : ''));
@endphp
<div class="anim-fade-up" style="max-width:640px;display:flex;flex-direction:column;gap:14px">
  <div>
    <a href="/data/{{ $dim }}" style="font-size:12px;color:var(--ink-3)">← Kembali</a>
    <h1 style="font-size:18px;font-weight:700;color:var(--ink)">{{ $isEdit ? 'Ubah' : 'Tambah' }} {{ $label }}</h1>
  </div>
  <form method="POST" action="{{ $isEdit ? "/data/{$dim}/{$row['id']}" : "/data/{$dim}" }}" class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
    @csrf
    @if($isEdit) @method('PUT') @endif
    @foreach($fields as $f)
      <div class="input-group">
        <label for="f-{{ $f }}">{{ $labels[$f] ?? $f }} <span style="color:var(--danger)">*</span></label>
        @if($f === 'district_id' && isset($opts['districts']))
          <select id="f-{{ $f }}" name="{{ $f }}" class="input" required>
            <option value="">Pilih kecamatan lokasi data</option>
            @foreach($opts['districts'] as $o)<option value="{{ $o->id }}" {{ (string)$val($f) === (string)$o->id ? 'selected' : '' }}>{{ $o->name }} — {{ $o->city_name }}</option>@endforeach
          </select>
        @elseif($f === 'village_id' && isset($opts['villages']))
          <select id="f-{{ $f }}" name="{{ $f }}" class="input" required>
            <option value="">Pilih desa/kelurahan</option>
            @foreach($opts['villages'] as $o)<option value="{{ $o->id }}" {{ (string)$val($f) === (string)$o->id ? 'selected' : '' }}>{{ $o->name }} — {{ $o->district_name }}, {{ $o->city_name }}</option>@endforeach
          </select>
        @elseif($f === 'city_id' && isset($opts['cities']))
          <select id="f-{{ $f }}" name="{{ $f }}" class="input" required>
            <option value="">Pilih kota/kabupaten</option>
            @foreach($opts['cities'] as $o)<option value="{{ $o->id }}" {{ (string)$val($f) === (string)$o->id ? 'selected' : '' }}>{{ $o->name }} ({{ $o->province_name }})</option>@endforeach
          </select>
        @elseif($f === 'respondent_id' && isset($opts['respondents']))
          <select id="f-{{ $f }}" name="{{ $f }}" class="input" required>
            <option value="">Pilih responden survei</option>
            @foreach($opts['respondents'] as $o)<option value="{{ $o->id }}" {{ (string)$val($f) === (string)$o->id ? 'selected' : '' }}>#{{ $o->id }} — {{ $o->age }} th ({{ $o->gender }}) — {{ $o->village_name }}</option>@endforeach
          </select>
        @elseif($f === 'gender')
          <select id="f-{{ $f }}" name="gender" class="input" required>
            <option value="">Pilih</option>
            <option value="L" {{ $val($f) === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ $val($f) === 'P' ? 'selected' : '' }}>Perempuan</option>
          </select>
        @elseif($f === 'age_group')
          <select id="f-{{ $f }}" name="age_group" class="input" required>
            <option value="">Sesuai kuota sampling IPO</option>
            @foreach(['10–15', '16–30', '31–44', '45–60', '18–25', '26–35', '36–45', '46–55', '56–65'] as $ag)<option {{ $val($f) === $ag ? 'selected' : '' }}>{{ $ag }}</option>@endforeach
          </select>
          <p style="font-size:11px;margin-top:4px;color:var(--ink-3)">Sesuai kuota sampling IPO: 10–15, 16–30, 31–44, 45–60</p>
        @elseif($f === 'year')
          <input id="f-{{ $f }}" name="year" type="number" class="input" value="{{ $val($f) }}" required>
        @elseif($f === 'age')
          <input id="f-{{ $f }}" name="age" type="number" min="10" max="60" inputmode="numeric" class="input" value="{{ $val($f) }}" required>
          <p style="font-size:11px;margin-top:4px;color:var(--ink-3)">Populasi target IPO: 10–60 tahun</p>
        @else
          <input id="f-{{ $f }}" name="{{ $f }}" type="number" step="any" min="0" inputmode="decimal" class="input" value="{{ $val($f) }}" required>
          @if(isset($hints[$f]))<p style="font-size:11px;margin-top:4px;color:var(--ink-3)">{{ $hints[$f] }}</p>@endif
        @endif
      </div>
    @endforeach
    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
  </form>
</div>
@endsection
