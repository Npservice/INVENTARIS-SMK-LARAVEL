<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderBy('name');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(): JsonResponse
    {
        return response()->json(User::query()->orderBy('name')->get(['id', 'name', 'username']));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load(['peminjaman', 'perawatan']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:15', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'status' => ['required', 'string', 'in:Guru,Kepsek,Pimpinan,Karyawan,Staff'],
            'role' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->syncRoleFromLevel();

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:15', 'unique:users,username,'.$user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'status' => ['required', 'string', 'in:Guru,Kepsek,Pimpinan,Karyawan,Staff'],
            'role' => ['required', 'integer', 'in:1,2,3'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoleFromLevel();

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'User tidak bisa dihapus karena masih memiliki riwayat peminjaman atau perawatan.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
