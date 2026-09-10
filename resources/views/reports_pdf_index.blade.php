<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Laporan IPO {{ $row->province_name }} {{ $year }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #1E1B2E; }
  h1 { font-size: 18pt; color: #4C1D95; } h2 { font-size: 13pt; color: #4C1D95; margin-top: 18px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; font-size: 10pt; }
  th { background: #EDE9FE; }
  .score { font-size: 28pt; font-weight: bold; color: #4C1D95; }
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
<h1>Laporan Indeks Pembangunan Olahraga</h1>
<p class="meta">{{ $row->province_name }} — Tahun {{ $year }} — Kategori: {{ $row->kategori }}</p>
<p class="score">{{ number_format($score, $score == round($score) ? 0 : 2, ',', '.') }} / 100</p>
<h2>Rincian 9 Dimensi</h2>
<table>
  <thead><tr><th>No</th><th>Dimensi</th><th>Skor</th></tr></thead>
  <tbody>
    @foreach($labels as $k => $label)
      <tr><td>{{ $loop->iteration }}</td><td>{{ $label }}</td><td>{{ (int) round($row->$k * 100) }}</td></tr>
    @endforeach
  </tbody>
</table>
<p class="meta">Dicetak dari Aplikasi IPO — Data Akurat, Olahraga Maju, Masyarakat Sehat!</p>
<div class="footer"><span class="pagenum"></span> · IPO — Data Akurat, Olahraga Maju</div>
</body>
</html>
