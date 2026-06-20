<?php

namespace App\Models;

use App\Observers\PinjamanObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    protected $table = 'pinjamans';

    protected $fillable = [
        'id_anggota',
        'no_kontrak',
        'jumlah_pinjaman',
        'tenor',
        'bunga',
        'status_pinjaman',
        'tanggal_pengajuan',
        'tanggal_disetujui',
        'jenis_bunga',
        'total_kewajiban',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_disetujui' => 'date',
        ];
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    // Daftarkan Observer di sini
    protected static function booted(): void
    {
        static::observe(PinjamanObserver::class);
    }

    // Relationship ke tabel jadwal
    public function jadwal(): HasMany
    {
        return $this->hasMany(PinjamanJadwal::class, 'id_pinjaman');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranPinjaman::class, 'id_pinjaman');
    }
}
