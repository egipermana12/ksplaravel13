<?php

namespace App\Models;

use App\Models\JurnalDetail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasMany;

class Jurnal extends Model
{
    protected $fillable = [
        'tanggal',
        'refid_transaksi',
        'ref_type_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date'
        ];
    }

    //relasi ke detail jurnal
    public function detailJurnals(): hasMany
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal');
    }
}
