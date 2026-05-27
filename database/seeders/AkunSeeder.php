<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('akuns')->insert([
            // ===== 1. ASET (1xx) =====
            [
                'kode_akun' => '101',
                'nama_akun' => 'Kas',
                'jenis_akun' => 'aset',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '102',
                'nama_akun' => 'Bank',
                'jenis_akun' => 'aset',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '103',
                'nama_akun' => 'Piutang Pinjaman Anggota',
                'jenis_akun' => 'aset', // Akun utama untuk nominal Pokok Pinjaman
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '104',
                'nama_akun' => 'Piutang Bunga',
                'jenis_akun' => 'aset', // Untuk mencatat bunga yang harusnya diterima (opsional)
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '105',
                'nama_akun' => 'Perlengkapan Kantor',
                'jenis_akun' => 'aset',
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ===== 2. KEWAJIBAN (2xx) =====
            [
                'kode_akun' => '201',
                'nama_akun' => 'Simpanan Pokok',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '202',
                'nama_akun' => 'Simpanan Wajib',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '203',
                'nama_akun' => 'Simpanan Sukarela',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '204',
                'nama_akun' => 'Hutang Biaya',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ===== 3. EKUITAS / MODAL (3xx) =====
            [
                'kode_akun' => '301',
                'nama_akun' => 'Modal Koperasi',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '302',
                'nama_akun' => 'Sisa Hasil Usaha (SHU)',
                'jenis_akun' => 'kewajiban',
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ===== 4. PENDAPATAN (4xx) =====
            [
                'kode_akun' => '401',
                'nama_akun' => 'Pendapatan Bunga Pinjaman',
                'jenis_akun' => 'pendapatan', // Dari cicilan bunga bulanan
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '402',
                'nama_akun' => 'Pendapatan Provisi/Administrasi',
                'jenis_akun' => 'pendapatan', // Biaya admin saat pinjaman cair
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '403',
                'nama_akun' => 'Pendapatan Denda',
                'jenis_akun' => 'pendapatan', // Dari keterlambatan bayar
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ===== 5. BEBAN (5xx) =====
            [
                'kode_akun' => '501',
                'nama_akun' => 'Beban Operasional',
                'jenis_akun' => 'beban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '502',
                'nama_akun' => 'Beban Gaji Karyawan',
                'jenis_akun' => 'beban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '503',
                'nama_akun' => 'Beban Listrik, Air & Internet',
                'jenis_akun' => 'beban',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'kode_akun' => '504',
                'nama_akun' => 'Beban Pajak',
                'jenis_akun' => 'beban',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
