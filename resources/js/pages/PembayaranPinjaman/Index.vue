<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/myutils';

type PembayaranPinjaman = {
    id: number;
    tanggal_bayar: string;
    no_kontrak: string | null;
    nik: string | null;
    nama_anggota: string | null;
    angsuran_ke: number | null;
    angsuran_pokok: number;
    angsuran_bunga: number;
    denda: number;
    nominal_bayar: number;
    metode_bayar: string;
    bukti_bayar: string | null;
};

type PaginatedPembayaran = {
    data: PembayaranPinjaman[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

defineProps<{
    pembayarans: PaginatedPembayaran;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pembayaran Cicilan',
                href: '/pembayaran-pinjaman',
            },
        ],
    },
});

function metodeLabel(value: string): string {
    return value.replace('_', ' ');
}
</script>

<template>
    <Head title="Pembayaran Cicilan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Pembayaran Cicilan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Riwayat pembayaran angsuran pinjaman anggota.
                </p>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Anggota</th>
                            <th class="px-4 py-3 font-medium">Kontrak</th>
                            <th class="px-4 py-3 font-medium">Angsuran</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Pokok
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Bunga
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Denda
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Total
                            </th>
                            <th class="px-4 py-3 font-medium">Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="pembayaran in pembayarans.data"
                            :key="pembayaran.id"
                            class="border-b last:border-0 hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                {{ pembayaran.tanggal_bayar }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ pembayaran.nama_anggota ?? '-' }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ pembayaran.nik ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ pembayaran.no_kontrak ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                Ke-{{ pembayaran.angsuran_ke ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatRupiah(pembayaran.angsuran_pokok) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatRupiah(pembayaran.angsuran_bunga) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatRupiah(pembayaran.denda) }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formatRupiah(pembayaran.nominal_bayar) }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge variant="outline" class="capitalize">
                                    {{ metodeLabel(pembayaran.metode_bayar) }}
                                </Badge>
                            </td>
                        </tr>
                        <tr v-if="pembayarans.data.length === 0">
                            <td
                                colspan="9"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Belum ada data pembayaran cicilan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ pembayarans.from ?? 0 }} sampai
                    {{ pembayarans.to ?? 0 }} dari
                    {{ pembayarans.total }} pembayaran
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!pembayarans.prev_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronLeft />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link
                            :href="pembayarans.prev_page_url"
                            preserve-scroll
                        >
                            <ChevronLeft />
                        </Link>
                    </Button>

                    <Button
                        v-if="!pembayarans.next_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronRight />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link
                            :href="pembayarans.next_page_url"
                            preserve-scroll
                        >
                            <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
