<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk Peminjaman - {{ $peminjaman->nama_pjm }}</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
        width: 76mm;
        margin: 0 auto;
        padding: 3mm 2mm;
        font-family: 'Courier New', Courier, monospace;
        font-size: 10.5pt;
        line-height: 1.35;
        color: #000;
    }
    .center { text-align: center; }
    .bold { font-weight: 700; }
    hr { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
    table { width: 100%; border-collapse: collapse; }
    table td { padding: 0.3mm 0; vertical-align: top; }
    table td:first-child { width: 32mm; }
    .barcode { display: flex; justify-content: center; margin: 2mm 0; }
    .barcode svg { width: 100%; height: 14mm; }
    .no-print { text-align: center; margin-bottom: 4mm; }
    @media print { .no-print { display: none; } }
</style>
</head>
<body>
<div class="no-print"><button onclick="window.print()">Cetak</button></div>

<p class="center bold" style="font-size: 12pt; margin: 0;">SMK ANNUR BULULAWANG</p>
<p class="center" style="margin: 0 0 2mm;">Struk Peminjaman Barang</p>
<hr>

<table>
    <tr><td>Peminjam</td><td>: {{ $peminjaman->nama_pjm }}</td></tr>
    <tr><td>Status</td><td>: {{ $peminjaman->status_pjm }}</td></tr>
    <tr><td>Barang</td><td>: {{ $peminjaman->inventaris->nama }}</td></tr>
    <tr><td>Kode</td><td>: {{ $peminjaman->inventaris->kode_invt }}</td></tr>
    <tr><td>Lokasi</td><td>: {{ $peminjaman->inventaris->lokasi->nama }}</td></tr>
    <tr><td>Tgl Pinjam</td><td>: {{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</td></tr>
    <tr><td>Tgl Kembali</td><td>: {{ $peminjaman->tanggal_kembali->format('d/m/Y') }}</td></tr>
    @if ($peminjaman->keterangan_pjm)
        <tr><td>Ket</td><td>: {{ $peminjaman->keterangan_pjm }}</td></tr>
    @endif
</table>

<hr>
<div class="barcode">{!! $barcode !!}</div>
<p class="center" style="margin: 0;">{{ $peminjaman->inventaris->kode_invt }}</p>
<hr>

<p class="center" style="margin: 0;">Bawa &amp; tunjukkan struk ini<br>saat mengembalikan barang.</p>

<script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
