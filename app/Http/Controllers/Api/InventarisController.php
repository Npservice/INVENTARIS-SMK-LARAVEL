<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Support\InventarisKode;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->filtered($request)->simplePaginate($request->integer('size', 15)));
    }

    public function filtered(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Inventaris::with(['jenis', 'lokasi.instansi', 'pendanaan'])
            ->orderBy('nama');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_invt', 'like', "%{$search}%");
            });
        }

        if ($lokasiId = $request->string('lokasi_id')->toString()) {
            $query->where('lokasi_id', $lokasiId);
        }

        if ($instansiId = $request->string('instansi_id')->toString()) {
            $query->whereHas('lokasi', fn ($q) => $q->where('instansi_id', $instansiId));
        }

        if ($request->boolean('gudang')) {
            $query->whereHas('lokasi', fn ($q) => $q->where('is_gudang', true));
        }

        if ($jenisId = $request->string('jenis_id')->toString()) {
            $query->where('jenis_id', $jenisId);
        }

        if ($pendanaanId = $request->string('pendanaan_id')->toString()) {
            $query->where('pendanaan_id', $pendanaanId);
        }

        if ($kondisi = $request->string('kondisi')->toString()) {
            $query->where('kondisi', $kondisi);
        }

        return $query;
    }

    public function show(Inventaris $inventaris): JsonResponse
    {
        return response()->json($inventaris->load(['jenis', 'lokasi.instansi', 'pendanaan', 'peminjaman', 'perawatan']));
    }

    public function select(Request $request): JsonResponse
    {
        $query = Inventaris::query()->orderBy('nama');

        if ($lokasiId = $request->string('lokasi_id')->toString()) {
            $query->where('lokasi_id', $lokasiId);
        }

        return response()->json($query->get(['id', 'nama', 'kode_invt']));
    }

    public function nextKode(): JsonResponse
    {
        return response()->json(['kode_invt' => InventarisKode::next()]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InventarisExport($this->filtered($request)),
            'inventaris-'.now()->format('Y-m-d-His').'.xlsx',
        );
    }

    public function importTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InventarisTemplateExport,
            'template-import-inventaris.xlsx',
        );
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new \App\Imports\InventarisImport;

        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

        return response()->json([
            'imported' => $import->imported,
            'skipped' => $import->skipped,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['kode_invt'] = InventarisKode::next();

        $inventaris = Inventaris::create($data);

        return response()->json($inventaris->load(['jenis', 'lokasi.instansi', 'pendanaan']), 201);
    }

    public function update(Request $request, Inventaris $inventaris): JsonResponse
    {
        $data = $this->validated($request);

        $inventaris->update($data);

        return response()->json($inventaris->load(['jenis', 'lokasi.instansi', 'pendanaan']));
    }

    public function destroy(Inventaris $inventaris): JsonResponse
    {
        try {
            $inventaris->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Barang tidak bisa dihapus karena masih memiliki riwayat peminjaman atau perawatan.',
            ], 409);
        }

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:40'],
            'jenis_id' => ['required', 'uuid', 'exists:jenis,id'],
            'lokasi_id' => ['required', 'uuid', 'exists:lokasi,id'],
            'masuk' => ['required', 'date'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak'],
            'pendanaan_id' => ['required', 'uuid', 'exists:pendanaan,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_beli' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:70'],
        ]);
    }
}
