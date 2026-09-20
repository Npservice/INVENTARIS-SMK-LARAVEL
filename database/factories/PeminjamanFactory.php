<?php

namespace Database\Factories;

use App\Models\Inventaris;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $tanggalPinjam = fake()->dateTimeBetween('-6 months', 'now');
        $tanggalKembali = (clone $tanggalPinjam)->modify('+'.fake()->numberBetween(1, 14).' days');

        return [
            'id_user' => User::factory(),
            'id_inventaris' => Inventaris::factory(),
            'nama_pjm' => Str::substr(fake()->name(), 0, 20),
            'status_pjm' => fake()->randomElement(['Guru', 'Siswa', 'Staff']),
            'waktu_pinjam' => fake()->time('H:i:s'),
            'waktu_kembali' => fake()->time('H:i:s'),
            'tanggal_pinjam' => $tanggalPinjam->format('Y-m-d'),
            'tanggal_kembali' => $tanggalKembali->format('Y-m-d'),
            'status_pinjam' => fake()->randomElement(['Dipinjam', 'Kembali']),
            'keterangan_pjm' => fake()->optional()->passthrough(Str::substr(fake()->sentence(6), 0, 70)),
        ];
    }
}
