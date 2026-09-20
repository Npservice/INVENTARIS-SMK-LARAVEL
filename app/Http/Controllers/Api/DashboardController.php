<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'total_inventaris' => (int) Inventaris::query()->sum('jumlah'),
                'stok_gudang' => (int) Inventaris::query()
                    ->whereHas('lokasi', fn ($q) => $q->where('is_gudang', true))
                    ->sum('jumlah'),
                'peminjaman_aktif' => Peminjaman::query()->where('status_pinjam', 'Dipinjam')->count(),
                'perlu_perawatan' => Inventaris::query()->where('kondisi', 'Rusak')->count(),
            ],
            'peminjaman_terbaru' => Peminjaman::with('inventaris')
                ->orderByDesc('tanggal_pinjam')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Peminjaman $peminjaman) => [
                    'barang' => $peminjaman->inventaris->nama,
                    'peminjam' => $peminjaman->nama_pjm,
                    'tanggal' => $peminjaman->tanggal_pinjam->format('d M Y'),
                    'status' => $peminjaman->status_pinjam,
                ]),
        ]);
    }
}
