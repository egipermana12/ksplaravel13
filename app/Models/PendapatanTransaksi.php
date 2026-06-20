<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendapatanTransaksi extends Model
{
    protected $table = 'pendapatan_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'tanggal_transaksi',
        'nominal',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PendapatanKategori::class, 'id_kategori');
    }
}
