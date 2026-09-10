@extends('layouts.app')
@section('title', 'Bantuan — IPO')

@section('content')
<div class="anim-fade-up" style="display:flex;flex-direction:column;gap:16px">
  <div>
    <a href="/dashboard" class="btn btn-secondary btn-sm">← Kembali</a>
  </div>

  <div class="card" style="padding:20px">
    <h1 style="font-size:19px;font-weight:800;color:var(--ink)">Bantuan</h1>
    <p style="font-size:13px;color:var(--ink-3)">{{ __('Panduan lengkap tiap fitur: fungsi & maksud, cara penggunaan, dan aturan main.') }}</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
      <a class="btn btn-secondary btn-sm" href="#f-dash">Dashboard</a>
      <a class="btn btn-secondary btn-sm" href="#f-data">Data</a>
      <a class="btn btn-secondary btn-sm" href="#f-hitung">Hitung</a>
      <a class="btn btn-secondary btn-sm" href="#f-grafik">Grafik</a>
      <a class="btn btn-secondary btn-sm" href="#f-lapor">Laporan</a>
      <a class="btn btn-secondary btn-sm" href="#f-user">User</a>
      <a class="btn btn-secondary btn-sm" href="/api-dok">API Publik</a>
    </div>
  </div>

  <div class="card" style="padding:20px" id="f-dash">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('1 · Dashboard') }}</h2>
    <p style="font-size:13px"><b>{{ __('Fungsi & maksud:') }}</b> ringkasan skor IPO satu provinsi + satu tahun dalam sekali lihat.</p>
    <p style="font-size:13px"><b>{{ __('Cara pakai:') }}</b> pilih provinsi (superadmin) dan tahun di kartu atas → ScoreRing, 9 kartu dimensi, dan tren 5 tahun ikut berubah. Klik "Perhitungan →" untuk rincian.</p>
  </div>

  <div class="card" style="padding:20px" id="f-data">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('2 · Pengelolaan Data') }}</h2>
    <p style="font-size:13px"><b>Fungsi &amp; maksud:</b> pintu masuk 10 jenis data (9 dimensi + responden survei).</p>
    <p style="font-size:13px"><b>Cara pakai:</b> pilih kartu dimensi → daftar dengan filter provinsi/tahun/pencarian (20 baris per halaman) → Tambah / Ubah / Hapus. Hapus selalu minta konfirmasi. Angka negatif dan kolom kosong ditolak.</p>
  </div>
  <div class="card" style="padding:20px">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('3 · Sembilan dimensi') }}</h2>
    @php($panduan = [
    ['SDM Olahraga', 'Mengukur kecukupan tenaga keolahragaan (pelatih, instruktur, wasit, pengelola) per kecamatan. Maksudnya: fasilitas semegah apa pun tidak termanfaatkan tanpa SDM yang cukup.', 'Isi kecamatan, penduduk usia 5+, lalu jumlah SDM.', 'indeks = (sdm ÷ penduduk) ÷ 0,005. Misal 188 SDM dari 63.499 jiwa → 0,59.'],
    ['Ruang Terbuka', 'Mengukur ketersediaan ruang dan fasilitas olahraga (GOR, lapangan, kolam renang) per desa. Maksudnya: akses fisik yang dekat dari rumah menentukan mau tidaknya warga berolahraga.', 'Isi desa, luas m², dan penduduk desa.', 'indeks = (luas ÷ penduduk) ÷ 3,5 m²/jiwa.'],
    ['Literasi Fisik', 'Mengukur pengetahuan, sikap, dan perilaku aktivitas fisik warga lewat 3 angket. Maksudnya: warga yang paham manfaat olahraga lebih mudah diajak bergerak.', 'Pilih responden, isi 3 angket skala 1–5.', 'indeks = rata-rata ÷ 5. Misal 4+3+5 → 4,0 → 0,80.'],
    ['Partisipasi', 'Mengukur keterlibatan nyata warga dalam berolahraga (hasil akhir pembinaan). Isi frekuensi, durasi, intensitas per responden; skor provinsi = proporsi yang aktif minimal 3× seminggu.', 'Isi angket tiap responden.', 'Skor = aktif ÷ total responden.'],
    ['Kebugaran', 'Mengukur kondisi fisik terukur lewat VO2max dari tes MFT. Bukti fisiologis bahwa warga benar-benar bugar, bukan sekadar mengaku aktif.', 'Pilih responden, isi tahun + VO2max.', 'indeks = (vo2 − 20,1) ÷ 32. Misal 35 → 0,47.'],
    ['Kesehatan', 'Mengukur kesehatan fisik dan psikis yang dirasakan (keluhan, kebugaran, stres, semangat). Cermin tujuan akhir: olahraga untuk sehat jiwa-raga.', 'Dua angket 1–5 per responden.', 'indeks = rata-rata ÷ 5.'],
    ['Personal', 'Mengukur resiliensi dan modal sosial (dampak karakter dan sosial olahraga).', 'Dua angket 1–5 per responden.', 'indeks = rata-rata ÷ 5.'],
    ['Ekonomi', 'Mengukur daya dukung ekonomi: belanja barang dan jasa olahraga. Partisipasi butuh biaya; belanja rendah = sinyal hambatan ekonomi.', 'Isi rupiah belanja per responden.', 'indeks = total ÷ 5.000.000 (maks 1).'],
    ['Performa', 'Mengukur prestasi puncak: medali per kota/kabupaten sebagai ujung piramida pembinaan.', 'Isi emas, perak, perunggu per kota + tahun.', 'indeks = (E×5+P×3+R) ÷ 200. Misal 10+8+12 → 86 → 0,43.'],
    ['Responden', 'Subjek survei dan kuota sampling: fondasi seluruh angket dimensi 3–8. Tanpa responden, angket tidak bisa diisi.', 'Isi desa, tahun, usia 10–60, L/P (kelompok usia otomatis).'],
    ])
    @foreach($panduan as $i => $g)
    <h3 style="font-size:13.5px;font-weight:800;color:var(--ink);margin:12px 0 4px">{{ $i+1 }}. {{ $g[0] }}</h3>
    <p style="font-size:13px"><b>Fungsi &amp; maksud:</b> {{ $g[1] }}</p>
    <p style="font-size:13px"><b>Cara pakai:</b> {{ $g[2] }}</p>
    @if(isset($g[3]))<p style="font-size:13px"><b>Rumus:</b> {{ $g[3] }}</p>@endif
    @endforeach
  </div>
  <div class="card" style="padding:20px" id="f-hitung">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('4 · Perhitungan') }}</h2>
    <p style="font-size:13px"><b>Fungsi:</b> rata-rata 9 dimensi (×100). <b>Cara pakai:</b> pilih provinsi+tahun → "⟳ Hitung Ulang". Idempoten; tanpa data = tidak disimpan.</p>
  </div>
  <div class="card" style="padding:20px" id="f-grafik">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('5 · Grafik') }}</h2>
    <p style="font-size:13px"><b>Fungsi:</b> garis tren 5 tahun + rincian per tahun. Offline (Chart.js lokal).</p>
  </div>
  <div class="card" style="padding:20px" id="f-lapor">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('6 · Laporan') }}</h2>
    <p style="font-size:13px"><b>Fungsi:</b> 4 tab — Indeks, Peringkat 38 provinsi, Tren, Analisis Dimensi (cari dimensi terlemah). <b>Ekspor PDF</b> + <b>Cetak</b> (navigasi disembunyikan otomatis).</p>
  </div>
  <div class="card" style="padding:20px" id="f-user">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('7 · Manajemen User') }}</h2>
    <p style="font-size:13px"><b>Fungsi (superadmin):</b> kelola 40 akun. Username unik; password min. 6 (opsional saat ubah); operator wajib provinsi. <b>Tidak bisa hapus akun sendiri.</b></p>
  </div>
  <div class="card" style="padding:18px">
    <h2 style="font-size:15px;font-weight:800;color:var(--brand-700);margin-bottom:8px">{{ __('8 · Fitur Lanjutan') }}</h2>
    <p style="font-size:13px"><b>Banding:</b> {{ __('pilih s.d. 4 provinsi + tahun → tabel, grafik, PDF landscape.') }}</p>
    <p style="font-size:13px"><b>Impor CSV:</b> template <code>;</code> → pratinjau (valid/gagal) → konfirmasi. {{ __('Maks 2.000 baris; ID tak dikenal ditolak per baris.') }}</p>
    <p style="font-size:13px"><b>Hapus massal:</b> centang baris → Hapus terpilih (maks 100). {{ __('Data masuk tong sampah 30 hari → pulihkan/permanen.') }}</p>
    <p style="font-size:13px"><b>Kunci tahun:</b> {{ __('2020–2023 arsip (superadmin kelola di Sistem); operator ditolak.') }}</p>
    <p style="font-size:13px"><b>Lupa password:</b> {{ __('minta token ke superadmin (Users → Reset, 1 jam) → atur ulang di halaman masuk.') }}</p>
    <p style="font-size:13px"><b>2FA:</b> {{ __('Admin & operator bisa aktifkan kode 6 digit di Profil → wajib tiap masuk.') }}</p>
    <p style="font-size:13px"><b>Sistem (superadmin):</b> {{ __('unduh cadangan DB, kunci tahun, rapikan DB, status.') }}</p>
    <p style="font-size:13px"><b>Laporan:</b> {{ __('PDF (nomor halaman), CSV, Excel.') }} <b>API publik:</b> <code>/api/v1/*</code>, dokumen di API.</p>
  </div>
</div>
@endsection
