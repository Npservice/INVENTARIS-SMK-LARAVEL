<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KritikSaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KritikSaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KritikSaran::query()->orderByDesc('created_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kritik_saran', 'like', "%{$search}%");
            });
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function show(KritikSaran $kritikSaran): JsonResponse
    {
        return response()->json($kritikSaran);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kritik_saran' => ['required', 'string'],
        ]);

        $kritikSaran = KritikSaran::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $kritikSaran,
        ], 201);
    }

    public function destroy(KritikSaran $kritikSaran): JsonResponse
    {
        $kritikSaran->delete();

        return response()->json(null, 204);
    }
}
