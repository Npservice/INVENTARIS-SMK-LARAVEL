<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Instansi::query()->orderBy('nama');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(): JsonResponse
    {
        return response()->json(Instansi::query()->orderBy('nama')->get(['id', 'nama']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:20', 'unique:instansi,nama'],
        ]);

        $instansi = Instansi::create($data);

        return response()->json($instansi, 201);
    }

    public function update(Request $request, Instansi $instansi): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:20', 'unique:instansi,nama,'.$instansi->id],
        ]);

        $instansi->update($data);

        return response()->json($instansi);
    }

    public function destroy(Instansi $instansi): JsonResponse
    {
        $instansi->delete();

        return response()->json(null, 204);
    }
}
