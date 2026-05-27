<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Simpanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_anggota',
        'jenis_simpanan',
        'nominal',
        'tanggal_setor',
        'bukti_setor',
        'ket',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    protected function casts(): array
    {
        return [
            'tanggal_setor' => 'date'
        ];
    }
}
