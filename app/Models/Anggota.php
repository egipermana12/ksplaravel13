<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama_anggota',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'nomor_hp',
        'tanggal_gabung',
        'status_anggota',
        'path_image',
    ];


    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_gabung' => 'date',
        ];
    }
}
