<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Illuminate\View\View;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

class PeminjamanReceiptController extends Controller
{
    public function show(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['user', 'inventaris.lokasi.instansi']);

        $barcode = DNS1D::getBarcodeSVG($peminjaman->inventaris->kode_invt, 'C128');

        return view('pages.peminjaman.receipt', [
            'peminjaman' => $peminjaman,
            'barcode' => $barcode,
        ]);
    }
}
