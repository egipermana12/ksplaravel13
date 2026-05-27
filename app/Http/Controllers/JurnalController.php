<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JurnalController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date', 'before_or_equal:to'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->endOfMonth()->toDateString();

        $jurnals = Jurnal::with(['detailJurnals.akun'])
            ->whereBetween('tanggal', [$from, $to])
            ->orderBy('tanggal', 'desc')
            ->paginate(50)
            ->withQueryString()
            ->through(fn (Jurnal $jurnal): array => [
                'id' => $jurnal->id,
                'tanggal' => $jurnal->tanggal->toDateString(),
                'ref_type' => $jurnal->ref_type_transaksi,
                'details' => $jurnal->detailJurnals->map(fn ($detail): array => [
                    'id' => $detail->id,
                    'nama_akun' => $detail->akun?->nama_akun ?? '-',
                    'kode_akun' => $detail->akun?->kode_akun ?? '-',
                    'debit' => (float) $detail->debit,
                    'kredit' => (float) $detail->kredit,
                ]),
            ]);

        return Inertia::render('Jurnal/Index', [
            'jurnals' => $jurnals,
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
