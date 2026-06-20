<?php

namespace App\Http\Controllers;

use App\Models\PendapatanKategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PendapatanKategoriController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $kategoris = PendapatanKategori::query()
            ->withCount('transaksi')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (PendapatanKategori $kategori): array => [
                'id' => $kategori->id,
                'nama_kategori' => $kategori->nama_kategori,
                'transaksi_count' => $kategori->transaksi_count,
            ]);

        return Inertia::render('Pendapatan/Kategori/Index', [
            'kategoris' => $kategoris,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:kategori_pendapatan,nama_kategori'],
        ]);

        PendapatanKategori::create($validated);

        return redirect()->route('pendapatan-kategori.index')->with('toast', [
            'type' => 'success',
            'message' => 'Kategori pendapatan berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, PendapatanKategori $pendapatanKategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_pendapatan', 'nama_kategori')->ignore($pendapatanKategori->id),
            ],
        ]);

        $pendapatanKategori->update($validated);

        return redirect()->route('pendapatan-kategori.index')->with('toast', [
            'type' => 'success',
            'message' => 'Kategori pendapatan berhasil diperbarui.',
        ]);
    }

    public function destroy(PendapatanKategori $pendapatanKategori): RedirectResponse
    {
        if ($pendapatanKategori->transaksi()->exists()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Kategori tidak bisa dihapus karena sudah digunakan transaksi.',
            ]);
        }

        $pendapatanKategori->delete();

        return redirect()->route('pendapatan-kategori.index')->with('toast', [
            'type' => 'success',
            'message' => 'Kategori pendapatan berhasil dihapus.',
        ]);
    }
}
