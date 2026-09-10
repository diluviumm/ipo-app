# Dokumentasi Teknis — Aplikasi IPO (Indeks Pembangunan Olahraga)

> Platform: **PHP Laravel** · Bahasa UI: **Indonesia** · Basis data: **SQLite** · Dokumen: 7 September 2026

Aplikasi web untuk menghimpun data indikator olahraga dari 38 provinsi, menghitung Indeks Pembangunan Olahraga (IPO,
skala 0–100), menampilkannya di dashboard, grafik tren, dan laporan yang bisa diekspor ke PDF atau dicetak.

---

## 1. Arsitektur

```
Browser ──HTTP──▶ Laravel (Blade server-render + Chart.js lokal)
                      │
                      ▼
              SQLite (database/database.sqlite)
```

| Lapisan       | Teknologi                              |
| ------------- | -------------------------------------- |
| Backend       | PHP 8.5, Laravel 13, auth sesi `web`   |
| Frontend      | Blade, 1 file CSS (`public/app.css`)   |
| Grafik        | Chart.js UMD lokal, tanpa CDN/internet |
| PDF           | `barryvdh/laravel-dompdf`              |
| Basis data    | SQLite, 15 tabel                       |
| Uji otomatis  | PHPUnit (`tests/Feature/IpoAuditTest`) |

Tidak ada proses build: cukup `php artisan serve`, tidak perlu Node.js sama sekali.

---

## 2. Peran pengguna

| Peran        | Cakupan data                  | Bisa kelola data | Bisa kelola user |
| ------------ | ----------------------------- | ---------------- | ---------------- |
| Superadmin   | Semua provinsi (bisa pilih)   | Ya               | Ya               |
| Operator     | Provinsinya saja (terkunci)   | Ya               | Tidak (403)      |
| User (biasa) | Lihat saja                    | Tidak (403)      | Tidak (403)      |
| Tamu         | —                             | Diarahkan login  | —                |

Pendaftaran mandiri selalu menjadi `user` (terkunci di controller). Pemilik akun tidak bisa menghapus akunnya
sendiri. Username unik; password di-hash bcrypt pada kolom `password_hash`.

Akun bawaan (dibuat oleh seeder, lihat `database/seeders/DatabaseSeeder.php`):

| Username         | Peran      | Provinsi     |
| ---------------- | ---------- | ------------ |
| `admin`          | Superadmin | — (semua)    |
| `operator_<slug>`| Operator   | 38 provinsi  |
| `user_biasa`     | User       | —            |

---

## 3. Menu & halaman (16+ halaman)

| #  | Menu              | Rute                | Isi |
| -- | ----------------- | ------------------- | --- |
| 1  | Dashboard         | `/dashboard`        | ScoreRing SVG skor 0–100 + kategori, 9 kartu dimensi + progress bar, tren 5 tahun, pemilih provinsi (superadmin) & tahun |
| 2  | Pengelolaan Data  | `/data`             | 10 kartu menu dimensi |
| 3  | Daftar data       | `/data/{dim}`       | Tabel + filter provinsi/tahun + cari + paginasi 20 + Ubah/Hapus (modal konfirmasi) |
| 4  | Tambah/Ubah       | `/data/{dim}/tambah`, `/data/{dim}/{id}/ubah` | Form + validasi server; dropdown responden; petunjuk MFT/psikomotor/kuota |
| 5  | Perhitungan Indeks| `/hitung`           | Skor + rincian 9 dimensi + tombol Hitung Ulang + riwayat hitung |
| 6  | Grafik Indeks     | `/grafik`           | Chart.js garis tren 5 tahun + daftar skor |
| 7  | Laporan           | `/laporan?tab=`     | 4 tab: `index`, `ranking`, `trend`, `dimensions`; Ekspor PDF + Cetak |
| 8  | Bantuan           | `/bantuan`          | Tips, kategori, alur, 9 dimensi |
| 9  | Manajemen User    | `/users`            | Tabel 40 user + Tambah/Ubah/Hapus (superadmin saja) |
| 10 | Masuk/Daftar      | `/login`, `/register` | Form ungu-hijau sesuai desain |

10 dimensi data: `sdm`, `ruang-terbuka`, `literasi-fisik`, `partisipasi`, `kebugaran`, `kesehatan`,
`perkembangan-personal`, `ekonomi`, `performa`, `responden`.

---

## 4. Rumus IPO (9 dimensi, rata-rata sama bobot)

```
IPO = (d1+d2+...+d9) / 9 × 100  →  display 0–100
Kategori: 0–25 Sangat Kurang · 26–50 Kurang · 51–75 Cukup · 76–100 Baik
```

| Dim | Sumber | Rumus per baris / agregat provinsi |
| --- | ------ | ---------------------------------- |
| d1 SDM | `sdm_olahraga` | `jumlah_sdm / (0.005 × penduduk_5plus)` per baris → rata-rata |
| d2 Ruang terbuka | `ruang_terbuka` | proporsi sesi selesai (`durasi ≥ 30`, indoor+outdoor) |
| d3 Literasi | `literasi_fisik` | rata-rata `skor_pengetahuan / 5` |
| d4 Partisipasi | `partisipasi` | proporsi `aktif_3x_minggu` (kolom generated) |
| d5 Kebugaran | `kebugaran` | rata-rata `indeks` (kolom turunan MFT: usia & JK) |
| d6 Kesehatan | `kesehatan` | rata-rata `(fisik+mental)/2/100` |
| d7 Personal | `perkembangan_personal` | rata-rata `(resiliensi+modal_sosial)/2/5` |
| d8 Ekonomi | `ekonomi` | `min(total_belanja / 5.000.000, 1)` |
| d9 Performa | `performa` | `min((emas×5 + perak×3 + perunggu) / 200, 1)` |

Detail implementasi: `app/Services/IpoCalculator.php`. Tahun default = tahun terbaru yang punya data **sumber**
(bukan tabel summary) agar tidak pernah menunjuk tahun kosong.

---

## 5. Skema basis data (15 tabel)

`provinces`, `cities`, `users`, `sdm_olahraga`, `ruang_terbuka`, `respondents`, `literasi_fisik`,
`partisipasi`, `kebugaran`, `kesehatan`, `perkembangan_personal`, `ekonomi`, `performa`,
`ipo_summary` (UNIQUE `province_id+tahun`), `ipo_trend`. Migrasi: `database/migrations/2026_09_07_000001_create_ipo_tables.php`.

Isolasi data: setiap baris ber-`province_id` (langsung atau via responden/kota); operator dikunci ke provinsinya
di `Dimensions::assertOwnProvince()`; lintas-provinsi → 403; data hilang → 404.

---

## 6. Desain & responsif

Mobile-first, satu kolom; palet ungu tua (`#2A1A5E`, `#3B237A`) + putih + hijau (`#16A34A`).
Sidebar 248px hanya ≥1024px; di bawah itu topbar + bottom tabbar (Home·Data·Grafik·Laporan).
Tabel dibungkus `.table-wrap` (geser horizontal di HP). Print CSS menyembunyikan navigasi & tombol.
Terverifikasi: 390px, 768px, 1366px/1919px — tanpa overflow, tanpa error console.

---

## 7. Instalasi & operasi

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed   # ±1 menit: 190 summary + 40 user
php artisan serve --host=0.0.0.0 --port=8000
```

Uji: `php artisan test` (5 grup, 165 assertion). Rute: `php artisan route:list`.
File `database/*.sqlite` tidak ikut Git (lihat `.gitignore`); di server baru jalankan seed di atas.

---

## 8. Riwayat migrasi

Aplikasi ini dimigrasi penuh dari Node.js (Express + React/Vite) ke PHP Laravel di repositori yang sama
(September 2026) agar berjalan dengan satu runtime PHP + SQLite tanpa proses build. Seluruh kontrak perilaku
dipertahankan: rumus 9 dimensi, 15 tabel, 40 akun seed, isolasi `province_id`, kategori & skala 0–100 —
dan diverifikasi oleh `tests/Feature/IpoAuditTest.php` (port dari 51 cek audit aplikasi sebelumnya).

---

## 9. Fitur tambahan (Batch A–D, September 2026)

- **Profil:** ganti password sendiri (verifikasi lama + min. 6 + konfirmasi).
- **Ekspor CSV:** 4 tab laporan (`;`, BOM Excel).
- **Konfirmasi hitung-ulang** via modal; **batas wajar** tiap numerik.
- **Pencarian:** user + filter ranking provinsi.
- **Audit log** (`audit_logs`) + halaman Aktivitas (superadmin) + riwayat per baris (⏱).
- **Pelindung draft:** peringatan keluar saat form kotor.
- **Impor CSV** per dimensi + template contoh, validasi per baris.
- **Banding** 4 provinsi (tabel + bar) · **PWA offline** (`sw.js`).
- **Kop PDF**, **cetak per-bagian**, **notifikasi skor ±3 poin** (🔔 + halaman).
- **ID/EN:** navigasi + auth + tabbar, toggle tersimpan sesi.
- Kebijakan password seragam **min. 6** di semua pintu.

## 10. Fitur tambahan (Batch E–H, 10 Sep 2026)

- **Rate-limit** login + CSV; **tolak password umum** + meter Lemah/Kuat; **retensi audit 90 hari**.
- **Sortir kolom** list/user/ranking; **pratinjau impor** + konfirmasi; **API publik v1** (`/api/v1/*`) + `/api-dok`.
- **Drawer HP bisa scroll** (menu panjang, logout selalu terjangkau).
- **Reset password via token:** superadmin buat token 1 jam di Users → pengguna atur ulang di `/lupa-password`.
- **Kunci akun:** 10x salah → dikunci 15 mnt (423) + throttle.
- **Sistem & cadangan:** `/sistem` (superadmin) info ukuran DB + tombol unduh `.sqlite`.
- **Validasi FK impor:** ID kecamatan/desa/kota/responden tak dikenal → pesan jelas per baris.
- **Cap "Diperbarui"** per dimensi di menu Data (dari audit log).
- **Hapus massal:** centang baris + "Hapus terpilih" (maks 100, scope provinsi dijaga, audit per baris).
- **Notifikasi:** tandai dibaca + hapus; badge 🔔 hanya yang belum dibaca.
- **Peringatan sesi:** banner <5 mnt + tombol perpanjang; **tombol ↑** kembali ke atas (mobile).

## 11. Fitur tambahan (Batch I–K, 10 Sep 2026; kecuali backup otomatis/I1 ditahan)

- **Log login** (`login_logs`: user–IP–agen–berhasil/gagal) + tabel Riwayat Masuk di Aktivitas.
- **Kunci tahun arsip:** 2020–2023 terkunci default; operator ditolak 403, superadmin kelola di `/sistem`.
- **Batas impor** 2.000 baris/file; **retensi notifikasi** ikut bersih 90 hari.
- **Tanggal Indonesia** (`12 Sep 2026`, helper `App\Support\Tgl`).
- **2FA TOTP superadmin** (Google2FA + QR BaconQrCode): aktifkan di Profil → kode 6 digit saat masuk → matikan pakai password.
- **Dasbor nasional** (rata-rata + 3 teratas/terbawah, superadmin) · **Ekspor Excel** (PhpSpreadsheet) semua tab laporan.
- **Tong sampah** (`sampah` + `App\Support\Sampah`): hapus → 30 hari → pulihkan (ID baru) / permanen / kosongkan lama.
- **Pencarian global** `/cari` (provinsi + menu + user) dari topbar · **Status sistem** di `/sistem` (Laravel/PHP/env/jumlah).
- **PDF:** nomor "Halaman X dari Y" + footer di 3 template · **EN** 50+ string baru · **PWA:** ikon maskable + tombol pasang.
- **Aksesibilitas:** `role=dialog` + fokus otomatis semua modal; `robots.txt`; `apple-touch-icon`.
- **Ingat sortir** per dimensi per sesi.

## 12. Fitur tambahan (Batch L, 10 Sep 2026; L1 & I1 dibuang permanen)

- **2FA operator:** admin & operator bisa aktifkan (sebelumnya superadmin saja).
- **Rapikan DB:** `VACUUM` + % ruang kosong di `/sistem` (superadmin).
- **Kunci register:** toggle tutup/buka pendaftaran di Users; halaman tutup + POST 403 saat tutup.
- **Ekspor users CSV** (tanpa password/hash) · **Bantuan §8** fitur lanjutan.
- L1 kode pemulihan 2FA & I1 backup cron: **dibuang permanen** atas keputusan Mael.

## 13. Fitur tambahan (Batch M, 10 Sep 2026)

- **Ingat saya:** checkbox login + kolom `remember_token` (tanpa ini login centang = 500).
- **EN pesan sistem:** 196 kunci (toast/validasi controller + bantuan + §8).
- **Operator lihat nasional:** rata-rata + "peringkat #N" di dasbor (Jatim #14).
- **Batas sesi per peran:** admin 24 jam, operator/user 8 jam (`SetSesiAktif`).

## 14. Keamanan galat (Batch N, 10 Sep 2026)

- **API tak bocorkan trace:** `api/*` selalu JSON generik (`Tidak ditemukan`/`Akses ditolak`/dst.) via `respond()` di `bootstrap/app.php`.
- **`.env.example`:** `APP_ENV=production` + `APP_DEBUG=false` + komentar kapan boleh `true`.

## 15. Pengerasan (Batch O, 10 Sep 2026)

- **Throttle** `POST /register` + `POST /lupa-password` (10/mnt, sejajar login); token reset 12→16 hex.
- **Sanitasi formula CSV/XLSX** (`App\Support\Csv::aman`): sel `= + - @ Tab CR` diawali `'` di 3 ekspor CSV + Excel.

## 16. Interaksi (Batch P, 10 Sep 2026)

- **Banner sesi jujur:** hitung mundur pakai batas per-peran (admin 24 jam, operator/user 8 jam); tombol Perpanjang benar-benar memperpanjang (`sesi_mulai` ikut reset).
- **SW `ipo-v2`:** hanya aset statis yang di-cache; cache `ipo-v1` (berisi halaman privat) dibersihkan otomatis.
