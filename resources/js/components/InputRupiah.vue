<script setup lang="ts">
import { ref, watch, type HTMLAttributes } from 'vue';
import { cn } from "@/lib/utils"; // Import utility cn untuk handling class merge bawaan shadcn

// 1. Definisikan props untuk menampung parameter disabled dan class tambahan
const props = defineProps<{
    disabled?: boolean;
    class?: HTMLAttributes["class"];
}>();

// Gunakan number agar bisa menyimpan nilai desimal (float)
const model = defineModel<number | string>({ default: 0 });

const display = ref<string>('');

/**
 * Format ke Rupiah dengan dukungan desimal
 */
const formatRupiah = (value: string | number): string => {
    if (value === null || value === undefined || value === '') return '';

    // Ubah ke string dan ganti titik (internal) ke koma (tampilan)
    let str = value.toString().replace('.', ',');

    // Pisahkan bagian integer dan desimal
    const parts = str.split(',');
    let integer = parts[0].replace(/\D/g, '');
    let decimal = parts[1];

    // Format ribuan
    integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Kembalikan format dengan koma jika ada desimal
    if (decimal !== undefined) {
        return `${integer},${decimal.slice(0, 2)}`; // Batasi 2 angka desimal
    }

    return integer;
};

/**
 * Konversi string tampilan (1.200,50) ke angka (1200.50)
 */
const parseRupiah = (value: string): number => {
    if (!value) return 0;
    // Hapus titik ribuan, ganti koma desimal menjadi titik standar programming
    const clean = value.replace(/\./g, '').replace(',', '.'); // Koma jadi titik
    return parseFloat(clean); // Hasilnya: 500.74 (Tipe Data Number)
};

const onInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    let value = target.value;

    // Hanya izinkan angka dan satu koma
    value = value.replace(/[^0-9,]/g, '');
    const commaCount = (value.match(/,/g) || []).length;
    if (commaCount > 1) {
        value = value.lastIndexOf(',')
            ? value.slice(0, value.lastIndexOf(','))
            : value;
    }

    display.value = formatRupiah(value);
    model.value = parseRupiah(display.value);

    // Paksa update nilai input agar karakter ilegal langsung hilang
    target.value = display.value;
};

watch(
    () => model.value,
    (val) => {
        const formatted = formatRupiah(val);
        if (formatted !== display.value) {
            display.value = formatted;
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="relative w-full">
        <!-- Tambahkan kondisi opasitas text "Rp" saat input dalam posisi disabled -->
        <span
            :class="cn(
                'absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground select-none pointer-events-none',
                props.disabled && 'opacity-50'
            )"
        >
            Rp
        </span>
        <input
            :value="display"
            @input="onInput"
            :disabled="props.disabled"
            type="text"
            inputmode="decimal"
            :class="cn(
                'w-full h-9 rounded-md border border-input bg-background py-2 pr-3 pl-10 text-sm ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-gray-50',
                props.class
            )"
            placeholder="0,00"
        />
    </div>
</template>
