<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Akun;

class AccountingService
{
    public function createSimpanan($simpanan)
    {
        $kodeKas = '101';
        $kodeSimpanan = '201';

        $akunKas = Akun::where('kode_akun', $kodeKas)->firstOrFail();
        $akunSimpanan = Akun::where('kode_akun', $kodeSimpanan)->firstOrFail();

        // 2. Siapkan data detail jurnal
        $details = [
            [
                'id_akun' => $akunKas->id,
                'debit'   => $simpanan->nominal,
                'kredit'  => 0,
            ],
            [
                'id_akun' => $akunSimpanan->id,
                'debit'   => 0,
                'kredit'  => $simpanan->nominal,
            ],
        ];

        // 3. Eksekusi pembuatan jurnal menggunakan helper internal
        return $this->createJurnal(
            $simpanan->tanggal_setor,
            'simpanan',
            $simpanan->id,
            $details
        );
    }

    public function createPinjamanJurnal(
        $pinjaman,
        int $akunPiutangId,
        int $akunKasId
    ) {
        // Pastikan akun sudah ada
        $details = [
            [
                'id_akun' => $akunPiutangId,
                'debit'   => $pinjaman->jumlah_pinjaman,
                'kredit'  => 0,
            ],
            [
                'id_akun' => $akunKasId,
                'debit'   => 0,
                'kredit'  => $pinjaman->jumlah_pinjaman,
            ],
        ];

        return $this->createJurnal(
            $pinjaman->tanggal_disetujui ?? now(),
            'pinjaman',
            $pinjaman->id,
            $details
        );
    }

    /**
     * Fungsi generik untuk membuat jurnal (Double Entry)
     */

    public function createJurnal($tanggal, $refType, $refId, array $details)
    {
        return DB::transaction(function () use ($tanggal, $refType, $refId, $details) {
            // Validasi Balance
            $totalDebit = collect($details)->sum('debit');
            $totalKredit = collect($details)->sum('kredit');

            if ($totalDebit != $totalKredit) {
                throw new \Exception("Jurnal tidak balance. Debit: $totalDebit, Kredit: $totalKredit");
            }

            // Simpan Header Jurnal
            $jurnal = Jurnal::create([
                'tanggal'    => $tanggal,
                'ref_type_transaksi'   => $refType,
                'refid_transaksi'     => $refId,
            ]);

            // Simpan Detail Jurnal (Mass Insert untuk efisiensi)
            $jurnalDetails = collect($details)->map(function ($detail) use ($jurnal) {
                return [
                    'id_jurnal'  => $jurnal->id,
                    'id_akun'    => $detail['id_akun'],
                    'debit'      => $detail['debit'],
                    'kredit'     => $detail['kredit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            $jurnal->detailJurnals()->insert($jurnalDetails);

            return $jurnal;
        });
    }
}
