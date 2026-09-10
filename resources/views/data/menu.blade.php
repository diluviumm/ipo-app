@extends('layouts.app')
@section('title', 'Pengelolaan Data — IPO')

@section('content')
@php
$menus = [
  ['key' => 'sdm', 'label' => 'SDM Olahraga', 'desc' => 'Ketenagaan & SDM olahraga', 'tint' => '#7C3AED', 'bg' => '#EDE9FE'],
  ['key' => 'ruang-terbuka', 'label' => 'Fasilitas Olahraga', 'desc' => 'GOR, lapangan, kolam renang', 'tint' => '#16A34A', 'bg' => '#DCFCE7'],
  ['key' => 'literasi-fisik', 'label' => 'Literasi Fisik', 'desc' => 'Pengetahuan aktivitas fisik', 'tint' => '#2563EB', 'bg' => '#DBEAFE'],
  ['key' => 'partisipasi', 'label' => 'Partisipasi Masyarakat', 'desc' => 'Keterlibatan berolahraga', 'tint' => '#D97706', 'bg' => '#FEF3C7'],
  ['key' => 'kebugaran', 'label' => 'Kebugaran Jasmani', 'desc' => 'Tes MFT & VO2Max', 'tint' => '#DC2626', 'bg' => '#FEE2E2'],
  ['key' => 'kesehatan', 'label' => 'Kesehatan', 'desc' => 'Fisik & psikis responden', 'tint' => '#0D9488', 'bg' => '#CCFBF1'],
  ['key' => 'perkembangan-personal', 'label' => 'Perkembangan Personal', 'desc' => 'Resiliensi & modal sosial', 'tint' => '#4F46E5', 'bg' => '#E0E7FF'],
  ['key' => 'ekonomi', 'label' => 'Ekonomi', 'desc' => 'Belanja barang & jasa', 'tint' => '#0891B2', 'bg' => '#CFFAFE'],
  ['key' => 'performa', 'label' => 'Performa Olahraga', 'desc' => 'Medali & prestasi', 'tint' => '#EA580C', 'bg' => '#FFEDD5'],
  ['key' => 'responden', 'label' => 'Responden Survei', 'desc' => 'Subjek survei & kuota', 'tint' => '#0E7490', 'bg' => '#CFFAFE'],
];
@endphp
<div class="anim-fade" style="display:flex;flex-direction:column;gap:16px">
  <div class="card" style="padding:20px;background:linear-gradient(120deg,#5B21B6,#4C1D95)">
    <h1 style="color:#fff;font-size:18px;font-weight:800">Pilih Menu Data</h1>
    <p style="color:rgba(255,255,255,.75);font-size:12.5px;margin-top:4px">Kelola data indikator pembangunan olahraga yang ingin diinput</p>
  </div>
  <div class="grid-cards stagger">
    @foreach($menus as $m)
      <a href="/data/{{ $m['key'] }}" class="card card-hover" style="padding:16px;text-decoration:none;display:block">
        <div style="display:flex;gap:14px;align-items:flex-start">
          <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:16px" data-bg="{{ $m['bg'] }}" data-color="{{ $m['tint'] }}">{{ strtoupper(substr($m['label'], 0, 1)) }}</div>
          <div style="min-width:0">
            <h3 style="font-size:14px;font-weight:700;color:var(--ink)">{{ $m['label'] }}</h3>
            <p style="font-size:12px;margin-top:4px;color:var(--ink-3)">{{ $m['desc'] }}</p>
            <p style="font-size:11px;margin-top:2px;color:var(--ink-3)">{{ __('Diperbarui') }}: {{ \App\Support\Tgl::id($segar[$m['key']] ?? null) }}</p>
          </div>
        </div>
      </a>
    @endforeach
  </div>
</div>
@endsection
