<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnggotaApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status', 'aktif')->toString();
        $sort = $request->string('sort', 'nama_anggota')->toString();
        $direction = $request->string('direction', 'asc')->toString();

        $allowedSorts = ['id', 'nik', 'nama_anggota', 'nomor_hp', 'alamat', 'status_anggota'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'nama_anggota';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return Anggota::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nik', 'like', "%{$search}%")
                        ->orWhere('nama_anggota', 'like', "%{$search}%")
                        ->orWhere('nomor_hp', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), function ($query) use ($status): void {
                $query->where('status_anggota', $status);
            })
            ->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 10))
            ->through(fn(Anggota $anggota): array => [
                'id' => $anggota->id,
                'nik' => $anggota->nik,
                'nama_anggota' => $anggota->nama_anggota,
                'nomor_hp' => $anggota->nomor_hp,
                'status_anggota' => $anggota->status_anggota,
                'alamat' => $anggota->alamat,
                'image_url' => $anggota->path_image ? Storage::url($anggota->path_image) : null,
            ]);
    }
}
