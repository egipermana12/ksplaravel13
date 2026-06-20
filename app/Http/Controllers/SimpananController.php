<?php

namespace App\Http\Controllers;

use App\Models\Simpanan;
use App\Models\Akun;
use App\Services\AccountingService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Inertia\Inertia;
use Inertia\Response;

class SimpananController extends Controller
{
    protected $accounting;
    protected $kd_akun_piutang = '201';
    protected $kd_akun_kas = '101';

    // Dependency Injection
    public function __construct(AccountingService $accounting)
    {
        $this->accounting = $accounting;
    }

    public function index(Request $request): Response
    {
        $jns_simpanan = $request->string('jenis_simpanan')->toString();
        $cari_anggota = $request->string('cari_anggota')->toString();

        $simpanans = Simpanan::query()
            // 1. Hanya ambil kolom yang dibutuhkan dari relasi
            ->with(['anggota' => function ($query) {
                $query->select('id', 'nik', 'nama_anggota');
            }])
            // 2. Batasi juga kolom dari tabel simpanan sendiri jika perlu
            ->select('id', 'id_anggota', 'jenis_simpanan', 'nominal', 'bukti_setor', 'tanggal_setor')
            //cari anggota
            ->when($cari_anggota !== '', function ($query) use ($cari_anggota): void {
                $query->whereHas('anggota', function ($query) use ($cari_anggota): void {
                    $query->where('nik', 'like', "%{$cari_anggota}%")
                        ->orWhere('nama_anggota', 'like', "%{$cari_anggota}%");
                });
            })
            //filter jenis simpanan
            ->when(in_array($jns_simpanan, ['wajib', 'pokok', 'sukarela'], true), function ($query) use ($jns_simpanan): void {
                $query->where('jenis_simpanan', $jns_simpanan);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Simpanan $simpanan): array => [
                'id' => $simpanan->id,
                'id_anggota' => $simpanan->id_anggota,
                'nik' => $simpanan->anggota?->nik,
                'nama_anggota' => $simpanan->anggota?->nama_anggota,
                'jenis_simpanan' => $simpanan->jenis_simpanan,
                'nominal' => $simpanan->nominal,
                'bukti_setor' => $simpanan->bukti_setor,
                'tanggal_setor' => $simpanan->tanggal_setor?->toDateString(),
            ]);

        $stats = Simpanan::query() // Mulai Query Builder
            ->selectRaw("
                jenis_simpanan, 
                count(*) as total_count, 
                sum(nominal) as total_nominal
            ")
            ->groupBy('jenis_simpanan')
            ->get() // Akhiri dengan get() untuk menarik data
            ->keyBy('jenis_simpanan');

        return Inertia::render('Simpanan/index', [
            'simpanans' => $simpanans,
            'simpananWajibTotal' => $stats->get('wajib')?->total_count ?? 0,
            'totalNominalWajib' => (int)($stats->get('wajib')?->total_nominal ?? 0),
            'simpananPokokTotal' => $stats->get('pokok')?->total_count ?? 0,
            'totalNominalPokok' => (int)($stats->get('pokok')?->total_nominal ?? 0),
            'simpananSukarelaTotal' => $stats->get('sukarela')?->total_count ?? 0,
            'totalNominalSukarela' => (int)($stats->get('sukarela')?->total_nominal ?? 0),
            'filters' => [
                'jenis_simpanan' => $jns_simpanan,
                'cari_anggota' => $cari_anggota,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Simpanan/Add', [
            'akunPiutang' => Akun::where('kode_akun', $this->kd_akun_piutang)->firstOrFail(),
            'akunKas' => Akun::where('kode_akun', $this->kd_akun_kas)->firstOrFail(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        try {
            $result = DB::transaction(function () use ($validated) {
                // 1. Simpan Data Simpanan
                $simpanan = Simpanan::create($validated);

                // AMBIL DATA AKUN DARI VALIDATED
                $akunPiutangId = $validated['akunPiutang'];
                $akunKasId = $validated['akunKas'];

                // 2. Panggil Service Akuntansi
                $jurnal = $this->accounting->createSimpanan($simpanan, $akunPiutangId, $akunKasId);

                return compact('simpanan', 'jurnal');
            });

            // Redirect ke halaman index dengan flash message sukses
            return redirect()->route('simpanan.index')->with('success', 'Simpanan berhasil disimpan dan jurnal telah dicatat.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'transaction' => 'Gagal mencatat transaksi: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function validatedData(Request $request, ?Simpanan $simpanan = null): array
    {
        return $request->validate([
            'id_anggota' => ['required', 'exists:anggotas,id'],
            'nama_anggota' => ['required'],
            'nik' => ['required'],
            'jenis_simpanan' => ['required', Rule::in(['wajib', 'pokok', 'sukarela'])],
            'akunPiutang' => ['required', 'exists:akuns,id'],
            'akunKas' => ['required', 'exists:akuns,id'],
            'nominal' => ['required', 'numeric', 'min:1000'],
            'bukti_setor' => ['required'],
            'tanggal_setor' => ['required', 'date'],
        ]);
    }
}
