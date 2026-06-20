<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\PendapatanKategori;
use App\Models\PendapatanTransaksi;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PendapatanTransaksiController extends Controller
{
    public function __construct(
        protected AccountingService $accounting,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $idKategori = $request->string('id_kategori')->toString();

        $transaksis = PendapatanTransaksi::query()
            ->with('kategori:id,nama_kategori')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($query) use ($search): void {
                        $query->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->when($idKategori !== '', function ($query) use ($idKategori): void {
                $query->where('id_kategori', $idKategori);
            })
            ->latest('tanggal_transaksi')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (PendapatanTransaksi $transaksi): array => [
                'id' => $transaksi->id,
                'id_kategori' => $transaksi->id_kategori,
                'nama_kategori' => $transaksi->kategori?->nama_kategori,
                'tanggal_transaksi' => $transaksi->tanggal_transaksi?->toDateString(),
                'nominal' => (float) $transaksi->nominal,
                'keterangan' => $transaksi->keterangan,
            ]);

        return Inertia::render('Pendapatan/Transaksi/Index', [
            'transaksis' => $transaksis,
            'kategoris' => PendapatanKategori::query()
                ->orderBy('nama_kategori')
                ->get(['id', 'nama_kategori']),
            'filters' => [
                'search' => $search,
                'id_kategori' => $idKategori,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pendapatan/Transaksi/Add', [
            'kategoris' => PendapatanKategori::query()
                ->orderBy('nama_kategori')
                ->get(['id', 'nama_kategori']),
            'akunKas' => Akun::where('kode_akun', '101')->firstOrFail(),
            'akunPendapatan' => Akun::where('kode_akun', '402')->firstOrFail(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_kategori' => ['required', 'exists:kategori_pendapatan,id'],
            'tanggal_transaksi' => ['required', 'date'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $transaksi = PendapatanTransaksi::create($validated);

                $this->accounting->createPendapatanTransaksiJurnal($transaksi);
            });

            return redirect()->route('pendapatan-transaksi.index')->with('toast', [
                'type' => 'success',
                'message' => 'Pendapatan transaksi berhasil disimpan dan jurnal telah dicatat.',
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan pendapatan transaksi: '.$e->getMessage(),
            ]);
        }
    }
}
