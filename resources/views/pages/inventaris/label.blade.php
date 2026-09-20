<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Label - {{ $inventaris->nama }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        @page { size: A4 portrait; margin: 8mm; }

        .label-sheet {
            display: grid;
            grid-template-columns: repeat(3, 62mm);
            grid-auto-rows: 26mm;
            gap: 2mm;
            width: 190mm;
            margin: 0 auto 8mm;
        }
        .label-card {
            display: grid;
            grid-template-rows: 3.5mm 5.6mm 8mm 7.9mm;
            width: 62mm;
            height: 26mm;
            padding: 0;
            overflow: hidden;
            border: .2mm solid #94a3b8;
            border-top: .8mm solid #1d4ed8;
            border-radius: 1mm;
            background: #fff;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
            break-inside: avoid;
        }
        .label-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1mm;
            padding: 0 1.8mm;
            background: #e0e7ff;
            color: #1e3a8a;
            font-size: 5.6pt;
            font-weight: 800;
            letter-spacing: .07em;
            line-height: 1;
            white-space: nowrap;
        }
        .label-brand span:last-child { color: #475569; font-weight: 700; letter-spacing: 0; }
        .label-name {
            display: -webkit-box;
            margin: 0;
            overflow: hidden;
            padding: .5mm 1.8mm 0;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            color: #0f172a;
            font-size: 7.2pt;
            font-weight: 700;
            line-height: 1.08;
            text-align: left;
        }
        .label-barcode {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0 1.8mm;
        }
        .label-barcode svg { display: block; width: 100%; max-width: 100%; height: 7.5mm; margin: 0 auto; }
        .label-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: .3mm 1.8mm;
            background: #eff6ff;
            text-align: center;
        }
        .label-code {
            width: 100%;
            margin: 0;
            color: #0f172a;
            font-size: 6.1pt;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .025em;
            text-align: center;
        }
        .label-meta {
            max-width: 100%;
            margin: .3mm 0 0;
            overflow: hidden;
            color: #475569;
            font-size: 5.1pt;
            line-height: 1;
            text-overflow: ellipsis;
            text-align: center;
            white-space: nowrap;
        }
        @media screen {
            .label-sheet { padding: 6mm; box-sizing: content-box; border: 1px solid #e2e8f0; box-shadow: 0 12px 32px rgba(15, 23, 42, .08); background: #fff; }
            .sheet-scroll { overflow-x: auto; }
        }
        @media print {
            html, body { width: auto; min-height: 0; margin: 0; padding: 0; background: #fff !important; }
            .no-print { display: none !important; }
            .sheet-scroll { overflow: visible; }
            .label-sheet { margin: 0 auto; break-after: page; page-break-after: always; }
            .label-sheet:last-child { break-after: auto; page-break-after: auto; }
            .label-card { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-6 font-sans text-slate-700 antialiased">
    <div class="no-print mb-6 flex items-center justify-center gap-4">
        <span class="text-sm font-semibold text-slate-600">{{ $labels->count() }} label · {{ $labels->chunk(30)->count() }} lembar A4</span>
        <button type="button" onclick="window.print()" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Cetak label</button>
    </div>
    <main class="sheet-scroll">
        @foreach ($labels->chunk(30) as $sheet)
            <div class="label-sheet" aria-label="Lembar label {{ $loop->iteration }}">
                @foreach ($sheet as $label)
                    <article class="label-card">
                        <div class="label-brand">
                            <span>SMK ANNUR · INVENTARIS</span>
                            <span>{{ sprintf('%02d / %02d', $loop->parent->index * 30 + $loop->iteration, $labels->count()) }}</span>
                        </div>
                        <p class="label-name">{{ $label['nama'] }}</p>
                        <div class="label-barcode">{!! $label['barcode'] !!}</div>
                        <div class="label-footer">
                            <p class="label-code">{{ $label['kode'] }}</p>
                            <p class="label-meta">{{ $inventaris->lokasi->nama }} · {{ $inventaris->masuk->format('m/Y') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endforeach
    </main>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
