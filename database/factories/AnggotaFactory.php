<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anggota>
 */
class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'nama_anggota' => $this->faker->name(),
            'tanggal_lahir' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'alamat' => $this->faker->streetAddress(),
            'nomor_hp' => $this->faker->unique()->numerify('08###########'),
            'tanggal_gabung' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status_anggota' => $this->faker->randomElement(['aktif', 'aktif', 'aktif', 'nonaktif']),
            'path_image' => null,
        ];
    }
}
