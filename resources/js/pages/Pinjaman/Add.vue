<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import DataTableCustom from '@/components/DataTableCustom.vue';
import InputError from '@/components/InputError.vue';
import InputInterest from '@/components/InputInterest.vue';
import InputRupiah from '@/components/InputRupiah.vue';
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

const randomTime = () => {
    const time = Math.floor(Date.now() / 1000);

    return time;
};

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
    jumlah_pinjaman: number;
    total_kewajiban: number;
    tenor: number;
    bunga: number;
    status_pinjaman: string;
    tanggal_disetujui: string;
    tanggal_pengajuan: string;
    no_kontrak: string;
    jenis_bunga: string;
    akunPiutang: number;
    akunKas: number;
}>({
    id_anggota: '',
    nik: '',
    nama_anggota: '',
    jumlah_pinjaman: 0,
    total_kewajiban: 0,
    tenor: 1,
    bunga: 0,
    status_pinjaman: 'pending',
    tanggal_disetujui: '',
    tanggal_pengajuan: new Date().toISOString().split('T')[0],
    no_kontrak: 'PJ-' + randomTime(),
    jenis_bunga: 'flat',
    akunPiutang: props.akunPiutang.id,
    akunKas: props.akunKas.id,
});

// 1. Fungsi Hitung yang Diperbaiki
const hitungTotalKewajiban = () => {
    // Validasi: Jangan hitung jika salah satu nilai masih kosong atau 0
    if (!form.jumlah_pinjaman || !form.tenor || !form.bunga) {
        form.total_kewajiban = 0;

        return;
    }

    // Pastikan input adalah angka untuk menghindari error kalkulasi
    const pokok = parseFloat(form.jumlah_pinjaman.toString());
    const tenor = parseInt(form.tenor.toString());
    const bungaPersen = parseFloat(form.bunga.toString());

    // Hitung Bunga (Metode Flat)
    const bungaRupiahPerBulan = (bungaPersen / 100) * pokok;
    const totalBunga = bungaRupiahPerBulan * tenor;

    // Total Kewajiban = Pokok + Total Bunga
    const total = pokok + totalBunga;

    form.total_kewajiban = Math.round(total); // Gunakan round untuk menghindari desimal koma yang panjang
};

// 2. Gunakan Watcher untuk otomatisasi
// Kita mengawasi perubahan pada 3 field ini
watch([() => form.jumlah_pinjaman, () => form.tenor, () => form.bunga], () => {
    hitungTotalKewajiban();
});

// 3. Tambahan: Logika otomatis isi tanggal disetujui jika status diubah ke approved
watch(
    () => form.status_pinjaman,
    (newStatus) => {
        if (newStatus === 'approved') {
            form.tanggal_disetujui = new Date().toISOString().split('T')[0];
        } else {
            form.tanggal_disetujui = '';
        }
    },
);

const modalCariAnggota = ref(false);

type SelectedAnggota = {
    id: string;
    nik: string;
    nama_anggota: string;
};

function handleSelectedAnggota(anggota: SelectedAnggota) {
    form.id_anggota = anggota.id;
    form.nik = anggota.nik;
    form.nama_anggota = anggota.nama_anggota;
    modalCariAnggota.value = false;
}

const submit = () => {
    form.post('/pinjaman', {
        onSuccess: () => {
            toast.success('Pinjaman berhasil disimpan.');
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Gagal menyimpan. Periksa kembali inputan Anda.',
            );
        },
    });
};
</script>
<template>
    <Head title="Tambah Pinjaman" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tambah Pinjaman
                </h1>
                <p class="text-sm text-muted-foreground">
                    Tambah data pinjaman anggota koperasi
                </p>
            </div>
            <div>
                <Button
                    @click="() => router.visit('/pinjaman')"
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
                <div class="grid w-full gap-6 p-4">
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
                                Informasi Debitur / Anggota
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

                    <!-- SECTION 2: DETAIL TRANSAKSI & PINJAMAN (Form Utama) -->
                    <div class="space-y-5">
                        <h3 class="px-1 text-sm font-semibold text-slate-700">
                            Detail Pengajuan Pinjaman
                        </h3>

                        <!-- ROW 1: Detail Pengajuan (Tanggal, Kontrak, Status) -->
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-3"
                        >
                            <div class="flex flex-col space-y-1.5">
                                <Label for="tanggal_pengajuan"
                                    >Tanggal Pengajuan</Label
                                >
                                <Input
                                    id="tanggal_pengajuan"
                                    v-model="form.tanggal_pengajuan"
                                    type="date"
                                />
                                <InputError
                                    :message="form.errors.tanggal_pengajuan"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="status_pinjaman"
                                    >Status Pinjaman</Label
                                >
                                <select
                                    v-model="form.status_pinjaman"
                                    id="status_pinjaman"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm capitalize shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                <InputError
                                    :message="form.errors.status_pinjaman"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="no_kontrak"
                                    >No. Kontrak (auto generate)</Label
                                >
                                <Input
                                    disabled
                                    id="no_kontrak"
                                    v-model="form.no_kontrak"
                                    placeholder="Contoh: KTR-2026-001"
                                />
                                <InputError :message="form.errors.no_kontrak" />
                            </div>
                        </div>

                        <!-- ROW 2: Struktur Finansial (Pinjaman, Tenor, Total) -->
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-3"
                        >
                            <div class="flex flex-col space-y-1.5">
                                <Label for="jumlah_pinjaman"
                                    >Jumlah Pinjaman</Label
                                >
                                <InputRupiah
                                    v-model="form.jumlah_pinjaman"
                                    id="jumlah_pinjaman"
                                />
                                <InputError
                                    :message="form.errors.jumlah_pinjaman"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="tenor">Tenor (Jangka Waktu)</Label>
                                <select
                                    v-model="form.tenor"
                                    id="tenor"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option :value="1">1 Bulan</option>
                                    <option :value="3">3 Bulan</option>
                                    <option :value="6">6 Bulan</option>
                                    <option :value="9">9 Bulan</option>
                                    <option :value="12">12 Bulan</option>
                                </select>
                                <InputError :message="form.errors.tenor" />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="bunga">Suku Bunga</Label>
                                <InputInterest
                                    v-model="form.bunga"
                                    id="bunga"
                                />
                                <InputError :message="form.errors.bunga" />
                            </div>
                        </div>

                        <!-- ROW 3: Aturan Bunga & Administrasi -->
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-3"
                        >
                            <div class="flex flex-col space-y-1.5">
                                <Label for="jenis_bunga">Jenis Bunga</Label>
                                <select
                                    v-model="form.jenis_bunga"
                                    id="jenis_bunga"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="" disabled selected>
                                        Pilih Jenis Bunga
                                    </option>
                                    <option value="flat">Flat / Tetap</option>
                                </select>
                                <InputError
                                    :message="form.errors.jenis_bunga"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="total_kewajiban"
                                    >Total Kewajiban</Label
                                >
                                <InputRupiah
                                    v-model="form.total_kewajiban"
                                    id="total_kewajiban"
                                    disabled
                                />
                                <InputError
                                    :message="form.errors.total_kewajiban"
                                />
                            </div>

                            <div class="flex flex-col space-y-1.5">
                                <Label for="tanggal_disetujui"
                                    >Tanggal Disetujui</Label
                                >
                                <Input
                                    id="tanggal_disetujui"
                                    v-model="form.tanggal_disetujui"
                                    type="date"
                                    :disabled="
                                        form.status_pinjaman !== 'approved'
                                    "
                                    :class="{
                                        'cursor-not-allowed bg-gray-100 opacity-60':
                                            form.status_pinjaman !== 'approved',
                                    }"
                                />
                                <InputError
                                    :message="form.errors.tanggal_disetujui"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row gap-4 p-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="() => router.visit('/pinjaman')"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </Button>
                    </div>
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
