<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Pinjaman;
use App\Observers\PinjamanObserver;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PinjamanController extends Controller
{
    public function __construct(
        protected AccountingService $accounting,
    ) {}

    public function index(Request $request): Response
    {
        $pinjamans = Pinjaman::query()
            ->with(['anggota:id,nik,nama_anggota'])
            ->select('id', 'id_anggota', 'no_kontrak', 'jumlah_pinjaman', 'tenor', 'bunga', 'status_pinjaman', 'tanggal_pengajuan', 'tanggal_disetujui', 'jenis_bunga', 'total_kewajiban')
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Pinjaman $pinjaman): array => [
                'id' => $pinjaman->id,
                'id_anggota' => $pinjaman->id_anggota,
                'nik' => $pinjaman->anggota?->nik,
                'nama_anggota' => $pinjaman->anggota?->nama_anggota,
                'jumlah_pinjaman' => (float) $pinjaman->jumlah_pinjaman,
                'tenor' => $pinjaman->tenor,
                'bunga' => (float) $pinjaman->bunga,
                'status_pinjaman' => $pinjaman->status_pinjaman,
                'tanggal_pengajuan' => $pinjaman->tanggal_pengajuan?->toDateString(),
                'total_kewajiban' => (float) $pinjaman->total_kewajiban,
                'tanggal_disetujui' => $pinjaman->tanggal_disetujui?->toDateString(),
                'jenis_bunga' => $pinjaman->jenis_bunga,
                'no_kontrak' => $pinjaman->no_kontrak,
            ]);

        return Inertia::render('Pinjaman/index', [
            'pinjamans' => $pinjamans,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pinjaman/Add', [
            'akunPiutang' => Akun::where('kode_akun', '103')->firstOrFail(),
            'akunKas' => Akun::where('kode_akun', '101')->firstOrFail(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        try {
            DB::transaction(function () use ($validated): void {
                $akunPiutangId = $validated['akunPiutang'];
                $akunKasId = $validated['akunKas'];
                $cleanData = collect($validated)
                    ->except(['akunPiutang', 'akunKas', 'nama_anggota', 'nik'])
                    ->toArray();

                $pinjaman = Pinjaman::create($cleanData);

                if ($pinjaman->status_pinjaman === 'approved') {
                    app(PinjamanObserver::class)->generateJadwal($pinjaman);

                    $this->accounting->createPinjamanJurnal(
                        $pinjaman,
                        $akunPiutangId,
                        $akunKasId
                    );
                }
            });

            return redirect()->route('pinjaman.index')->with('toast', [
                'type' => 'success',
                'message' => 'Data pinjaman berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal simpan: '.$e->getMessage(),
            ]);
        }
    }

    public function approve(Request $request, Pinjaman $pinjaman): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_disetujui' => [
                'nullable',
                'date',
                'after_or_equal:'.$pinjaman->tanggal_pengajuan->toDateString(),
            ],
        ]);

        try {
            DB::transaction(function () use ($pinjaman, $validated): void {
                $pinjaman = Pinjaman::query()
                    ->whereKey($pinjaman->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($pinjaman->status_pinjaman !== 'pending') {
                    throw new \RuntimeException('Hanya pinjaman dengan status pending yang bisa disetujui.');
                }

                $pinjaman->update([
                    'status_pinjaman' => 'approved',
                    'tanggal_disetujui' => $validated['tanggal_disetujui'] ?? now()->toDateString(),
                ]);

                $pinjaman = $pinjaman->fresh();

                app(PinjamanObserver::class)->generateJadwal($pinjaman);

                $akunPiutang = Akun::where('kode_akun', '103')->firstOrFail();
                $akunKas = Akun::where('kode_akun', '101')->firstOrFail();

                $this->accounting->createPinjamanJurnal(
                    $pinjaman,
                    $akunPiutang->id,
                    $akunKas->id
                );
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Pinjaman berhasil disetujui.',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal menyetujui pinjaman: '.$e->getMessage(),
            ]);
        }
    }

    public function validatedData(Request $request, ?Pinjaman $pinjaman = null): array
    {
        return $request->validate([
            'id_anggota' => ['required', 'exists:anggotas,id'],
            'nama_anggota' => ['required'],
            'nik' => ['required'],
            'akunPiutang' => ['required', 'exists:akuns,id'],
            'akunKas' => ['required', 'exists:akuns,id'],
            'jumlah_pinjaman' => ['required', 'numeric', 'min:1000'],
            'total_kewajiban' => ['required', 'numeric', 'min:1000'],
            'no_kontrak' => ['required', Rule::unique('pinjamans', 'no_kontrak')->ignore($pinjaman)],
            'tanggal_pengajuan' => ['required', 'date'],
            'jenis_bunga' => ['required', Rule::in(['flat', 'anuitas'])],
            'bunga' => ['required', 'numeric', 'min:0'],
            'tenor' => ['required', 'integer', 'min:1', Rule::in([1, 3, 6, 9, 12])],
            'status_pinjaman' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'tanggal_disetujui' => [
                Rule::requiredIf($request->status_pinjaman === 'approved'),
                'nullable',
                'date',
                'after_or_equal:tanggal_pengajuan',
            ],
        ]);
    }
}
