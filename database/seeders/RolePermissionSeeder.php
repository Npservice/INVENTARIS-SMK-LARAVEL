<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(config('permissions'))
            ->flatMap(fn (array $actions, string $resource) => collect($actions)
                ->map(fn (string $action) => "{$resource}.{$action}"))
            ->values();

        $permissions->each(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // Staff: lihat saja, tidak termasuk data user.
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions($permissions->filter(fn (string $name) => str_ends_with($name, '.index')
            || str_ends_with($name, '.show')
            || str_ends_with($name, '.select'))
            ->reject(fn (string $name) => str_starts_with($name, 'user.')));

        // Guru/petugas: lihat + tambah + ubah, tidak boleh hapus, kelola user, atau kelola role.
        $guru = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $guru->syncPermissions($permissions->reject(fn (string $name) => str_starts_with($name, 'user.')
            || str_ends_with($name, '.destroy')
            || in_array($name, ['role.store', 'role.update'], true)));
    }
}
