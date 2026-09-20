<?php

namespace Database\Factories;

use App\Models\Instansi;
use App\Models\Lokasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lokasi>
 */
class LokasiFactory extends Factory
{
    protected $model = Lokasi::class;

    public function definition(): array
    {
        return [
            'instansi_id' => Instansi::factory(),
            'nama' => fake()->randomElement([
                'Kantor', 'Kelas', 'Lab TKJ', 'Lab MM', 'Lab KPR',
                'Lab TBG', 'Lab TBS', 'Lab TBSM', 'Lab KCT', 'Perpustakaan', 'Gudang',
            ]),
            'is_gudang' => false,
        ];
    }

    public function gudang(): static
    {
        return $this->state(fn () => [
            'nama' => 'Gudang',
            'is_gudang' => true,
        ]);
    }
}
