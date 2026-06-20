<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendapatanKategori extends Model
{
    protected $table = 'kategori_pendapatan';

    public $timestamps = false;

    protected $fillable = [
        'nama_kategori',
    ];

    public function transaksi(): HasMany
    {
        return $this->hasMany(PendapatanTransaksi::class, 'id_kategori');
    }
}
