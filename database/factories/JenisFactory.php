<?php

namespace Database\Factories;

use App\Models\Jenis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jenis>
 */
class JenisFactory extends Factory
{
    protected $model = Jenis::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement([
                'Elektronik', 'Furniture', 'Alat Tulis Kantor', 'Alat Praktik',
                'Kendaraan', 'Alat Olahraga', 'Buku', 'Alat Kebersihan',
            ]),
        ];
    }
}
