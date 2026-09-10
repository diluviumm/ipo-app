<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Banding IPO {{ $year }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1E1B2E; }
  h1 { font-size: 17pt; color: #4C1D95; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th, td { border: 1px solid #999; padding: 5px 6px; font-size: 9pt; }
  th { background: #EDE9FE; }
  .meta { color: #555; font-size: 9pt; }
  @page { margin-bottom: 60px; }
  .pagenum:before { content: 'Halaman ' counter(page) ' dari ' counter(pages) ';'; }
  .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 9pt; color: #777; }
</style>
</head>
<body>
<div style="border-bottom:3px double #4C1D95;padding-bottom:6px;margin-bottom:8px">
<div style="font-size:14pt;font-weight:bold;color:#4C1D95">INDEKS PEMBANGUNAN OLAHRAGA (IPO)</div>
<div class="meta">Perbandingan antar provinsi · Tahun {{ $year }}</div>
</div>
<table>
<thead><tr><th>Dimensi</th>@foreach($dims as $d)<th>{{ $d['nama'] }}</th>@endforeach</tr></thead>
<tbody>
@foreach($labels as $k => $label)
<tr><td>{{ $label }}</td>@foreach($dims as $d)<td>{{ $d['skor'][$k] }}</td>@endforeach</tr>
@endforeach
<tr><td><b>IPO</b></td>@foreach($dims as $d)<td><b>{{ $d['ipo'] }}</b></td>@endforeach</tr>
</tbody>
</table>
<div class="footer"><span class="pagenum"></span> · IPO — Data Akurat, Olahraga Maju</div>
</body>
</html>