<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Jenis;
use App\Models\KritikSaran;
use App\Models\Lokasi;
use App\Models\Peminjaman;
use App\Models\Pendanaan;
use App\Models\Perawatan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Users (role: 1 = Admin, 2 = Staff, 3 = Guru/Petugas)
        $admin = User::factory()->create([
            'name' => 'Admin Sekolah',
            'username' => 'admin',
            'role' => 1,
        ]);
        $admin->assignRole('admin');

        $staff = User::factory()->create([
            'name' => 'Staff Sarpras',
            'username' => 'staff',
            'role' => 2,
        ]);
        $staff->assignRole('staff');

        $guru = User::factory()->create([
            'name' => 'Guru Piket',
            'username' => 'guru',
            'role' => 3,
        ]);
        $guru->assignRole('guru');

        $users = User::factory(5)->create();

        // Master data
        $instansiPutra = Instansi::factory()->create(['nama' => 'Putra']);
        $instansiPutri = Instansi::factory()->create(['nama' => 'Putri']);

        $lokasiNama = [
            'Kantor', 'Kelas', 'Lab TKJ', 'Lab MM', 'Lab KPR',
            'Lab TBG', 'Lab TBS', 'Lab TBSM', 'Lab KCT', 'Perpustakaan',
        ];

        $lokasi = collect();
        foreach ([$instansiPutra, $instansiPutri] as $instansi) {
            foreach ($lokasiNama as $nama) {
                $lokasi->push(Lokasi::factory()->create([
                    'instansi_id' => $instansi->id,
                    'nama' => $nama,
                    'is_gudang' => false,
                ]));
            }
            $lokasi->push(Lokasi::factory()->create([
                'instansi_id' => $instansi->id,
                'nama' => 'Gudang',
                'is_gudang' => true,
            ]));
        }

        $jenis = Jenis::factory(8)->create();
        $pendanaan = Pendanaan::factory(4)->create();

        // Inventaris
        $inventaris = Inventaris::factory(40)->create([
            'jenis_id' => fn () => $jenis->random()->id,
            'lokasi_id' => fn () => $lokasi->random()->id,
            'pendanaan_id' => fn () => $pendanaan->random()->id,
        ]);

        $allUsers = $users->push($admin, $staff);

        // Peminjaman
        Peminjaman::factory(20)->create([
            'id_user' => fn () => $allUsers->random()->id,
            'id_inventaris' => fn () => $inventaris->random()->id,
        ]);

        // Perawatan
        Perawatan::factory(15)->create([
            'inventaris_id' => fn () => $inventaris->random()->id,
            'user_id' => fn () => $allUsers->random()->id,
        ]);

        // Usulan & saran
        KritikSaran::factory(10)->create();
    }
}
