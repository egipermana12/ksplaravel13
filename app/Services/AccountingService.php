<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function createSimpanan(
        $simpanan,
        int $akunPiutangId,
        int $akunKasId
    ) {
        // 2. Siapkan data detail jurnal
        $details = [
            [
                'id_akun' => $akunKasId,
                'debit' => $simpanan->nominal,
                'kredit' => 0,
            ],
            [
                'id_akun' => $akunPiutangId,
                'debit' => 0,
                'kredit' => $simpanan->nominal,
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
                'debit' => $pinjaman->jumlah_pinjaman,
                'kredit' => 0,
            ],
            [
                'id_akun' => $akunKasId,
                'debit' => 0,
                'kredit' => $pinjaman->jumlah_pinjaman,
            ],
        ];

        return $this->createJurnal(
            $pinjaman->tanggal_disetujui ?? now(),
            'pinjaman',
            $pinjaman->id,
            $details
        );
    }

    public function createPembayaranPinjamanJurnal(
        $pembayaran,
        int $akunKasId,
        int $akunPiutangPinjamanId,
        int $akunPiutangBungaId
    ) {
        $jadwal = $pembayaran->jadwal;

        $details = [
            [
                'id_akun' => $akunKasId,
                'debit' => $pembayaran->nominal_bayar,
                'kredit' => 0,
            ],
            [
                'id_akun' => $akunPiutangPinjamanId,
                'debit' => 0,
                'kredit' => $jadwal->angsuran_pokok,
            ],
            [
                'id_akun' => $akunPiutangBungaId,
                'debit' => 0,
                'kredit' => $jadwal->angsuran_bunga,
            ],
        ];

        if ((float) $pembayaran->denda > 0) {
            $akunPendapatanDenda = Akun::where('kode_akun', '403')->firstOrFail();

            $details[] = [
                'id_akun' => $akunPendapatanDenda->id,
                'debit' => 0,
                'kredit' => $pembayaran->denda,
            ];
        }

        return $this->createJurnal(
            $pembayaran->tanggal_bayar,
            'pembayaran_pinjaman',
            $pembayaran->id,
            $details
        );
    }

    public function createPendapatanTransaksiJurnal($pendapatanTransaksi)
    {
        $akunKas = Akun::where('kode_akun', '101')->firstOrFail();
        $akunPendapatan = Akun::where('kode_akun', '402')->firstOrFail();

        $details = [
            [
                'id_akun' => $akunKas->id,
                'debit' => $pendapatanTransaksi->nominal,
                'kredit' => 0,
            ],
            [
                'id_akun' => $akunPendapatan->id,
                'debit' => 0,
                'kredit' => $pendapatanTransaksi->nominal,
            ],
        ];

        return $this->createJurnal(
            $pendapatanTransaksi->tanggal_transaksi,
            'pendapatan_transaksi',
            $pendapatanTransaksi->id,
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
                'tanggal' => $tanggal,
                'ref_type_transaksi' => $refType,
                'refid_transaksi' => $refId,
            ]);

            // Simpan Detail Jurnal (Mass Insert untuk efisiensi)
            $jurnalDetails = collect($details)->map(function ($detail) use ($jurnal) {
                return [
                    'id_jurnal' => $jurnal->id,
                    'id_akun' => $detail['id_akun'],
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            $jurnal->detailJurnals()->insert($jurnalDetails);

            return $jurnal;
        });
    }
}
