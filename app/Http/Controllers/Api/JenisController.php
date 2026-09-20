<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Jenis::query()->orderBy('nama');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(): JsonResponse
    {
        return response()->json(Jenis::query()->orderBy('nama')->get(['id', 'nama']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:40', 'unique:jenis,nama'],
        ]);

        $jenis = Jenis::create($data);

        return response()->json($jenis, 201);
    }

    public function update(Request $request, Jenis $jenis): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:40', 'unique:jenis,nama,'.$jenis->id],
        ]);

        $jenis->update($data);

        return response()->json($jenis);
    }

    public function destroy(Jenis $jenis): JsonResponse
    {
        $jenis->delete();

        return response()->json(null, 204);
    }
}
