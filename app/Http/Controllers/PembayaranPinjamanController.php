<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\PembayaranPinjaman;
use App\Models\PinjamanJadwal;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PembayaranPinjamanController extends Controller
{

    protected $kd_akun_piutang_pinjaman = '103';
    protected $kd_akun_piutang_bunga = '104';
    protected $kd_akun_kas = '101';

    public function __construct(
        protected AccountingService $accounting,
    ) {}

    public function index(): Response
    {
        $pembayarans = PembayaranPinjaman::query()
            ->with([
                'pinjaman.anggota:id,nik,nama_anggota',
                'jadwal:id,angsuran_ke,angsuran_pokok,angsuran_bunga,total_tagihan,status_bayar',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn(PembayaranPinjaman $pembayaran): array => [
                'id' => $pembayaran->id,
                'tanggal_bayar' => $pembayaran->tanggal_bayar?->toDateString(),
                'no_kontrak' => $pembayaran->pinjaman?->no_kontrak,
                'nik' => $pembayaran->pinjaman?->anggota?->nik,
                'nama_anggota' => $pembayaran->pinjaman?->anggota?->nama_anggota,
                'angsuran_ke' => $pembayaran->jadwal?->angsuran_ke,
                'angsuran_pokok' => (float) ($pembayaran->jadwal?->angsuran_pokok ?? 0),
                'angsuran_bunga' => (float) ($pembayaran->jadwal?->angsuran_bunga ?? 0),
                'denda' => (float) $pembayaran->denda,
                'nominal_bayar' => (float) $pembayaran->nominal_bayar,
                'metode_bayar' => $pembayaran->metode_bayar,
                'bukti_bayar' => $pembayaran->bukti_bayar,
            ]);

        return Inertia::render('PembayaranPinjaman/Index', [
            'pembayarans' => $pembayarans,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PembayaranPinjaman/Add', [
            'akunKas' => Akun::where('kode_akun', $this->kd_akun_kas)->firstOrFail(),
            'akunPiutangPinjaman' => Akun::where('kode_akun', $this->kd_akun_piutang_pinjaman)->firstOrFail(),
            'akunPiutangBunga' => Akun::where('kode_akun', $this->kd_akun_piutang_bunga)->firstOrFail(),
        ]);
    }

    public function jadwal(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $jadwals = PinjamanJadwal::query()
            ->with(['pinjaman.anggota:id,nik,nama_anggota'])
            ->when($status !== '', function ($query) use ($status): void {
                $query->where('status_bayar', $status);
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
            ->orderBy('tgl_jatuh_tempo')
            ->orderBy('angsuran_ke')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(PinjamanJadwal $jadwal): array => [
                'id' => $jadwal->id,
                'no_kontrak' => $jadwal->pinjaman?->no_kontrak,
                'nik' => $jadwal->pinjaman?->anggota?->nik,
                'nama_anggota' => $jadwal->pinjaman?->anggota?->nama_anggota,
                'angsuran_ke' => $jadwal->angsuran_ke,
                'tgl_jatuh_tempo' => $jadwal->tgl_jatuh_tempo?->toDateString(),
                'angsuran_pokok' => (float) $jadwal->angsuran_pokok,
                'angsuran_bunga' => (float) $jadwal->angsuran_bunga,
                'total_tagihan' => (float) $jadwal->total_tagihan,
                'status_bayar' => $jadwal->status_bayar,
            ]);

        return Inertia::render('PembayaranPinjaman/Jadwal', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        try {
            DB::transaction(function () use ($validated): void {

                //AMBIL KODE AKUN
                $akunKasId = $validated['kd_akun_kas'];
                $akunPiutangPinjamanId = $validated['kd_akun_piutang_pinjaman'];
                $akunPiutangBungaId = $validated['kd_akun_piutang_bunga'];

                $jadwal = PinjamanJadwal::query()
                    ->with('pinjaman')
                    ->whereKey($validated['id_jadwal'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($jadwal->status_bayar !== 'belum_bayar') {
                    throw new \RuntimeException('Jadwal cicilan ini sudah dibayar.');
                }

                $denda = (float) ($validated['denda'] ?? 0);
                $nominalBayar = (float) $jadwal->total_tagihan + $denda;

                $pembayaran = PembayaranPinjaman::create([
                    'id_pinjaman' => $jadwal->id_pinjaman,
                    'id_jadwal' => $jadwal->id,
                    'tanggal_bayar' => $validated['tanggal_bayar'],
                    'nominal_bayar' => $nominalBayar,
                    'denda' => $denda,
                    'bukti_bayar' => $validated['bukti_bayar'] ?? null,
                    'metode_bayar' => $validated['metode_bayar'],
                ]);

                $jadwal->update([
                    'status_bayar' => 'lunas',
                ]);

                $remainingUnpaid = PinjamanJadwal::query()
                    ->where('id_pinjaman', $jadwal->id_pinjaman)
                    ->where('status_bayar', '!=', 'lunas')
                    ->exists();

                $jadwal->pinjaman->update([
                    'status_pinjaman' => $remainingUnpaid ? 'ongoing' : 'settled',
                ]);

                $this->accounting->createPembayaranPinjamanJurnal(
                    $pembayaran->load('jadwal'),
                    $akunKasId,
                    $akunPiutangPinjamanId,
                    $akunPiutangBungaId
                );
            });

            return redirect()->route('pembayaran-pinjaman.index')->with('toast', [
                'type' => 'success',
                'message' => 'Pembayaran cicilan berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage(),
            ]);
        }
    }

    public function validatedData(Request $request, ?PembayaranPinjaman $pembayaran = null): array
    {
        return $request->validate([
            'id_jadwal' => ['required', 'exists:pinjaman_jadwal,id'],
            'tanggal_bayar' => ['required', 'date'],
            'kd_akun_kas' => ['required', 'exists:akuns,id'],
            'kd_akun_piutang_pinjaman' => ['required', 'exists:akuns,id'],
            'kd_akun_piutang_bunga' => ['required', 'exists:akuns,id'],
            'denda' => ['nullable', 'numeric', 'min:0'],
            'bukti_bayar' => ['nullable', 'string', 'max:255'],
            'metode_bayar' => ['required', Rule::in(['cash', 'transfer', 'potong_gaji'])],
        ]);
    }
}
