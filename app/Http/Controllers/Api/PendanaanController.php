<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendanaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendanaanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pendanaan::query()->orderBy('nama');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(): JsonResponse
    {
        return response()->json(Pendanaan::query()->orderBy('nama')->get(['id', 'nama']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:40', 'unique:pendanaan,nama'],
        ]);

        $pendanaan = Pendanaan::create($data);

        return response()->json($pendanaan, 201);
    }

    public function update(Request $request, Pendanaan $pendanaan): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:40', 'unique:pendanaan,nama,'.$pendanaan->id],
        ]);

        $pendanaan->update($data);

        return response()->json($pendanaan);
    }

    public function destroy(Pendanaan $pendanaan): JsonResponse
    {
        $pendanaan->delete();

        return response()->json(null, 204);
    }
}
