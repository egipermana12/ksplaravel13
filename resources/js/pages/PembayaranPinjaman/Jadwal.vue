<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Search, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatRupiah } from '@/lib/myutils';

type JadwalPembayaran = {
    id: number;
    no_kontrak: string | null;
    nik: string | null;
    nama_anggota: string | null;
    angsuran_ke: number;
    tgl_jatuh_tempo: string | null;
    angsuran_pokok: number;
    angsuran_bunga: number;
    total_tagihan: number;
    status_bayar: 'belum_bayar' | 'cicil' | 'lunas';
};

type PaginatedJadwal = {
    data: JadwalPembayaran[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    jadwals: PaginatedJadwal;
    filters: {
        search: string;
        status: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Jadwal Pembayaran',
                href: '/pembayaran-pinjaman/jadwal',
            },
        ],
    },
});

const search = ref(props.filters.search);
const status = ref(props.filters.status);

watch(status, () => {
    applyFilters();
});

function applyFilters(): void {
    router.get(
        '/pembayaran-pinjaman/jadwal',
        {
            search: search.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function resetFilters(): void {
    search.value = '';
    status.value = '';
    router.get('/pembayaran-pinjaman/jadwal', {}, { replace: true });
}

function statusLabel(value: JadwalPembayaran['status_bayar']): string {
    return {
        belum_bayar: 'Belum Bayar',
        cicil: 'Cicil',
        lunas: 'Lunas',
    }[value];
}

function statusVariant(
    value: JadwalPembayaran['status_bayar'],
): 'default' | 'secondary' | 'outline' {
    if (value === 'lunas') {
        return 'default';
    }

    if (value === 'cicil') {
        return 'secondary';
    }

    return 'outline';
}
</script>

<template>
    <Head title="Jadwal Pembayaran" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Jadwal Pembayaran
                </h1>
                <p class="text-sm text-muted-foreground">
                    Daftar jatuh tempo cicilan pinjaman anggota.
                </p>
            </div>
        </div>

        <div
            class="grid gap-3 rounded-lg border bg-card p-4 md:grid-cols-[1fr_180px_auto_auto]"
        >
            <div class="relative">
                <Search
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    class="pl-9"
                    placeholder="Cari anggota, NIK, kontrak, atau jatuh tempo"
                    @keyup.enter="applyFilters"
                />
            </div>

            <select
                v-model="status"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
            >
                <option value="">Semua Status</option>
                <option value="belum_bayar">Belum Bayar</option>
                <option value="cicil">Cicil</option>
                <option value="lunas">Lunas</option>
            </select>

            <Button variant="outline" @click="applyFilters">
                <Search />
                Cari
            </Button>

            <Button variant="ghost" @click="resetFilters">
                <X />
                Reset
            </Button>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Jatuh Tempo</th>
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
                                Tagihan
                            </th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="jadwal in jadwals.data"
                            :key="jadwal.id"
                            class="border-b last:border-0 hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                {{ jadwal.tgl_jatuh_tempo ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ jadwal.nama_anggota ?? '-' }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ jadwal.nik ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ jadwal.no_kontrak ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                Ke-{{ jadwal.angsuran_ke }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatRupiah(jadwal.angsuran_pokok) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatRupiah(jadwal.angsuran_bunga) }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formatRupiah(jadwal.total_tagihan) }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(jadwal.status_bayar)">
                                    {{ statusLabel(jadwal.status_bayar) }}
                                </Badge>
                            </td>
                        </tr>
                        <tr v-if="jadwals.data.length === 0">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Belum ada data jadwal pembayaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ jadwals.from ?? 0 }} sampai
                    {{ jadwals.to ?? 0 }} dari {{ jadwals.total }} jadwal
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!jadwals.prev_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronLeft />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="jadwals.prev_page_url" preserve-scroll>
                            <ChevronLeft />
                        </Link>
                    </Button>

                    <Button
                        v-if="!jadwals.next_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronRight />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="jadwals.next_page_url" preserve-scroll>
                            <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
