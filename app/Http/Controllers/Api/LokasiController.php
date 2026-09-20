<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lokasi::with('instansi')->orderBy('nama');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(Request $request): JsonResponse
    {
        $query = Lokasi::query()->orderBy('nama');

        if ($instansiId = $request->string('instansi_id')->toString()) {
            $query->where('instansi_id', $instansiId);
        }

        return response()->json($query->get(['id', 'nama', 'instansi_id', 'is_gudang']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instansi_id' => ['required', 'uuid', 'exists:instansi,id'],
            'nama' => ['required', 'string', 'max:20'],
            'is_gudang' => ['sometimes', 'boolean'],
        ]);

        $lokasi = Lokasi::create($data);

        return response()->json($lokasi->load('instansi'), 201);
    }

    public function update(Request $request, Lokasi $lokasi): JsonResponse
    {
        $data = $request->validate([
            'instansi_id' => ['required', 'uuid', 'exists:instansi,id'],
            'nama' => ['required', 'string', 'max:20'],
            'is_gudang' => ['sometimes', 'boolean'],
        ]);

        $lokasi->update($data);

        return response()->json($lokasi->load('instansi'));
    }

    public function destroy(Lokasi $lokasi): JsonResponse
    {
        $lokasi->delete();

        return response()->json(null, 204);
    }
}
