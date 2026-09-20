<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Perawatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerawatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Perawatan::with(['inventaris.lokasi.instansi', 'user'])
            ->orderByDesc('tanggal_perawatan');

        if ($inventarisId = $request->string('inventaris_id')->toString()) {
            $query->where('inventaris_id', $inventarisId);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('inventaris', fn ($i) => $i->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_invt', 'like', "%{$search}%"));
            });
        }

        if ($userId = $request->string('user_id')->toString()) {
            $query->where('user_id', $userId);
        }

        if ($status = $request->string('status_perawatan')->toString()) {
            $query->where('status_perawatan', $status);
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function show(Perawatan $perawatan): JsonResponse
    {
        return response()->json($perawatan->load(['inventaris.lokasi.instansi', 'user']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $perawatan = Perawatan::create($data);

        return response()->json($perawatan->load(['inventaris.lokasi.instansi', 'user']), 201);
    }

    public function update(Request $request, Perawatan $perawatan): JsonResponse
    {
        $data = $this->validated($request);

        $perawatan->update($data);

        return response()->json($perawatan->load(['inventaris.lokasi.instansi', 'user']));
    }

    public function destroy(Perawatan $perawatan): JsonResponse
    {
        $perawatan->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'inventaris_id' => ['required', 'uuid', 'exists:inventaris,id'],
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'tanggal_perawatan' => ['required', 'date'],
            'status_perawatan' => ['required', 'string', 'in:Proses,Selesai'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:70'],
        ]);
    }
}
