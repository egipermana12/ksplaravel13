<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Loader2,
    Plus,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import { formatRupiah } from '@/lib/myutils';

type Pinjaman = {
    id: number;
    id_anggota: number;
    nik: string | null;
    nama_anggota: string | null;
    jumlah_pinjaman: number;
    tenor: number;
    bunga: number;
    total_kewajiban: number;
    tanggal_pengajuan: string;
    tanggal_disetujui: string | null;
    jenis_bunga: string;
    status_pinjaman: 'pending' | 'approved' | 'rejected' | 'settled' | 'ongoing';
    no_kontrak: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedPinjaman = {
    data: Pinjaman[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};

const props = defineProps<{
    pinjamans: PaginatedPinjaman;
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
                title: 'Pinjaman Anggota',
                href: '/pinjaman',
            },
        ],
    },
});

const selectedIds = ref<number[]>([]);
const approvingId = ref<number | null>(null);
const currentPageIds = computed(() =>
    props.pinjamans.data.map((pinjaman) => pinjaman.id),
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

function approve(pinjaman: Pinjaman): void {
    if (!confirm(`Setujui pinjaman ${pinjaman.no_kontrak}?`)) {
        return;
    }

    approvingId.value = pinjaman.id;

    router.patch(
        `/pinjaman/${pinjaman.id}/approve`,
        {
            tanggal_disetujui: new Date().toISOString().slice(0, 10),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Pinjaman berhasil disetujui.');
            },
            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                        'Pinjaman gagal disetujui. Periksa kembali datanya.',
                );
            },
            onFinish: () => {
                approvingId.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Pinjaman Anggota" />
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div v-if="pageLoading"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/60 backdrop-blur-[1px]">
            <div class="flex items-center gap-2 rounded-md border bg-background px-4 py-2 text-sm shadow-sm">
                <Loader2 class="h-4 w-4 animate-spin" />
                Memuat data pinjaman...
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Data Pinjaman Anggota
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data pinjaman anggota koperasi.
                </p>
            </div>

            <Button @click="() => router.visit('/pinjaman/create')">
                <Plus />
                Tambah
            </Button>
        </div>

        <!-- table -->
        <div class="rounded-lg border bg-card text-card-foreground">
            <!-- untuk filter -->

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-12 px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-input"
                                    :disabled="pinjamans.data.length === 0" :checked="allCurrentPageSelected" @change="
                                        toggleCurrentPage(
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                        " title="Pilih semua di halaman ini" />
                            </th>
                            <th class="px-4 py-3 font-medium">Anggota</th>
                            <th class="px-4 py-3 font-medium">
                                Status Pinjaman
                            </th>
                            <th class="px-4 py-3 font-medium">Jumlah Pinjaman</th>
                            <th class="px-4 py-3 font-medium">Tenor</th>
                            <th class="px-4 py-3 font-medium">Bunga</th>
                            <th class="w-32 px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="pinjaman in pinjamans.data" :key="pinjaman.id"
                            class="border-b last:border-0 hover:bg-muted/40">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-input" :title="`Pilih ${pinjaman.id}`"
                                    :checked="isSelected(pinjaman.id)" @change="
                                        toggleRow(
                                            pinjaman.id,
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                        " />
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ pinjaman.nama_anggota }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ pinjaman.nik }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="pinjaman.status_pinjaman === 'rejected'
                                    ? 'destructive'
                                    : pinjaman.status_pinjaman ===
                                        'pending'
                                        ? 'default'
                                        : 'outline'
                                    ">
                                    {{ pinjaman.status_pinjaman }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                {{ formatRupiah(pinjaman.jumlah_pinjaman) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ pinjaman.tenor }} bulan
                            </td>
                            <td class="px-4 py-3">
                                {{ pinjaman.bunga }} %
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <Button
                                        v-if="pinjaman.status_pinjaman === 'pending'"
                                        size="sm"
                                        variant="outline"
                                        :disabled="approvingId === pinjaman.id"
                                        @click="approve(pinjaman)"
                                    >
                                        <Loader2
                                            v-if="approvingId === pinjaman.id"
                                            class="h-4 w-4 animate-spin"
                                        />
                                        <CheckCircle2 v-else class="h-4 w-4" />
                                        Approve
                                    </Button>
                                    <span
                                        v-else
                                        class="text-xs text-muted-foreground"
                                    >
                                        -
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pinjamans.data.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Data pinjaman belum tersedia.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ pinjamans.from ?? 0 }} sampai
                    {{ pinjamans.to ?? 0 }} dari {{ pinjamans.total }} pinjaman
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="!pinjamans.prev_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronLeft />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="pinjamans.prev_page_url" preserve-scroll>
                        <ChevronLeft />
                        </Link>
                    </Button>

                    <Button v-if="!pinjamans.next_page_url" variant="outline" size="icon-sm" disabled>
                        <ChevronRight />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="pinjamans.next_page_url" preserve-scroll>
                        <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
