<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PinjamanJadwal;
use Illuminate\Http\Request;

class JadwalPinjamanApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $sort = $request->string('sort', 'tgl_jatuh_tempo')->toString();
        $direction = $request->string('direction', 'asc')->toString();
        $allowedSorts = ['id', 'angsuran_ke', 'tgl_jatuh_tempo', 'total_tagihan'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'tgl_jatuh_tempo';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return PinjamanJadwal::query()
            ->with(['pinjaman.anggota:id,nik,nama_anggota'])
            ->where('status_bayar', 'belum_bayar')
            ->whereHas('pinjaman', function ($query): void {
                $query->whereIn('status_pinjaman', ['approved', 'ongoing']);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('angsuran_ke', 'like', "%{$search}%")
                        ->orWhere('tgl_jatuh_tempo', 'like', "%{$search}%")
                        ->orWhereHas('pinjaman', function ($query) use ($search): void {
                            $query->where('no_kontrak', 'like', "%{$search}%")
                                ->orWhereHas('anggota', function ($query) use ($search): void {
                                    $query->where('nik', 'like', "%{$search}%")
                                        ->orWhere('nama_anggota', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10))
            ->through(fn (PinjamanJadwal $jadwal): array => [
                'id' => $jadwal->id,
                'id_pinjaman' => $jadwal->id_pinjaman,
                'no_kontrak' => $jadwal->pinjaman?->no_kontrak,
                'angsuran_ke' => $jadwal->angsuran_ke,
                'tgl_jatuh_tempo' => $jadwal->tgl_jatuh_tempo?->toDateString(),
                'angsuran_pokok' => (float) $jadwal->angsuran_pokok,
                'angsuran_bunga' => (float) $jadwal->angsuran_bunga,
                'total_tagihan' => (float) $jadwal->total_tagihan,
                'total_tagihan_label' => 'Rp '.number_format((float) $jadwal->total_tagihan, 0, ',', '.'),
                'nik' => $jadwal->pinjaman?->anggota?->nik,
                'nama_anggota' => $jadwal->pinjaman?->anggota?->nama_anggota,
            ]);
    }
}
