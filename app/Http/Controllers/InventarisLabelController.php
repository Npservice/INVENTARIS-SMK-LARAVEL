<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use Illuminate\View\View;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

class InventarisLabelController extends Controller
{
    public function show(Inventaris $inventaris): View
    {
        $inventaris->load('lokasi');

        $labels = collect(range(1, $inventaris->jumlah))->map(fn () => [
            'nama' => $inventaris->nama,
            'kode' => $inventaris->kode_invt,
            'keterangan_kode' => sprintf(
                '%s/%s/%s/%s',
                $inventaris->kode_invt,
                $inventaris->masuk->format('m'),
                $inventaris->masuk->format('Y'),
                str_replace(' ', '', $inventaris->lokasi->nama),
            ),
            'barcode' => DNS1D::getBarcodeSVG($inventaris->kode_invt, 'C128'),
        ]);

        return view('pages.inventaris.label', [
            'inventaris' => $inventaris,
            'labels' => $labels,
        ]);
    }
}
