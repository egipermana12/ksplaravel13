<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AnggotaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $anggotas = Anggota::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nik', 'like', "%{$search}%")
                        ->orWhere('nama_anggota', 'like', "%{$search}%")
                        ->orWhere('nomor_hp', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), function ($query) use ($status): void {
                $query->where('status_anggota', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Anggota $anggota): array => [
                'id' => $anggota->id,
                'nik' => $anggota->nik,
                'nama_anggota' => $anggota->nama_anggota,
                'tanggal_lahir' => $anggota->tanggal_lahir?->toDateString(),
                'jenis_kelamin' => $anggota->jenis_kelamin,
                'alamat' => $anggota->alamat,
                'nomor_hp' => $anggota->nomor_hp,
                'tanggal_gabung' => $anggota->tanggal_gabung?->toDateString(),
                'status_anggota' => $anggota->status_anggota,
                'path_image' => $anggota->path_image,
                'image_url' => $anggota->path_image ? Storage::url($anggota->path_image) : null,
            ]);

        return Inertia::render('Anggota/Index', [
            'anggotas' => $anggotas,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('path_image')) {
            $data['path_image'] = $this->storeImage($request);
        }

        Anggota::create($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Anggota berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, Anggota $anggota): RedirectResponse
    {
        $data = $this->validatedData($request, $anggota);

        if ($request->hasFile('path_image')) {
            if ($anggota->path_image) {
                Storage::disk('public')->delete($anggota->path_image);
            }

            $data['path_image'] = $this->storeImage($request);
        } else {
            unset($data['path_image']);
        }

        $anggota->update($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Data anggota berhasil diperbarui.',
        ]);
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        if ($anggota->path_image) {
            Storage::disk('public')->delete($anggota->path_image);
        }

        $anggota->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Anggota berhasil dihapus.',
        ]);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'exists:anggotas,id'],
        ]);

        $anggotas = Anggota::query()
            ->whereIn('id', $data['ids'])
            ->get();

        $paths = $anggotas
            ->pluck('path_image')
            ->filter()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        $deleted = $anggotas->count();

        Anggota::query()
            ->whereIn('id', $anggotas->pluck('id'))
            ->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$deleted} anggota berhasil dihapus.",
        ]);
    }

    private function validatedData(Request $request, ?Anggota $anggota = null): array
    {
        return $request->validate([
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas', 'nik')->ignore($anggota)],
            'nama_anggota' => ['required', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string', 'max:100'],
            'nomor_hp' => ['nullable', 'string', 'max:13', 'regex:/^[0-9+\-\s]+$/'],
            'tanggal_gabung' => ['required', 'date'],
            'status_anggota' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'path_image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('path_image');

        if (! $file || ! $file->isValid() || ! is_readable($file->getPathname())) {
            throw ValidationException::withMessages([
                'path_image' => 'Foto gagal diupload. Coba pilih file gambar lain.',
            ]);
        }

        $path = 'anggota/' . Str::uuid()->toString() . '.' . $file->extension();
        $stream = fopen($file->getPathname(), 'r');

        if (! $stream) {
            throw ValidationException::withMessages([
                'path_image' => 'Foto gagal dibaca dari temporary upload.',
            ]);
        }

        try {
            Storage::disk('public')->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }
}
