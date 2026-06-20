<?php

namespace App\Observers;

use App\Models\Pinjaman;
use App\Models\PinjamanJadwal;
use Carbon\Carbon;

class PinjamanObserver
{
    public function generateJadwal(Pinjaman $pinjaman): void
    {
        $tenor = (int) $pinjaman->tenor;
        $angsuranPokok = $this->splitByThousand((float) $pinjaman->jumlah_pinjaman, $tenor);
        $totalBunga = ((float) $pinjaman->bunga / 100) * (float) $pinjaman->jumlah_pinjaman * $tenor;
        $angsuranBunga = $this->splitByThousand($totalBunga, $tenor);

        $tanggalMulai = Carbon::parse($pinjaman->tanggal_disetujui ?? now());

        for ($i = 1; $i <= $tenor; $i++) {
            $pokokPerBulan = $angsuranPokok[$i - 1];
            $bungaPerBulan = $angsuranBunga[$i - 1];

            PinjamanJadwal::create([
                'id_pinjaman' => $pinjaman->id,
                'angsuran_ke' => $i,
                'tgl_jatuh_tempo' => $tanggalMulai->copy()->addMonths($i)->format('Y-m-d'),
                'angsuran_pokok' => $pokokPerBulan,
                'angsuran_bunga' => $bungaPerBulan,
                'total_tagihan' => $pokokPerBulan + $bungaPerBulan,
                'status_bayar' => 'belum_bayar',
            ]);
        }
    }

    /**
     * Membagi nominal ke beberapa periode dengan kelipatan ribuan.
     * Sisa pembulatan ditaruh di periode terakhir agar total tetap sama.
     */
    private function splitByThousand(float $amount, int $periods): array
    {
        $amount = (int) round($amount);
        $baseAmount = (int) floor(($amount / $periods) / 1000) * 1000;
        $installments = array_fill(0, $periods, $baseAmount);
        $installments[$periods - 1] = $amount - ($baseAmount * ($periods - 1));

        return $installments;
    }
}
