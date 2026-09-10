<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Peringkat IPO {{ $year }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #1E1B2E; }
  h1 { font-size: 18pt; color: #4C1D95; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; font-size: 10pt; }
  th { background: #EDE9FE; }
  .meta { color: #555; font-size: 10pt; }
  @page { margin-bottom: 60px; }
  .pagenum:before { content: 'Halaman ' counter(page) ' dari ' counter(pages) ';'; }
  .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 9pt; color: #777; }
</style>
</head>
<body>
<div style="border-bottom:3px double #4C1D95;padding-bottom:8px;margin-bottom:10px">
  <div style="font-size:15pt;font-weight:bold;color:#4C1D95">INDEKS PEMBANGUNAN OLAHRAGA (IPO)</div>
  <div style="font-size:10pt;color:#555">Aplikasi pendataan indikator olahraga 38 provinsi · Tahun {{ $year }}</div>
</div>
<h1>Peringkat Provinsi — Indeks Pembangunan Olahraga {{ $year }}</h1>
<table>
  <thead><tr><th>No</th><th>Provinsi</th><th>Skor</th><th>Kategori</th></tr></thead>
  <tbody>
    @foreach($rows as $r)
      <tr><td>{{ $loop->iteration }}</td><td>{{ $r->province_name }}</td><td>{{ (int) round($r->ipo_score * 100) }}</td><td>{{ $r->kategori }}</td></tr>
    @endforeach
  </tbody>
</table>
<p class="meta">Dicetak dari Aplikasi IPO — Data Akurat, Olahraga Maju, Masyarakat Sehat!</p>
<div class="footer"><span class="pagenum"></span> · IPO — Data Akurat, Olahraga Maju</div>
</body>
</html>
