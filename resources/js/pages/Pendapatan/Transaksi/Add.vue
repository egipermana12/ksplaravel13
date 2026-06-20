<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Loader2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import InputRupiah from '@/components/InputRupiah.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type KategoriPendapatan = {
    id: number;
    nama_kategori: string;
};

type Akun = {
    id: number;
    kode_akun: string;
    nama_akun: string;
};

defineProps<{
    kategoris: KategoriPendapatan[];
    akunKas: Akun;
    akunPendapatan: Akun;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tambah Transaksi Pendapatan',
                href: '/pendapatan-transaksi/create',
            },
        ],
    },
});

const form = useForm<{
    id_kategori: string;
    tanggal_transaksi: string;
    nominal: number;
    keterangan: string;
}>({
    id_kategori: '',
    tanggal_transaksi: new Date().toISOString().slice(0, 10),
    nominal: 0,
    keterangan: '',
});

function submit(): void {
    form.post('/pendapatan-transaksi', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pendapatan transaksi berhasil disimpan.');
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Pendapatan transaksi gagal disimpan. Periksa kembali inputnya.',
            );
        },
    });
}
</script>

<template>
    <Head title="Tambah Transaksi Pendapatan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tambah Transaksi Pendapatan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Input pendapatan provisi atau administrasi koperasi.
                </p>
            </div>

            <Button
                variant="outline"
                @click="() => router.visit('/pendapatan-transaksi')"
            >
                <ChevronLeft />
                Kembali
            </Button>
        </div>

        <form class="rounded-lg border bg-card p-4" @submit.prevent="submit">
            <div class="grid gap-5">
                <div class="grid gap-4 rounded-lg border bg-background p-4 md:grid-cols-2">
                    <div>
                        <div class="text-xs text-muted-foreground">Debit</div>
                        <div class="mt-1 font-medium">
                            {{ akunKas.kode_akun }} - {{ akunKas.nama_akun }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">Kredit</div>
                        <div class="mt-1 font-medium">
                            {{ akunPendapatan.kode_akun }} -
                            {{ akunPendapatan.nama_akun }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="id_kategori">Kategori</Label>
                        <select
                            id="id_kategori"
                            v-model="form.id_kategori"
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            required
                        >
                            <option value="">Pilih kategori</option>
                            <option
                                v-for="kategori in kategoris"
                                :key="kategori.id"
                                :value="String(kategori.id)"
                            >
                                {{ kategori.nama_kategori }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_kategori" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="tanggal_transaksi">Tanggal transaksi</Label>
                        <Input
                            id="tanggal_transaksi"
                            v-model="form.tanggal_transaksi"
                            type="date"
                            required
                        />
                        <InputError :message="form.errors.tanggal_transaksi" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nominal">Nominal</Label>
                        <InputRupiah id="nominal" v-model="form.nominal" />
                        <InputError :message="form.errors.nominal" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="keterangan">Keterangan</Label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="4"
                        class="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        placeholder="Catatan transaksi"
                    />
                    <InputError :message="form.errors.keterangan" />
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        @click="() => router.visit('/pendapatan-transaksi')"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="
                            form.processing ||
                            !form.id_kategori ||
                            Number(form.nominal) <= 0
                        "
                    >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Simpan
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
