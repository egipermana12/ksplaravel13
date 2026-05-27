<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Loader2, Search, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatRupiah } from '@/lib/myutils';

type JurnalDetail = {
    id: number;
    nama_akun: string;
    kode_akun: string;
    debit: number;
    kredit: number;
};

type Jurnals = {
    id: number;
    tanggal: string;
    ref_type: string | null;
    details: JurnalDetail[]; // Relasi detail
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedJurnal = {
    data: Jurnals[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};

const props = defineProps<{
    jurnals: PaginatedJurnal;
    filters: {
        from: string;
        to: string;
    };
}>();

const pageLoading = ref(false);
const removeRouterListeners = ref<Array<() => void>>([]);

onMounted(() => {
    const removeStartListener = router.on('start', () => {
        pageLoading.value = true;
    });

    const removeFinishListener = router.on('finish', () => {
        pageLoading.value = false;
    });

    removeRouterListeners.value = [removeStartListener, removeFinishListener];
});

onUnmounted(() => {
    removeRouterListeners.value.forEach((removeListener) => removeListener());
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Jurnal Transaksi',
                href: '/jurnals',
            },
        ],
    },
});

const filterForm = ref({
    from: props.filters.from,
    to: props.filters.to,
});

function refreshList(): void {
    router.get(
        '/jurnals',
        {
            from: filterForm.value.from,
            to: filterForm.value.to,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}



function resetFilter(): void {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const formatDate = (date: Date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    filterForm.value.from = formatDate(firstDay);
    filterForm.value.to = formatDate(lastDay);

    refreshList();
}
</script>

<template>
    <Head title="Jurnal Transaksi" />
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Data Jurnal Transaksi
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data Jurnal Transaksi
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 rounded-lg border bg-muted/20 p-3">
            <div class="flex items-center gap-2">
                <Label for="from" class="text-xs">Dari:</Label>
                <Input id="from" type="date" v-model="filterForm.from" class="w-40 bg-background" />
            </div>
            <div class="flex items-center gap-2">
                <Label for="to" class="text-xs">Sampai:</Label>
                <Input id="to" type="date" v-model="filterForm.to" class="w-40 bg-background" />
            </div>
            <Button size="sm" :disabled="pageLoading" @click="refreshList">
                <Loader2 v-if="pageLoading" class="mr-2 h-4 w-4 animate-spin" />
                <Search v-else class="mr-2 h-4 w-4" />
                Terapkan
            </Button>
            <Button variant="outline" size="sm" @click="resetFilter" class="ml-auto">
                <X class="mr-2 h-4 w-4" />
                Reset
            </Button>
        </div>

        <div class="relative rounded-lg border bg-card text-card-foreground">
            <div v-if="pageLoading"
                class="absolute inset-0 z-10 flex items-center justify-center bg-background/60 backdrop-blur-[1px]">
                <div class="flex items-center gap-2 rounded-md border bg-background px-4 py-2 text-sm shadow-sm">
                    <Loader2 class="h-4 w-4 animate-spin" />
                    Memuat data jurnal...
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Jenis</th>
                            <th class="px-4 py-3 font-medium">Akun</th>
                            <th class="px-4 py-3 font-medium">Debet</th>
                            <th class="px-4 py-3 font-medium">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="jurnal in jurnals.data" :key="jurnal.id">
                            <tr class="border-b hover:bg-muted/40">
                                <td class="px-4 py-3 align-top font-medium">
                                    {{ jurnal.tanggal }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="text-sm text-muted-foreground">{{ jurnal.ref_type }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="jurnal.details[0]">
                                        {{ jurnal.details[0].kode_akun }} - {{ jurnal.details[0].nama_akun }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatRupiah(jurnal.details[0]?.debit) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatRupiah(jurnal.details[0]?.kredit) }}
                                </td>
                            </tr>

                            <tr v-for="detail in jurnal.details.slice(1)" :key="detail.id"
                                class="border-b border-dashed last:border-solid hover:bg-muted/40">
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 pl-8 italic text-muted-foreground">
                                    {{ detail.kode_akun }} - {{ detail.nama_akun }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatRupiah(detail.debit) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatRupiah(detail.kredit) }}
                                </td>
                            </tr>
                        </template>

                        <tr v-if="jurnals.data.length === 0">
                            <td colspan="5" class="py-10 text-center text-muted-foreground">
                                Tidak ada data transaksi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ jurnals.from ?? 0 }} sampai
                    {{ jurnals.to ?? 0 }} dari {{ jurnals.total }} Jurnals
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="!jurnals.prev_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronLeft />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="jurnals.prev_page_url" preserve-scroll>
                        <ChevronLeft />
                        </Link>
                    </Button>

                    <Button v-if="!jurnals.next_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronRight />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="jurnals.next_page_url" preserve-scroll>
                        <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
