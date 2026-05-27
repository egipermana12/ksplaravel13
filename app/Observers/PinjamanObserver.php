<?php

namespace App\Observers;

use App\Models\Pinjaman;
use App\Models\PinjamanJadwal;
use Carbon\Carbon;

class PinjamanObserver
{

    public function generateJadwal(Pinjaman $pinjaman)
    {
        $tenor = $pinjaman->tenor;
        $pokokPerBulan = $pinjaman->jumlah_pinjaman / $tenor;
        $bungaPerBulan = ($pinjaman->bunga / 100) * $pinjaman->jumlah_pinjaman;

        $tanggalMulai = Carbon::parse($pinjaman->tanggal_disetujui ?? now());

        for ($i = 1; $i <= $tenor; $i++) {
            PinjamanJadwal::create([
                'id_pinjaman'     => $pinjaman->id,
                'angsuran_ke'     => $i,
                'tgl_jatuh_tempo' => $tanggalMulai->copy()->addMonths($i)->format('Y-m-d'),
                'angsuran_pokok'  => $pokokPerBulan,
                'angsuran_bunga'  => $bungaPerBulan,
                'total_tagihan'   => $pokokPerBulan + $bungaPerBulan,
                'status_bayar'    => 'belum_bayar',
            ]);
        }
    }
}
