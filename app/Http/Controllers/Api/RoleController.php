<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::query()->with('permissions')->withCount('users')->orderBy('name');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->simplePaginate($request->integer('size', 15)));
    }

    public function select(): JsonResponse
    {
        return response()->json(Role::query()->orderBy('name')->get(['id', 'name']));
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json($role->load('permissions'), 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $this->validated($request, $role);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            $role->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Role tidak bisa dihapus karena masih dipakai oleh user.',
            ], 409);
        }

        return response()->json(null, 204);
    }

    public function permissions(): JsonResponse
    {
        return response()->json(Permission::query()->orderBy('name')->get(['id', 'name']));
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,'.($role?->id).',id,guard_name,web'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }
}
