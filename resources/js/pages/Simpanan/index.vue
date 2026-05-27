<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { formatRupiah } from '@/lib/myutils';

type Simpanan = {
    id: number;
    id_anggota: number;
    nik: string | null;
    nama_anggota: string | null;
    jenis_simpanan: string;
    nominal: number;
    bukti_setor: string;
    tanggal_setor: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedSimpanan = {
    data: Simpanan[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};

const props = defineProps<{
    simpanans: PaginatedSimpanan;
    simpananWajibTotal: number;
    totalNominalWajib: number;
    simpananPokokTotal: number;
    totalNominalPokok: number;
    simpananSukarelaTotal: number;
    totalNominalSukarela: number;
    filters: {
        jenis_simpanan: string;
        cari_anggota: string;
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
                title: 'Simpanan Anggota',
                href: '/simpanan',
            },
        ],
    },
});

// Filter states
const cari_anggota = ref(props.filters.cari_anggota);
const jenis_simpanan = ref(props.filters.jenis_simpanan);

function refreshList(): void {
    router.get(
        '/simpanan',
        {
            cari_anggota: cari_anggota.value,
            jenis_simpanan: jenis_simpanan.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch([cari_anggota, jenis_simpanan], () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(refreshList, 300);
});

const selectedIds = ref<number[]>([]);
const currentPageIds = computed(() =>
    props.simpanans.data.map((simpanan) => simpanan.id),
);
const allCurrentPageSelected = computed(
    () =>
        currentPageIds.value.length > 0 &&
        currentPageIds.value.every((id) => selectedIds.value.includes(id)),
);

function toggleCurrentPage(checked: boolean): void {
    selectedIds.value = checked ? [...currentPageIds.value] : [];
}

function isSelected(id: number): boolean {
    return selectedIds.value.includes(id);
}

function toggleRow(id: number, checked: boolean): void {
    if (checked) {
        selectedIds.value = [...new Set([...selectedIds.value, id])];

        return;
    }

    selectedIds.value = selectedIds.value.filter(
        (selectedId) => selectedId !== id,
    );
}


//reset filter
function resetFilters(): void {
    cari_anggota.value = '';
    jenis_simpanan.value = '';
}
</script>
<template>
    <Head title="Simpanan Anggota" />
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div v-if="pageLoading"
            class="absolute inset-0 z-10 flex items-center justify-center bg-background/60 backdrop-blur-[1px]">
            <div class="flex items-center gap-2 rounded-md border bg-background px-4 py-2 text-sm shadow-sm">
                <Loader2 class="h-4 w-4 animate-spin" />
                Memuat data jurnal...
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Data Simpanan Anggota
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data simpanan anggota koperasi.
                </p>
            </div>

            <Button @click="() => router.visit('/simpanan/create')">
                <Plus />
                Tambah
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">Simpanan wajib</div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold">
                        {{ simpananWajibTotal }} Data
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatRupiah(totalNominalWajib) }}
                    </p>
                </div>
            </div>
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">Simpanan pokok</div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold">
                        {{ simpananPokokTotal }} Data
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatRupiah(totalNominalPokok) }}
                    </p>
                </div>
            </div>
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">
                    Simpanan sukarela
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold">
                        {{ simpananSukarelaTotal }} Data
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatRupiah(totalNominalSukarela) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- table -->
        <div class="rounded-lg border bg-card text-card-foreground">
            <div class="flex flex-col gap-3 border-b p-4 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="cari_anggota" class="pl-9" placeholder="Cari NIK, nama, atau nomor HP" />
                </div>

                <select v-model="jenis_simpanan"
                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    <option value="">Semua jenis</option>
                    <option value="wajib">Wajib</option>
                    <option value="pokok">Pokok</option>
                    <option value="sukarela">Sukarela</option>
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
                            <th class="w-12 px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-input"
                                    :disabled="simpanans.data.length === 0" :checked="allCurrentPageSelected" @change="
                                        toggleCurrentPage(
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                        " title="Pilih semua di halaman ini" />
                            </th>
                            <th class="px-4 py-3 font-medium">Anggota</th>
                            <th class="px-4 py-3 font-medium">
                                Jenis Simpanan
                            </th>
                            <th class="px-4 py-3 font-medium">Nominal</th>
                            <th class="px-4 py-3 font-medium">Bukti Setor</th>
                            <th class="px-4 py-3 font-medium">Tanggal Setor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="simpanan in simpanans.data" :key="simpanan.id"
                            class="border-b last:border-0 hover:bg-muted/40">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-input" :title="`Pilih ${simpanan.id}`"
                                    :checked="isSelected(simpanan.id)" @change="
                                        toggleRow(
                                            simpanan.id,
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                        " />
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ simpanan.nama_anggota }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ simpanan.nik }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="simpanan.jenis_simpanan === 'wajib'
                                    ? 'destructive'
                                    : simpanan.jenis_simpanan ===
                                        'pokok'
                                        ? 'default'
                                        : 'outline'
                                    ">
                                    {{ simpanan.jenis_simpanan }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                {{ formatRupiah(simpanan.nominal) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ simpanan.bukti_setor ? 'Ada' : 'Tidak Ada' }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    new Date(
                                        simpanan.tanggal_setor,
                                    ).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: 'long',
                                        year: 'numeric',
                                    })
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ simpanans.from ?? 0 }} sampai
                    {{ simpanans.to ?? 0 }} dari {{ simpanans.total }} simpanan
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="!simpanans.prev_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronLeft />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="simpanans.prev_page_url" preserve-scroll>
                        <ChevronLeft />
                        </Link>
                    </Button>

                    <Button v-if="!simpanans.next_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronRight />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="simpanans.next_page_url" preserve-scroll>
                        <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
        <!-- table -->
    </div>
</template>
;
