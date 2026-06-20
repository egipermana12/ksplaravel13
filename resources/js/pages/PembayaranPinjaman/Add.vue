<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Loader2, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import DataTableCustom from '@/components/DataTableCustom.vue';
import InputError from '@/components/InputError.vue';
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
import { formatRupiah } from '@/lib/myutils';

type Akun = {
    id: number;
    kode_akun: string;
    nama_akun: string;
};

type JadwalCicilan = {
    id: number;
    id_pinjaman: number;
    no_kontrak: string | null;
    angsuran_ke: number;
    tgl_jatuh_tempo: string;
    angsuran_pokok: number;
    angsuran_bunga: number;
    total_tagihan: number;
    nik: string | null;
    nama_anggota: string | null;
};

const props = defineProps<{
    akunKas: Akun;
    akunPiutangPinjaman: Akun;
    akunPiutangBunga: Akun;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tambah Pembayaran Cicilan',
                href: '/pembayaran-pinjaman/create',
            },
        ],
    },
});

const form = useForm<{
    id_jadwal: string;
    tanggal_bayar: string;
    denda: number;
    bukti_bayar: string;
    metode_bayar: 'cash' | 'transfer' | 'potong_gaji';
    kd_akun_kas: number;
    kd_akun_piutang_pinjaman: number;
    kd_akun_piutang_bunga: number;
}>({
    id_jadwal: '',
    tanggal_bayar: new Date().toISOString().slice(0, 10),
    denda: 0,
    bukti_bayar: '',
    metode_bayar: 'cash',
    kd_akun_kas: props.akunKas.id,
    kd_akun_piutang_pinjaman: props.akunPiutangPinjaman.id,
    kd_akun_piutang_bunga: props.akunPiutangBunga.id,
});

const modalCariJadwal = ref(false);
const selectedJadwal = ref<JadwalCicilan | null>(null);

const totalBayar = computed(() => {
    if (!selectedJadwal.value) {
        return Number(form.denda || 0);
    }

    return selectedJadwal.value.total_tagihan + Number(form.denda || 0);
});

watch(
    () => form.id_jadwal,
    () => {
        form.clearErrors();
    },
);

function handleSelectedJadwal(jadwal: JadwalCicilan): void {
    selectedJadwal.value = jadwal;
    form.id_jadwal = String(jadwal.id);
    modalCariJadwal.value = false;
}

function submit(): void {
    form.post('/pembayaran-pinjaman', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pembayaran cicilan berhasil disimpan.');
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                    'Pembayaran gagal disimpan. Periksa kembali inputnya.',
            );
        },
    });
}
</script>

<template>
    <Head title="Tambah Pembayaran Cicilan" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tambah Pembayaran Cicilan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Input pembayaran cicilan dari jadwal pinjaman anggota.
                </p>
            </div>

            <Button
                variant="outline"
                @click="() => router.visit('/pembayaran-pinjaman')"
            >
                <ChevronLeft />
                Kembali
            </Button>
        </div>

        <form class="rounded-lg border bg-card p-4" @submit.prevent="submit">
            <div class="grid gap-5">
                <div class="grid gap-3">
                    <Label for="id_jadwal">Jadwal cicilan</Label>
                    <div
                        class="grid gap-3 rounded-lg border border-dashed bg-muted/20 p-4 md:grid-cols-[1fr_auto]"
                    >
                        <div>
                            <div class="text-sm font-medium">
                                {{
                                    selectedJadwal
                                        ? `${selectedJadwal.no_kontrak} - ${selectedJadwal.nama_anggota}`
                                        : 'Belum ada jadwal dipilih'
                                }}
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">
                                {{
                                    selectedJadwal
                                        ? `Angsuran ke-${selectedJadwal.angsuran_ke} | Jatuh tempo ${selectedJadwal.tgl_jatuh_tempo} | ${formatRupiah(selectedJadwal.total_tagihan)}`
                                        : 'Cari berdasarkan nama anggota, NIK, nomor kontrak, atau tanggal jatuh tempo.'
                                }}
                            </div>
                        </div>
                        <Button
                            type="button"
                            class="self-start"
                            @click="modalCariJadwal = true"
                        >
                            <Search />
                            Cari Jadwal
                        </Button>
                    </div>
                    <InputError :message="form.errors.id_jadwal" />
                </div>

                <div
                    class="grid gap-4 rounded-lg border border-dashed bg-muted/20 p-4 md:grid-cols-3"
                >
                    <div>
                        <div class="text-xs text-muted-foreground">Anggota</div>
                        <div class="mt-1 font-medium">
                            {{ selectedJadwal?.nama_anggota ?? '-' }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ selectedJadwal?.nik ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">Kontrak</div>
                        <div class="mt-1 font-medium">
                            {{ selectedJadwal?.no_kontrak ?? '-' }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Jatuh tempo
                            {{ selectedJadwal?.tgl_jatuh_tempo ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">
                            Angsuran
                        </div>
                        <div class="mt-1 font-medium">
                            Ke-{{ selectedJadwal?.angsuran_ke ?? '-' }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{
                                formatRupiah(selectedJadwal?.total_tagihan ?? 0)
                            }}
                        </div>
                    </div>
                </div>

                <div
                    class="grid gap-4 rounded-lg border bg-background p-4 md:grid-cols-3"
                >
                    <div>
                        <div class="text-xs text-muted-foreground">Kas</div>
                        <div class="mt-1 font-medium">
                            {{ akunKas.kode_akun }} - {{ akunKas.nama_akun }}
                        </div>
                        <InputError :message="form.errors.kd_akun_kas" />
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">
                            Piutang Pinjaman
                        </div>
                        <div class="mt-1 font-medium">
                            {{ akunPiutangPinjaman.kode_akun }} -
                            {{ akunPiutangPinjaman.nama_akun }}
                        </div>
                        <InputError
                            :message="form.errors.kd_akun_piutang_pinjaman"
                        />
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">
                            Piutang Bunga
                        </div>
                        <div class="mt-1 font-medium">
                            {{ akunPiutangBunga.kode_akun }} -
                            {{ akunPiutangBunga.nama_akun }}
                        </div>
                        <InputError
                            :message="form.errors.kd_akun_piutang_bunga"
                        />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="tanggal_bayar">Tanggal bayar</Label>
                        <Input
                            id="tanggal_bayar"
                            v-model="form.tanggal_bayar"
                            type="date"
                            required
                        />
                        <InputError :message="form.errors.tanggal_bayar" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="metode_bayar">Metode bayar</Label>
                        <select
                            id="metode_bayar"
                            v-model="form.metode_bayar"
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            required
                        >
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="potong_gaji">Potong gaji</option>
                        </select>
                        <InputError :message="form.errors.metode_bayar" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="bukti_bayar">Bukti bayar</Label>
                        <Input
                            id="bukti_bayar"
                            v-model="form.bukti_bayar"
                            placeholder="Nomor bukti/ref transfer"
                        />
                        <InputError :message="form.errors.bukti_bayar" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-muted-foreground">Pokok</div>
                        <div class="mt-1 font-semibold">
                            {{
                                formatRupiah(
                                    selectedJadwal?.angsuran_pokok ?? 0,
                                )
                            }}
                        </div>
                    </div>
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-muted-foreground">Bunga</div>
                        <div class="mt-1 font-semibold">
                            {{
                                formatRupiah(
                                    selectedJadwal?.angsuran_bunga ?? 0,
                                )
                            }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="denda">Denda</Label>
                        <Input
                            id="denda"
                            v-model="form.denda"
                            type="number"
                            min="0"
                            step="100"
                        />
                        <InputError :message="form.errors.denda" />
                    </div>
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-muted-foreground">
                            Total bayar
                        </div>
                        <div class="mt-1 text-lg font-semibold">
                            {{ formatRupiah(totalBayar) }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        @click="() => router.visit('/pembayaran-pinjaman')"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.id_jadwal"
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

        <Dialog v-model:open="modalCariJadwal">
            <DialogContent
                class="h-[90vh] overflow-auto p-6 sm:max-w-5xl"
                @interact-outside.prevent
            >
                <DialogHeader>
                    <DialogTitle>Pilih Jadwal Cicilan</DialogTitle>
                    <DialogDescription>
                        Pilih jadwal pinjaman yang belum lunas.
                    </DialogDescription>
                </DialogHeader>

                <DataTableCustom
                    endpoint="/ajax/jadwal-pinjaman"
                    :columns="[
                        { label: 'Kontrak', field: 'no_kontrak' },
                        { label: 'Anggota', field: 'nama_anggota' },
                        { label: 'NIK', field: 'nik' },
                        { label: 'Angsuran', field: 'angsuran_ke' },
                        { label: 'Jatuh Tempo', field: 'tgl_jatuh_tempo' },
                        { label: 'Tagihan', field: 'total_tagihan_label' },
                    ]"
                    @selected="handleSelectedJadwal"
                />

                <DialogFooter>
                    <Button @click="modalCariJadwal = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
