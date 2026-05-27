project laravel 13 saya menggunakan inertia 3 vue dan fortify, saya ingin kamu buatkan hal hal berikut :
 1. buat migration table anggota dengan field berikut :
    - id auto increment primary key
    - nik varchar max 16 unique
    - nama_anggota varchar max 50 not null
    - tanggal_lahir date not null
    - jenis_kelamin enum(L,P) dengan komentar Laki laki, Perempuan
    - alamat varchar max 100
    - nomor_hp varchar max 13
    - tanggal_gabung date not null default now
    - status_anggota enum (aktif, nonaktif)
    - path_image varchar max 255
2. buat model anggota
3. ini adalah problem utama diaplikasi yang akan saya bangun dimana data anggota dibutuhkan pada list anggota di modul anggota, kemudian dibutuhkan pada cari anggota di modul pinjaman dan simpanan, apakah membutuhkan dua controller dimana pada modul anggota di handle langsung oleh inertia sedangkan di modul pinjaman dihandle oleh API, mana yang lebih kamu rekomendasikan ?
ini adalah rancangan hasil diskusi dengan model AI :
app/
 └── Http/
      ├── Controllers/
      │      ├── AnggotaController.php
      │      ├── Api/
      │      │     └── AnggotaApiController.php

resources/js/
 ├── Components/
 │    └── datatable/
 │         ├── DataTable.vue
 │         ├── TablePagination.vue
 │         ├── TableSearch.vue
 │         └── BulkActions.vue
 │
 ├── Pages/
 │    ├── Anggota/Index.vue
 │    ├── Pinjaman/Index.vue
 │    └── Simpanan/Index.vue


 Anggota Api
 ```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Anggota::query();

        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('kode', 'like', "%{$request->search}%");
        }

        if ($request->sort) {
            $query->orderBy(
                $request->sort,
                $request->direction ?? 'asc'
            );
        }

        return $query->paginate(10);
    }
}
```

Generic DataTable.vue
```vue
<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
    endpoint: String,
    title: String,
    columns: Array
})

const rows = ref([])
const meta = ref({})
const search = ref('')
const page = ref(1)
const sort = ref('id')
const direction = ref('desc')
const checked = ref([])

async function loadData()
{
    let url =
        `${props.endpoint}?page=${page.value}` +
        `&search=${search.value}` +
        `&sort=${sort.value}` +
        `&direction=${direction.value}`

    const res = await fetch(url)
    const json = await res.json()

    rows.value = json.data
    meta.value = json
}

function toggleSort(field)
{
    if (sort.value === field) {
        direction.value =
            direction.value === 'asc'
            ? 'desc'
            : 'asc'
    } else {
        sort.value = field
        direction.value = 'asc'
    }

    loadData()
}

watch(search, () => {
    page.value = 1
    loadData()
})

onMounted(loadData)
</script>

<template>
<div class="p-6 bg-white rounded-xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">
            {{ title }}
        </h2>

        <input
            v-model="search"
            placeholder="Cari..."
            class="border px-3 py-2 rounded"
        >
    </div>

    <table class="w-full border">
        <thead>
            <tr>
                <th>
                    <input
                        type="checkbox"
                        @change="checked =
                          $event.target.checked
                          ? rows.map(x => x.id)
                          : []"
                    >
                </th>

                <th
                    v-for="col in columns"
                    :key="col.field"
                    @click="toggleSort(col.field)"
                    class="cursor-pointer"
                >
                    {{ col.label }}
                </th>
            </tr>
        </thead>

        <tbody>
            <tr
                v-for="row in rows"
                :key="row.id"
            >
                <td>
                    <input
                        type="checkbox"
                        :value="row.id"
                        v-model="checked"
                    >
                </td>

                <td
                    v-for="col in columns"
                    :key="col.field"
                >
                    {{ row[col.field] }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4 flex gap-2">

        <button
            @click="page--; loadData()"
            :disabled="page <= 1"
        >
            Prev
        </button>

        <button
            @click="page++; loadData()"
            :disabled="!meta.next_page_url"
        >
            Next
        </button>

    </div>

</div>
</template>
```

Pakai di Halaman Anggota
```vue
<script setup>
import DataTable from '@/Components/datatable/DataTable.vue'

const columns = [
    { label: 'Kode', field: 'kode' },
    { label: 'Nama', field: 'nama' },
    { label: 'NIK', field: 'nik' }
]
</script>

<template>
<DataTable
    title="Data Anggota"
    endpoint="/api/anggota"
    :columns="columns"
/>
</template>
```

AnggotaPicker.vue
```vue
<script setup>
import { ref } from 'vue'
import DataTable from '@/Components/datatable/DataTable.vue'

const emit = defineEmits(['selected'])
const open = ref(false)

const columns = [
    { label: 'Kode', field: 'kode' },
    { label: 'Nama', field: 'nama' }
]

function pilih(row)
{
    emit('selected', row)
    open.value = false
}
</script>

<template>
<div>

    <button
        @click="open = true"
        class="bg-green-600 text-white px-4 py-2"
    >
        Cari
    </button>

    <div
        v-if="open"
        class="fixed inset-0 bg-black/40 flex items-center justify-center"
    >
        <div class="bg-white p-6 rounded-xl w-[900px]">

            <h2 class="text-xl font-bold mb-4">
                Pilih Anggota
            </h2>

            <DataTable
                endpoint="/api/anggota"
                title="Data Anggota"
                :columns="columns"
                selectable
                @row-click="pilih"
            />

            <button
                class="mt-4"
                @click="open = false"
            >
                Tutup
            </button>

        </div>
    </div>

</div>
</template>
```

Modifikasi DataTable.vue
```vue
const emit = defineEmits(['row-click'])
```

```vue
<tr
    v-for="row in rows"
    :key="row.id"
    @dblclick="emit('row-click', row)"
    class="cursor-pointer hover:bg-gray-100"
>
```

