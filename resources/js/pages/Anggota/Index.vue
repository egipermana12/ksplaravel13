<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserRound,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type StatusAnggota = 'aktif' | 'nonaktif';
type JenisKelamin = 'L' | 'P';

type Anggota = {
    id: number;
    nik: string;
    nama_anggota: string;
    tanggal_lahir: string;
    jenis_kelamin: JenisKelamin;
    alamat: string | null;
    nomor_hp: string | null;
    tanggal_gabung: string;
    status_anggota: StatusAnggota;
    image_url: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedAnggota = {
    data: Anggota[];
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};

const props = defineProps<{
    anggotas: PaginatedAnggota;
    filters: {
        search: string;
        status: string;
    };
}>();

onMounted(() => {
    router.on('start', () => {
        pageLoading.value = true;
    });

    router.on('finish', () => {
        pageLoading.value = false;
    });
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Anggota',
                href: '/anggota',
            },
        ],
    },
});

const formOpen = ref(false);
const editingAnggota = ref<Anggota | null>(null);
const search = ref(props.filters.search);
const status = ref(props.filters.status);
const selectedIds = ref<number[]>([]);
const pageLoading = ref(false);
const deleteLoadingId = ref<number | null>(null);

const today = new Date().toISOString().slice(0, 10);

const form = useForm<{
    nik: string;
    nama_anggota: string;
    tanggal_lahir: string;
    jenis_kelamin: JenisKelamin;
    alamat: string;
    nomor_hp: string;
    tanggal_gabung: string;
    status_anggota: StatusAnggota;
    path_image: File | null;
}>({
    nik: '',
    nama_anggota: '',
    tanggal_lahir: '',
    jenis_kelamin: 'L',
    alamat: '',
    nomor_hp: '',
    tanggal_gabung: today,
    status_anggota: 'aktif',
    path_image: null,
});

const activeCount = computed(
    () =>
        props.anggotas.data.filter(
            (anggota) => anggota.status_anggota === 'aktif',
        ).length,
);
const currentPageIds = computed(() =>
    props.anggotas.data.map((anggota) => anggota.id),
);
const allCurrentPageSelected = computed(
    () =>
        currentPageIds.value.length > 0 &&
        currentPageIds.value.every((id) => selectedIds.value.includes(id)),
);

function refreshList(): void {
    router.get(
        '/anggota',
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

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch([search, status], () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(refreshList, 300);
});

watch(
    () => currentPageIds.value.join(','),
    () => {
        selectedIds.value = [];
    },
);

function resetForm(): void {
    form.reset();
    form.clearErrors();
    form.tanggal_gabung = today;
    form.jenis_kelamin = 'L';
    form.status_anggota = 'aktif';
    form.path_image = null;
}

function openCreateForm(): void {
    editingAnggota.value = null;
    resetForm();
    formOpen.value = true;
}

function openEditForm(anggota: Anggota): void {
    editingAnggota.value = anggota;
    form.clearErrors();
    form.nik = anggota.nik;
    form.nama_anggota = anggota.nama_anggota;
    form.tanggal_lahir = anggota.tanggal_lahir;
    form.jenis_kelamin = anggota.jenis_kelamin;
    form.alamat = anggota.alamat ?? '';
    form.nomor_hp = anggota.nomor_hp ?? '';
    form.tanggal_gabung = anggota.tanggal_gabung;
    form.status_anggota = anggota.status_anggota;
    form.path_image = null;
    formOpen.value = true;
}

function closeForm(): void {
    formOpen.value = false;
    editingAnggota.value = null;
    resetForm();
}

function onImageChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    form.path_image = input.files?.[0] ?? null;
}

function firstError(errors: Record<string, string>): string {
    return Object.values(errors)[0] ?? 'Periksa kembali data yang diisi.';
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

function toggleCurrentPage(checked: boolean): void {
    selectedIds.value = checked ? [...currentPageIds.value] : [];
}

function submit(): void {
    if (editingAnggota.value) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(`/anggota/${editingAnggota.value.id}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
                toast.success('Data anggota berhasil diperbarui.');
            },
            onError: (errors) => {
                toast.error(firstError(errors));
            },
        });

        return;
    }

    form.post('/anggota', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeForm();
            toast.success('Anggota berhasil ditambahkan.');
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function destroy(anggota: Anggota): void {
    if (!confirm(`Hapus anggota ${anggota.nama_anggota}?`)) {
        return;
    }

    deleteLoadingId.value = anggota.id;

    router.delete(`/anggota/${anggota.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Anggota berhasil dihapus.');
        },
        onError: () => {
            toast.error(
                'Anggota gagal dihapus. Coba ulangi beberapa saat lagi.',
            );
        },
        onFinish: () => {
            deleteLoadingId.value = null;
        },
    });
}

function bulkDestroy(): void {
    const total = selectedIds.value.length;

    if (total === 0) {
        toast.info('Pilih anggota terlebih dahulu.');

        return;
    }

    if (!confirm(`Hapus ${total} anggota terpilih?`)) {
        return;
    }

    router.delete('/anggota/bulk', {
        data: {
            ids: selectedIds.value,
        },
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${total} anggota berhasil dihapus.`);
        },
        onError: (errors) => {
            toast.error(firstError(errors));
        },
    });
}

function genderLabel(value: JenisKelamin): string {
    return value === 'L' ? 'Laki-laki' : 'Perempuan';
}

function resetFilters(): void {
    search.value = '';
    status.value = '';
}
</script>

<template>
    <Head title="Anggota" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Data Anggota
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola identitas anggota koperasi sebagai data utama
                    transaksi.
                </p>
            </div>

            <Button @click="openCreateForm">
                <Plus />
                Tambah
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">Total anggota</div>
                <div class="mt-2 text-2xl font-semibold">
                    {{ anggotas.total }}
                </div>
            </div>
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">
                    Aktif di halaman ini
                </div>
                <div class="mt-2 text-2xl font-semibold">
                    {{ activeCount }}
                </div>
            </div>
            <div class="rounded-lg border bg-card p-4 text-card-foreground">
                <div class="text-sm text-muted-foreground">Ditampilkan</div>
                <div class="mt-2 text-2xl font-semibold">
                    {{ anggotas.from ?? 0 }}-{{ anggotas.to ?? 0 }}
                </div>
            </div>
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
                        placeholder="Cari NIK, nama, atau nomor HP"
                    />
                </div>

                <select
                    v-model="status"
                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                >
                    <option value="">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>

                <Button variant="outline" @click="resetFilters">
                    <X />
                    Reset
                </Button>
            </div>

            <div
                v-if="selectedIds.length > 0"
                class="flex flex-col gap-3 border-b bg-muted/30 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm font-medium">
                    {{ selectedIds.length }} anggota dipilih
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" @click="selectedIds = []">
                        <X />
                        Batal pilih
                    </Button>
                    <Button variant="destructive" @click="bulkDestroy">
                        <Trash2 />
                        Hapus terpilih
                    </Button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-12 px-4 py-3">
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-input"
                                    :checked="allCurrentPageSelected"
                                    :disabled="anggotas.data.length === 0"
                                    title="Pilih semua di halaman ini"
                                    @change="
                                        toggleCurrentPage(
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                    "
                                />
                            </th>
                            <th class="w-16 px-4 py-3 font-medium">Foto</th>
                            <th class="px-4 py-3 font-medium">Anggota</th>
                            <th class="px-4 py-3 font-medium">NIK</th>
                            <th class="px-4 py-3 font-medium">Kontak</th>
                            <th class="px-4 py-3 font-medium">Bergabung</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="w-28 px-4 py-3 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="anggota in anggotas.data"
                            :key="anggota.id"
                            class="border-b last:border-0 hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-input"
                                    :checked="isSelected(anggota.id)"
                                    :title="`Pilih ${anggota.nama_anggota}`"
                                    @change="
                                        toggleRow(
                                            anggota.id,
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                    "
                                />
                            </td>
                            <td class="px-4 py-3">
                                <img
                                    v-if="anggota.image_url"
                                    :src="anggota.image_url"
                                    :alt="anggota.nama_anggota"
                                    class="size-10 rounded-md object-cover"
                                />
                                <div
                                    v-else
                                    class="flex size-10 items-center justify-center rounded-md bg-muted text-muted-foreground"
                                >
                                    <UserRound class="size-5" />
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ anggota.nama_anggota }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ genderLabel(anggota.jenis_kelamin) }} -
                                    {{ anggota.tanggal_lahir }}
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ anggota.nik }}
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ anggota.nomor_hp ?? '-' }}</div>
                                <div
                                    class="max-w-56 truncate text-xs text-muted-foreground"
                                >
                                    {{ anggota.alamat ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <CalendarDays
                                        class="size-4 text-muted-foreground"
                                    />
                                    {{ anggota.tanggal_gabung }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        anggota.status_anggota === 'aktif'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ anggota.status_anggota }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        size="icon-sm"
                                        variant="outline"
                                        :title="`Edit ${anggota.nama_anggota}`"
                                        @click="openEditForm(anggota)"
                                    >
                                        <Pencil />
                                    </Button>
                                    <Button
                                        size="icon-sm"
                                        variant="destructive"
                                        :disabled="
                                            deleteLoadingId === anggota.id
                                        "
                                        :title="`Hapus ${anggota.nama_anggota}`"
                                        @click="destroy(anggota)"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="anggotas.data.length === 0">
                            <td
                                class="px-4 py-12 text-center text-muted-foreground"
                                colspan="8"
                            >
                                Data anggota belum ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ anggotas.from ?? 0 }} sampai
                    {{ anggotas.to ?? 0 }} dari {{ anggotas.total }} anggota
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!anggotas.prev_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronLeft />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="anggotas.prev_page_url" preserve-scroll>
                            <ChevronLeft />
                        </Link>
                    </Button>

                    <Button
                        v-if="!anggotas.next_page_url"
                        variant="outline"
                        size="icon-sm"
                        disabled
                    >
                        <ChevronRight />
                    </Button>

                    <Button v-else as-child variant="outline" size="icon-sm">
                        <Link :href="anggotas.next_page_url" preserve-scroll>
                            <ChevronRight />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="formOpen">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingAnggota ? 'Edit Anggota' : 'Tambah Anggota' }}
                    </DialogTitle>
                    <DialogDescription>
                        Lengkapi data identitas anggota koperasi.
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="nik">NIK</Label>
                            <Input
                                id="nik"
                                v-model="form.nik"
                                maxlength="16"
                                inputmode="numeric"
                                required
                            />
                            <InputError :message="form.errors.nik" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="nama_anggota">Nama anggota</Label>
                            <Input
                                id="nama_anggota"
                                v-model="form.nama_anggota"
                                maxlength="50"
                                required
                            />
                            <InputError :message="form.errors.nama_anggota" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tanggal_lahir">Tanggal lahir</Label>
                            <Input
                                id="tanggal_lahir"
                                v-model="form.tanggal_lahir"
                                type="date"
                                required
                            />
                            <InputError :message="form.errors.tanggal_lahir" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="jenis_kelamin">Jenis kelamin</Label>
                            <select
                                id="jenis_kelamin"
                                v-model="form.jenis_kelamin"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                required
                            >
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <InputError :message="form.errors.jenis_kelamin" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="nomor_hp">Nomor HP</Label>
                            <Input
                                id="nomor_hp"
                                v-model="form.nomor_hp"
                                maxlength="13"
                            />
                            <InputError :message="form.errors.nomor_hp" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tanggal_gabung">Tanggal gabung</Label>
                            <Input
                                id="tanggal_gabung"
                                v-model="form.tanggal_gabung"
                                type="date"
                                required
                            />
                            <InputError :message="form.errors.tanggal_gabung" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status_anggota">Status</Label>
                            <select
                                id="status_anggota"
                                v-model="form.status_anggota"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                required
                            >
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                            <InputError :message="form.errors.status_anggota" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="path_image">Foto</Label>
                            <Input
                                id="path_image"
                                type="file"
                                accept="image/*"
                                @change="onImageChange"
                            />
                            <InputError :message="form.errors.path_image" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="alamat">Alamat</Label>
                        <textarea
                            id="alamat"
                            v-model="form.alamat"
                            class="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            maxlength="100"
                        />
                        <InputError :message="form.errors.alamat" />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeForm"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
    <div
        v-if="pageLoading"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm"
    >
        <div class="rounded-xl bg-white px-6 py-4 shadow-xl">Memuat...</div>
    </div>
</template>
