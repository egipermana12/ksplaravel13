<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tgl_jatuh_tempo' => 'date',
            'angsuran_pokok' => 'decimal:2',
            'angsuran_bunga' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'id_pinjaman');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranPinjaman::class, 'id_jadwal');
    }
}
