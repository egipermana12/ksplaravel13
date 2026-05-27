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

const form = useForm<{
    id_anggota: string;
    nik: string;
    nama_anggota: string;
    jenis_simpanan: string;
    nominal: string;
    bukti_setor: string;
    ket: string;
    tanggal_setor: string;
}>({
    id_anggota: '',
    nik: '',
    nama_anggota: '',
    jenis_simpanan: '',
    nominal: '',
    bukti_setor: '',
    ket: '',
    tanggal_setor: '',
});

const modalCariAnggota = ref(false);
//untuk selected anggota dari modal cari anggota
const emit = defineEmits(['selected']);

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
            const message = page.props.flash?.success || 'Simpanan berhasil disimpan!';
            toast.success(message);
            form.reset(); // Opsional: reset form setelah berhasil
        },
        onError: () => {
            if (errors.transaction) {
                toast.error(errors.transaction);
            } else {
                toast.error('Gagal menyimpan. Periksa kembali inputan Anda.');
            }
        },
    });
}
</script>

<template>
    <Head title="Tambah Simpanan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tambah Simpanan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Tambah data simpanan anggota koperasi
                </p>
            </div>
        </div>
        <div>
            <Button @click="() => router.visit('/simpanan')" variant="outline">
                <ChevronLeft class="me-2 h-4 w-4" />
                Kembali
            </Button>
        </div>
        <!-- form -->
        <div class="rounded-lg border bg-card text-card-foreground">
            <form @submit.prevent="submit">
                <div class="grid w-full gap-4 p-4">
                    <!-- ROW 1 -->
                    <div class="grid grid-cols-3 items-end gap-4">
                        <div class="flex w-full flex-col space-y-1.5">
                            <Label for="nik">NIK</Label>
                            <Input id="nik" v-model="form.nik" disabled class="bg-gray-200" />
                            <InputError :message="form.errors.nik" />
                        </div>

                        <div class="flex flex-col space-y-1.5">
                            <Label for="nama_anggota">Nama Anggota</Label>
                            <Input id="nama_anggota" v-model="form.nama_anggota" disabled class="bg-gray-200" />
                            <InputError :message="form.errors.nama_anggota" />
                        </div>

                        <div class="flex flex-col space-y-1.5">
                            <Button class="max-w-auto px-4 py-2" @click="modalCariAnggota = true" type="button">
                                <Search class="mr-2 h-4 w-4" />
                                Cari Anggota
                            </Button>
                            <span></span>
                        </div>
                    </div>

                    <!-- ROW 2 -->
                    <div class="grid grid-cols-3 items-end gap-4">
                        <div class="flex flex-col space-y-1.5">
                            <Label for="jenis_simpanan">Jenis Simpanan</Label>
                            <select v-model="form.jenis_simpanan" id="jenis_simpanan"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                                <option value="">Semua jenis</option>
                                <option value="wajib">Wajib</option>
                                <option value="pokok">Pokok</option>
                                <option value="sukarela">Sukarela</option>
                            </select>
                            <InputError :message="form.errors.jenis_simpanan" />
                        </div>

                        <div class="flex flex-col space-y-1.5">
                            <Label for="nominal">Nominal</Label>
                            <InputRupiah v-model="form.nominal" />
                            <InputError :message="form.errors.nominal" />
                        </div>

                        <div></div>
                    </div>
                    <!-- ROW 3 -->
                    <div class="grid grid-cols-3 items-end gap-4">
                        <div class="flex flex-col space-y-1.5">
                            <Label for="bukti_setor">Bukti Setor</Label>
                            <Input id="bukti_setor" v-model="form.bukti_setor" maxlength="50" />
                            <InputError :message="form.errors.bukti_setor" />
                        </div>

                        <div class="flex flex-col space-y-1.5">
                            <Label for="tanggal_setor">Tanggal Setor</Label>
                            <Input id="tanggal_setor" v-model="form.tanggal_setor" type="date" />
                            <InputError :message="form.errors.tanggal_setor" />
                        </div>

                        <div></div>
                    </div>
                </div>
                <Input id="id_anggota" type="hidden" v-model="form.id_anggota" placeholder="Masukkan ID anggota..." />

                <div class="flex flex-row gap-4 p-4">
                    <Button type="button" variant="outline" @click="() => router.visit('/simpanan')">
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
            <DialogContent class="h-[90vh] overflow-auto p-6 sm:max-w-4xl" @interact-outside.prevent>
                <DialogHeader>
                    <DialogTitle>Pilih Anggota</DialogTitle>
                    <DialogDescription>
                        Pilih anggota dari daftar di bawah.
                    </DialogDescription>
                </DialogHeader>
                <DataTableCustom :endpoint="'/ajax/anggota'" :columns="[
                    { label: 'Anggota', field: 'nama_anggota' },
                    { label: 'NIK', field: 'nik' },
                    { label: 'Kontak', field: 'nomor_hp' },
                    { label: 'Alamat', field: 'alamat' },
                ]" @selected="handleSelectedAnggota" />
                <DialogFooter>
                    <Button @click="modalCariAnggota = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
