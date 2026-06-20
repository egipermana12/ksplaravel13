<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
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
import DataTableCustom from '@/components/DataTableCustom.vue';
import InputRupiah from '@/components/InputRupiah.vue';

// Ambil page props agar bisa membaca flash messages
const page = usePage();

const props = defineProps<{
    akunPiutang: {
        id: number;
        kode_akun: string;
        nama_akun: string;
    };
    akunKas: {
        id: number;
        kode_akun: string;
        nama_akun: string;
    };
}>();

const form = useForm<{
    id_anggota: string;
    nik: string;
    nama_anggota: string;
    jenis_simpanan: string;
    nominal: string;
    bukti_setor: string;
    ket: string;
    tanggal_setor: string;
    akunPiutang: number;
    akunKas: number;
}>({
    id_anggota: '',
    nik: '',
    nama_anggota: '',
    jenis_simpanan: '',
    nominal: 0,
    bukti_setor: '',
    ket: '',
    tanggal_setor: new Date().toISOString().split('T')[0],
    akunPiutang: props.akunPiutang.id,
    akunKas: props.akunKas.id,
});

const modalCariAnggota = ref(false);

type SelectedAnggota = {
    id: string;
    nik: string;
    nama_anggota: string;
};

function handleSelectedAnggota(anggota: any) {
    form.id_anggota = anggota.id;
    form.nik = anggota.nik;
    form.nama_anggota = anggota.nama_anggota;
    modalCariAnggota.value = false;
}

// simpan data simpanan
function submit() {
    form.post('/simpanan', {
        onSuccess: () => {
            toast.success('Simpanan berhasil disimpan.');
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Gagal menyimpan. Periksa kembali inputan Anda.',
            );
        },
    });
}
</script>

<template>
    <Head title="Tambah Simpanan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tambah Simpanan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Tambah data simpanan anggota koperasi
                </p>
            </div>
            <div>
                <Button
                    @click="() => router.visit('/simpanan')"
                    variant="outline"
                >
                    <ChevronLeft class="me-2 h-4 w-4" />
                    Kembali
                </Button>
            </div>
        </div>

        <!-- form -->
        <div class="rounded-lg border bg-card text-card-foreground">
            <form @submit.prevent="submit">
                <div class="grid w-full gap-4 p-4">
                    <!-- SECTION 1: AREA CARI ANGGOTA (Dibuat terpisah seperti Card tersendiri) -->
                    <input type="hidden" v-model="form.id_anggota" />
                    <input type="hidden" :value="form.akunPiutang" />
                    <input type="hidden" :value="form.akunKas" />

                    <div
                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-5 shadow-inner"
                    >
                        <!-- Header Kecil untuk memberi konteks -->
                        <div
                            class="mb-4 flex items-center justify-between border-b border-slate-200 pb-2"
                        >
                            <h3 class="text-sm font-semibold text-slate-700">
                                Informasi Donatur / Anggota
                            </h3>

                            <!-- Tombol Cari dipisah di pojok kanan atas area ini, tidak sejajar vertikal dengan input -->
                            <Button
                                class="flex h-8 cursor-pointer items-center px-4 py-1.5 text-xs"
                                @click="modalCariAnggota = true"
                                type="button"
                            >
                                <Search class="mr-1.5 h-3.5 w-3.5" />
                                Pilih Anggota dari Database
                            </Button>
                        </div>

                        <!-- Grid Input Data Anggota (Hanya 2 kolom, terlihat kokoh dan bersih) -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="nik" class="text-slate-600"
                                    >NIK</Label
                                >
                                <Input
                                    id="nik"
                                    v-model="form.nik"
                                    disabled
                                    class="cursor-not-allowed border-slate-200 bg-white text-slate-500 shadow-none"
                                    placeholder="Belum ada anggota dipilih"
                                />
                                <InputError :message="form.errors.nik" />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="nama_anggota" class="text-slate-600"
                                    >Nama Anggota</Label
                                >
                                <Input
                                    id="nama_anggota"
                                    v-model="form.nama_anggota"
                                    disabled
                                    class="cursor-not-allowed border-slate-200 bg-white text-slate-500 shadow-none"
                                    placeholder="Nama otomatis terisi"
                                />
                                <InputError
                                    :message="form.errors.nama_anggota"
                                />
                            </div>
                        </div>

                        <!-- untuk akun -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex flex-col space-y-1.5">
                                <Label for="akun_piutang" class="text-slate-600"
                                    >Akun Piutang</Label
                                >
                                <Input
                                    id="akun_piutang"
                                    :model-value="props.akunPiutang.nama_akun"
                                    disabled
                                    class="cursor-not-allowed border-slate-200 bg-white text-slate-500 shadow-none"
                                />
                                <InputError
                                    :message="form.errors.akunPiutang"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="akun_kas" class="text-slate-600"
                                    >Akun Kas</Label
                                >
                                <Input
                                    id="akun_kas"
                                    :model-value="props.akunKas.nama_akun"
                                    disabled
                                    class="cursor-not-allowed border-slate-200 bg-white text-slate-500 shadow-none"
                                />
                                <InputError :message="form.errors.akunKas" />
                            </div>
                        </div>
                    </div>

                    <!-- ROW 2 -->
                    <div class="space-y-5">
                        <h3 class="px-1 text-sm font-semibold text-slate-700">
                            Detail Pengajuan Simpanan
                        </h3>

                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-2"
                        >
                            <div class="flex flex-col space-y-1.5">
                                <Label for="jenis_simpanan"
                                    >Jenis Simpanan</Label
                                >
                                <select
                                    v-model="form.jenis_simpanan"
                                    id="jenis_simpanan"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="">Semua jenis</option>
                                    <option value="wajib">Wajib</option>
                                    <option value="pokok">Pokok</option>
                                    <option value="sukarela">Sukarela</option>
                                </select>
                                <InputError
                                    :message="form.errors.jenis_simpanan"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="nominal">Nominal</Label>
                                <InputRupiah v-model="form.nominal" />
                                <InputError :message="form.errors.nominal" />
                            </div>
                        </div>

                        <!-- ROW 3 -->
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-2"
                        >
                            <div class="flex flex-col space-y-1.5">
                                <Label for="bukti_setor">Bukti Setor</Label>
                                <Input
                                    id="bukti_setor"
                                    v-model="form.bukti_setor"
                                    maxlength="50"
                                />
                                <InputError
                                    :message="form.errors.bukti_setor"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="tanggal_setor">Tanggal Setor</Label>
                                <Input
                                    id="tanggal_setor"
                                    v-model="form.tanggal_setor"
                                    type="date"
                                />
                                <InputError
                                    :message="form.errors.tanggal_setor"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row gap-4 p-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="() => router.visit('/simpanan')"
                    >
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan</span>
                    </Button>
                </div>
            </form>
        </div>

        <!-- Modal Cari Anggota -->
        <Dialog v-model:open="modalCariAnggota">
            <DialogContent
                class="h-[90vh] overflow-auto p-6 sm:max-w-4xl"
                @interact-outside.prevent
            >
                <DialogHeader>
                    <DialogTitle>Pilih Anggota</DialogTitle>
                    <DialogDescription>
                        Pilih anggota dari daftar di bawah.
                    </DialogDescription>
                </DialogHeader>
                <DataTableCustom
                    :endpoint="'/ajax/anggota'"
                    :columns="[
                        { label: 'Anggota', field: 'nama_anggota' },
                        { label: 'NIK', field: 'nik' },
                        { label: 'Kontak', field: 'nomor_hp' },
                        { label: 'Alamat', field: 'alamat' },
                    ]"
                    @selected="handleSelectedAnggota"
                />
                <DialogFooter>
                    <Button @click="modalCariAnggota = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
