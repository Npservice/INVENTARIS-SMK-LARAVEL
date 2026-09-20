<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::with(['user', 'inventaris.lokasi.instansi'])
            ->orderByDesc('tanggal_pinjam');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pjm', 'like', "%{$search}%")
                    ->orWhereHas('inventaris', fn ($i) => $i->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_invt', 'like', "%{$search}%"));
            });
        }

        if ($userId = $request->string('id_user')->toString()) {
            $query->where('id_user', $userId);
        }

        if ($inventarisId = $request->string('id_inventaris')->toString()) {
            $query->where('id_inventaris', $inventarisId);
        }

        if ($statusPjm = $request->string('status_pjm')->toString()) {
            $query->where('status_pjm', $statusPjm);
        }

        if ($statusPinjam = $request->string('status_pinjam')->toString()) {
            $query->where('status_pinjam', $statusPinjam);
        }

        if ($kode = $request->string('kode_invt')->toString()) {
            $query->whereHas('inventaris', fn ($i) => $i->where('kode_invt', $kode));
        }

        if ($dari = $request->string('tanggal_pinjam_dari')->toString()) {
            $query->whereDate('tanggal_pinjam', '>=', $dari);
        }

        if ($sampai = $request->string('tanggal_pinjam_sampai')->toString()) {
            $query->whereDate('tanggal_pinjam', '<=', $sampai);
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function show(Peminjaman $peminjaman): JsonResponse
    {
        return response()->json($peminjaman->load(['user', 'inventaris.lokasi.instansi']));
    }

    public function findByKode(Request $request): JsonResponse
    {
        $request->validate(['kode' => ['required', 'string']]);

        $peminjaman = Peminjaman::with(['user', 'inventaris.lokasi.instansi'])
            ->whereHas('inventaris', fn ($i) => $i->where('kode_invt', $request->string('kode')))
            ->where('status_pinjam', 'Dipinjam')
            ->latest('tanggal_pinjam')
            ->first();

        if (! $peminjaman) {
            return response()->json([
                'message' => 'Tidak ada peminjaman aktif untuk kode ini.',
            ], 404);
        }

        return response()->json($peminjaman);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $peminjaman = DB::transaction(function () use ($data) {
            $inventaris = Inventaris::query()->lockForUpdate()->findOrFail($data['id_inventaris']);

            if ($inventaris->jumlah < 1) {
                throw ValidationException::withMessages([
                    'id_inventaris' => 'Stok barang habis, tidak bisa dipinjam.',
                ]);
            }

            $inventaris->decrement('jumlah');

            $data['status_pinjam'] = 'Dipinjam';

            return Peminjaman::create($data);
        });

        return response()->json($peminjaman->load(['user', 'inventaris.lokasi.instansi']), 201);
    }

    public function update(Request $request, Peminjaman $peminjaman): JsonResponse
    {
        $data = $this->validated($request);

        if ($peminjaman->status_pinjam === 'Dipinjam' && $data['id_inventaris'] !== $peminjaman->id_inventaris) {
            DB::transaction(function () use ($peminjaman, $data) {
                $oldInventaris = Inventaris::query()->lockForUpdate()->findOrFail($peminjaman->id_inventaris);
                $newInventaris = Inventaris::query()->lockForUpdate()->findOrFail($data['id_inventaris']);

                if ($newInventaris->jumlah < 1) {
                    throw ValidationException::withMessages([
                        'id_inventaris' => 'Stok barang tujuan habis, tidak bisa dipindah.',
                    ]);
                }

                $oldInventaris->increment('jumlah');
                $newInventaris->decrement('jumlah');

                $peminjaman->update($data);
            });
        } else {
            $peminjaman->update($data);
        }

        return response()->json($peminjaman->load(['user', 'inventaris.lokasi.instansi']));
    }

    public function returnItem(Peminjaman $peminjaman): JsonResponse
    {
        DB::transaction(function () use ($peminjaman) {
            if ($peminjaman->status_pinjam === 'Dipinjam') {
                Inventaris::query()->lockForUpdate()->findOrFail($peminjaman->id_inventaris)->increment('jumlah');
            }

            $peminjaman->update([
                'status_pinjam' => 'Kembali',
                'waktu_kembali' => now()->format('H:i:s'),
            ]);
        });

        return response()->json($peminjaman->load(['user', 'inventaris.lokasi.instansi']));
    }

    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                if ($peminjaman->status_pinjam === 'Dipinjam') {
                    Inventaris::query()->lockForUpdate()->findOrFail($peminjaman->id_inventaris)->increment('jumlah');
                }

                $peminjaman->delete();
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data peminjaman tidak bisa dihapus.',
            ], 409);
        }

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'id_user' => ['required', 'uuid', 'exists:users,id'],
            'id_inventaris' => ['required', 'uuid', 'exists:inventaris,id'],
            'nama_pjm' => ['required', 'string', 'max:20'],
            'status_pjm' => ['required', 'string', 'in:Guru,Siswa,Staff'],
            'waktu_pinjam' => ['nullable', 'date_format:H:i:s'],
            'waktu_kembali' => ['nullable', 'date_format:H:i:s'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keterangan_pjm' => ['nullable', 'string', 'max:70'],
        ]);
    }
}
