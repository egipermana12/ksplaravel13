<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class JurnalDetail extends Model
{
    protected $guarded = [];

    // Nama relasi sebaiknya 'jurnal', bukan 'id_jurnal'
    public function jurnal(): BelongsTo
    {
        // Parameter kedua adalah foreign key di tabel jurnal_details
        // Parameter ketiga adalah primary key di tabel jurnals
        return $this->belongsTo(Jurnal::class, 'id_jurnal', 'id');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }
}
