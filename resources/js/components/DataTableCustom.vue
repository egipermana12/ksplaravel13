<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

// 1. Definisikan Interface untuk Type Safety
interface Column {
    label: string;
    field: string;
}

interface Meta {
    current_page?: number;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    last_page?: number;
    total?: number;
    // Tambahkan property lain sesuai response API Anda
}

// 2. Definisi Props dengan TypeScript
const props = defineProps<{
    columns: Column[];
    endpoint: string;
}>();

// 3. Definisi Emits
const emit = defineEmits<{
    (e: 'selected', row: any): void;
}>();

// 4. State dengan Type Inference
const rows = ref<any[]>([]);
const meta = ref<Meta>({});
const search = ref<string>('');
const page = ref<number>(1);
const sort = ref<string>('id');
const direction = ref<'asc' | 'desc'>('desc'); // Literal type
const loading = ref<boolean>(false);

async function loadData(): Promise<void> {
    loading.value = true;

    try {
        const url =
            `${props.endpoint}?page=${page.value}` +
            `&search=${search.value}` +
            `&sort=${sort.value}` +
            `&direction=${direction.value}`;
        const res = await fetch(url);
        const json = await res.json();

        // Asumsi struktur response standar Laravel/Pagination
        rows.value = json.data || [];
        meta.value = json;
    } catch (error) {
        console.error('Failed to load data:', error);
    } finally {
        loading.value = false;
    }
}

function toggleSort(field: string): void {
    if (sort.value === field) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        direction.value = 'asc';
    }

    loadData();
}

watch(search, () => {
    page.value = 1;
    loadData();
});

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="rounded-xl bg-white p-4 shadow">
        <input
            v-model="search"
            placeholder="Cari..."
            class="mb-4 rounded-md border px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/20"
        />
        <!-- Wrapper Scroll -->
        <div class="rounded-md border">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-100 text-left">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.field"
                            @click="toggleSort(col.field)"
                            class="cursor-pointer px-4 py-3 font-medium select-none"
                        >
                            <div class="flex items-center gap-1">
                                {{ col.label }}
                                <span
                                    v-if="sort === col.field"
                                    class="text-[10px]"
                                >
                                    {{ direction === 'asc' ? '▲' : '▼' }}
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="relative">
                    <tr v-if="loading">
                        <td
                            :colspan="columns.length"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Memuat data...
                        </td>
                    </tr>
                    <tr v-else-if="rows.length === 0">
                        <td
                            :colspan="columns.length"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="cursor-pointer border-b transition-colors last:border-0 hover:bg-muted/40"
                        @click="emit('selected', row)"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.field"
                            class="px-4 py-3 text-xs"
                        >
                            {{ row[col.field] }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-end gap-2">
            <span class="mr-2 text-xs text-muted-foreground">
                Halaman {{ page }}
            </span>
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8 cursor-pointer"
                @click="
                    page--;
                    loadData();
                "
                :disabled="page <= 1 || loading"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>

            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8 cursor-pointer"
                @click="
                    page++;
                    loadData();
                "
                :disabled="!meta.next_page_url || loading"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
