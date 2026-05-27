<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinjamanJadwal extends Model
{
    protected $table = 'pinjaman_jadwal';

    protected $fillable = [
        'id_pinjaman',
        'angsuran_ke',
        'tgl_jatuh_tempo',
        'angsuran_pokok',
        'angsuran_bunga',
        'total_tagihan',
        'status_bayar'
    ];
}
