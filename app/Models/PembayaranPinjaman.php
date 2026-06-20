<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranPinjaman extends Model
{
    protected $table = 'pembayaran_pinjaman';

    protected $fillable = [
        'id_pinjaman',
        'id_jadwal',
        'tanggal_bayar',
        'nominal_bayar',
        'denda',
        'bukti_bayar',
        'metode_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'date',
            'nominal_bayar' => 'decimal:2',
            'denda' => 'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'id_pinjaman');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(PinjamanJadwal::class, 'id_jadwal');
    }
}
