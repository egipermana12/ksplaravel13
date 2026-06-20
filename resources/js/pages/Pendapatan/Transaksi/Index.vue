<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Plus,
    Search,
    X,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatRupiah } from '@/lib/myutils';

type PendapatanTransaksi = {
    id: number;
    id_kategori: number;
    nama_kategori: string | null;
    tanggal_transaksi: string;
    nominal: number;
    keterangan: string | null;
};

type KategoriPendapatan = {
    id: number;
    nama_kategori: string;
};

type PaginatedTransaksi = {
    data: PendapatanTransaksi[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    transaksis: PaginatedTransaksi;
    kategoris: KategoriPendapatan[];
    filters: {
        search: string;
        id_kategori: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Transaksi Pendapatan',
                href: '/pendapatan-transaksi',
            },
        ],
    },
});

const search = ref(props.filters.search);
const idKategori = ref(props.filters.id_kategori);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch([search, idKategori], () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(
            '/pendapatan-transaksi',
            {
                search: search.value,
                id_kategori: idKategori.value,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function resetFilters(): void {
    search.value = '';
    idKategori.value = '';
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Transaksi Pendapatan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Transaksi Pendapatan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Riwayat pendapatan provisi dan administrasi koperasi.
                </p>
            </div>

            <Button @click="() => router.visit('/pendapatan-transaksi/create')">
                <Plus />
                Tambah
            </Button>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground">
            <div
                class="flex flex-col gap-3 border-b p-4 md:flex-row md:items-center"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        class="pl-9"
                        placeholder="Cari kategori atau keterangan"
                    />
                </div>

                <select
                    v-model="idKategori"
                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                >
                    <option value="">Semua kategori</option>
                    <option
                        v-for="kategori in kategoris"
                        :key="kategori.id"
                        :value="String(kategori.id)"
                    >
                        {{ kategori.nama_kategori }}
                    </option>
                </select>

                <Button variant="outline" @click="resetFilters">
                    <X />
                    Reset
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Keterangan</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Nominal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="transaksi in transaksis.data"
                            :key="transaksi.id"
                            class="border-b last:border-0 hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                {{ formatDate(transaksi.tanggal_transaksi) }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ transaksi.nama_kategori ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ transaksi.keterangan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formatRupiah(transaksi.nominal) }}
                            </td>
                        </tr>
                        <tr v-if="transaksis.data.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Belum ada transaksi pendapatan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ transaksis.from ?? 0 }} sampai
                    {{ transaksis.to ?? 0 }} dari
                    {{ transaksis.total }} transaksi
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!transaksis.prev_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronLeft />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="transaksis.prev_page_url" preserve-scroll>
                            <ChevronLeft />
                        </Link>
                    </Button>

                    <Button
                        v-if="!transaksis.next_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronRight />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="transaksis.next_page_url" preserve-scroll>
                            <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
