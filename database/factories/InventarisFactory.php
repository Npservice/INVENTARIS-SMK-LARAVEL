<?php

namespace Database\Factories;

use App\Models\Inventaris;
use App\Models\Jenis;
use App\Models\Lokasi;
use App\Models\Pendanaan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Inventaris>
 */
class InventarisFactory extends Factory
{
    protected $model = Inventaris::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(3, true),
            'jenis_id' => Jenis::factory(),
            'kode_invt' => 'INV/SMKUN/'.fake()->unique()->numerify('####'),
            'lokasi_id' => Lokasi::factory(),
            'masuk' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'kondisi' => fake()->randomElement(['Baik', 'Rusak']),
            'pendanaan_id' => Pendanaan::factory(),
            'jumlah' => fake()->numberBetween(1, 50),
            'harga_beli' => fake()->randomFloat(2, 50000, 15000000),
            'keterangan' => fake()->optional()->passthrough(Str::substr(fake()->sentence(6), 0, 70)),
        ];
    }
}
