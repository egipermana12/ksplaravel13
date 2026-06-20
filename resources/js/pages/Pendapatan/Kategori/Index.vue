<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Loader2,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type KategoriPendapatan = {
    id: number;
    nama_kategori: string;
    transaksi_count: number;
};

type PaginatedKategori = {
    data: KategoriPendapatan[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    kategoris: PaginatedKategori;
    filters: {
        search: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kategori Pendapatan',
                href: '/pendapatan-kategori',
            },
        ],
    },
});

const search = ref(props.filters.search);
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selectedKategori = ref<KategoriPendapatan | null>(null);

const createForm = useForm({
    nama_kategori: '',
});

const editForm = useForm({
    nama_kategori: '',
});

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(
            '/pendapatan-kategori',
            { search: search.value },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function resetFilters(): void {
    search.value = '';
}

function submitCreate(): void {
    createForm.post('/pendapatan-kategori', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Kategori pendapatan berhasil ditambahkan.');
            createForm.reset();
            showCreateDialog.value = false;
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Kategori gagal disimpan. Periksa kembali inputnya.',
            );
        },
    });
}

function openEditDialog(kategori: KategoriPendapatan): void {
    selectedKategori.value = kategori;
    editForm.nama_kategori = kategori.nama_kategori;
    editForm.clearErrors();
    showEditDialog.value = true;
}

function submitEdit(): void {
    if (!selectedKategori.value) {
        return;
    }

    editForm.put(`/pendapatan-kategori/${selectedKategori.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Kategori pendapatan berhasil diperbarui.');
            showEditDialog.value = false;
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Kategori gagal diperbarui. Periksa kembali inputnya.',
            );
        },
    });
}

function destroyKategori(kategori: KategoriPendapatan): void {
    if (
        !window.confirm(
            `Hapus kategori "${kategori.nama_kategori}"?`,
        )
    ) {
        return;
    }

    router.delete(`/pendapatan-kategori/${kategori.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Kategori pendapatan berhasil dihapus.'),
        onError: () => toast.error('Kategori gagal dihapus.'),
    });
}
</script>

<template>
    <Head title="Kategori Pendapatan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Kategori Pendapatan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola kategori untuk transaksi pendapatan koperasi.
                </p>
            </div>

            <Button @click="showCreateDialog = true">
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
                        placeholder="Cari kategori pendapatan"
                    />
                </div>

                <Button variant="outline" @click="resetFilters">
                    <X />
                    Reset
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Transaksi
                            </th>
                            <th class="w-32 px-4 py-3 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="kategori in kategoris.data"
                            :key="kategori.id"
                            class="border-b last:border-0 hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ kategori.nama_kategori }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ kategori.transaksi_count }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="icon-sm"
                                        @click="openEditDialog(kategori)"
                                    >
                                        <Pencil />
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="icon-sm"
                                        @click="destroyKategori(kategori)"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="kategoris.data.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Belum ada kategori pendapatan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ kategoris.from ?? 0 }} sampai
                    {{ kategoris.to ?? 0 }} dari {{ kategoris.total }} kategori
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!kategoris.prev_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronLeft />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="kategoris.prev_page_url" preserve-scroll>
                            <ChevronLeft />
                        </Link>
                    </Button>

                    <Button
                        v-if="!kategoris.next_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronRight />
                    </Button>
                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="kategoris.next_page_url" preserve-scroll>
                            <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="showCreateDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tambah Kategori Pendapatan</DialogTitle>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="nama_kategori">Nama kategori</Label>
                        <Input
                            id="nama_kategori"
                            v-model="createForm.nama_kategori"
                            required
                        />
                        <InputError :message="createForm.errors.nama_kategori" />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showCreateDialog = false"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            <Loader2
                                v-if="createForm.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showEditDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Kategori Pendapatan</DialogTitle>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit_nama_kategori">Nama kategori</Label>
                        <Input
                            id="edit_nama_kategori"
                            v-model="editForm.nama_kategori"
                            required
                        />
                        <InputError :message="editForm.errors.nama_kategori" />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showEditDialog = false"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            <Loader2
                                v-if="editForm.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
