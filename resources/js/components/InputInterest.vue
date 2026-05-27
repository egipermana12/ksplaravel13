<script setup lang="ts">
import { ref, watch, type HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  modelValue: string | number | null
  placeholder?: string
  disabled?: boolean
  class?: HTMLAttributes["class"]
}>()

const emits = defineEmits<{
  (e: "update:modelValue", payload: number | null): void
}>()

// State internal untuk menampilkan teks di input
const displayValue = ref<string>("")

// Sinkronisasi dari Parent (Form) ke Input Internal
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue === null || newValue === undefined || newValue === "") {
      displayValue.value = ""
      return
    }

    // Pastikan nilai dari luar dikonversi ke string desimal yang rapi
    const num = Number(newValue)
    if (!isNaN(num)) {
      // Jika fokus tidak aktif, kita bisa batasi ke 2 desimal
      displayValue.value = num.toString()
    }
  },
  { immediate: true }
)

// Menangani ketikan user & memvalidasi format desimal (5,2)
const handleInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value

  // 1. Ganti koma (,) menjadi titik (.) untuk standarisasi desimal
  value = value.replace(",", ".")

  // 2. Hanya izinkan angka dan satu buah titik desimal
  value = value.replace(/[^0-9.]/g, "")
  const parts = value.split(".")
  if (parts.length > 2) {
    value = parts[0] + "." + parts.slice(1).join("")
  }

  // 3. Validasi aturan Desimal (5,2) -> Maks 3 digit di depan koma, maks 2 digit di belakang
  if (parts[0] && parts[0].length > 3) {
    parts[0] = parts[0].substring(0, 3) // Potong jika lebih dari 999
  }
  if (parts[1] && parts[1].length > 2) {
    parts[1] = parts[1].substring(0, 2) // Potong jika lebih dari 2 desimal (.99)
  }

  // Gabungkan kembali jika ada titik desimal
  if (parts.length === 2) {
    value = parts[0] + "." + parts[1]
  } else {
    value = parts[0]
  }

  // Update tampilan input internal
  displayValue.value = value
  input.value = value

  // 4. Kirim data ke parent (v-model) dalam bentuk Number/Float atau null jika kosong
  if (value === "" || value === ".") {
    emits("update:modelValue", null)
  } else {
    emits("update:modelValue", parseFloat(value))
  }
}

// Format pelengkap saat user selesai mengetik (blur)
const handleBlur = () => {
  if (displayValue.value !== "") {
    const num = parseFloat(displayValue.value)
    if (!isNaN(num)) {
      // Mengubah nilai menjadi format 2 desimal tetap saat blur (misal: 5 -> 5.00 atau 5.5 -> 5.50)
      displayValue.value = num.toFixed(2)
      emits("update:modelValue", num)
    }
  }
}
</script>

<template>
  <div class="relative flex items-center w-full min-w-0">
    <input
      type="text"
      inputmode="decimal"
      :value="displayValue"
      @input="handleInput"
      @blur="handleBlur"
      :disabled="disabled"
      :placeholder="placeholder || '0.00'"
      data-slot="input-interest"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent pl-3 pr-8 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        props.class,
      )"
    />
    <!-- Icon Persentase permanen di kanan luar input agar konsisten -->
    <span class="absolute right-3 text-sm font-medium text-muted-foreground pointer-events-none select-none">
      %
    </span>
  </div>
</template>
