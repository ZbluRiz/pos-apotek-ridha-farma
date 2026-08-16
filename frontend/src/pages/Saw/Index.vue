<script setup>
import { ActivitySquare, RefreshCw } from 'lucide-vue-next'
import { nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const filter = reactive({
    start_date: new Date(Date.now() - 30 * 86400000 - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10),
    end_date: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10),
})
const ranking = ref([])
const meta = ref(null)
const loading = ref(true)
const error = ref('')

async function load() {
    loading.value = true
    error.value = ''

    try {
        const { data } = await http.get('/saw/restock-ranking', { params: filter })
        ranking.value = Array.isArray(data.data) ? data.data : []
        meta.value = data.meta
        await nextTick()
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Ranking SAW gagal dimuat.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Ranking Restock SAW</h1>
            <p class="text-sm text-slate-500">Prioritas restock berdasarkan jumlah penjualan, stok, masa kedaluwarsa, prioritas owner, dan harga obat.</p>
        </div>

        <section class="mb-5 rounded-md border border-slate-200 bg-white p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[180px_180px_140px_1fr] items-end">
                <div>
                    <label class="label" for="saw_start_date">Tanggal Mulai</label>
                    <input id="saw_start_date" v-model="filter.start_date" class="input" type="date" />
                </div>
                <div>
                    <label class="label" for="saw_end_date">Tanggal Selesai</label>
                    <input id="saw_end_date" v-model="filter.end_date" class="input" type="date" />
                </div>
                <div>
                    <button class="btn-primary w-full" @click="load">
                        <RefreshCw class="h-4 w-4" />
                        Hitung
                    </button>
                </div>
                <div v-if="meta" class="text-xs text-slate-500 pb-2">
                    <span class="font-semibold text-slate-700 block mb-0.5">Kriteria Bobot:</span>
                    C1 Penjualan 30%, C2 Stok 25%, C3 Kedaluwarsa 20%, C4 Prioritas Owner 15%, C5 Harga 10%
                </div>
            </div>
        </section>

        <section class="overflow-x-auto rounded-md border border-slate-200 bg-white">
            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3 font-semibold">
                <ActivitySquare class="h-4 w-4 text-leaf" />
                Rekomendasi Restock
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Rank</th>
                        <th class="px-3 py-3">Obat</th>
                        <th class="px-3 py-3">Stok</th>
                        <th class="px-3 py-3">Expired</th>
                        <th class="px-3 py-3">Prioritas Owner</th>
                        <th class="px-3 py-3">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td class="px-3 py-8 text-center text-slate-500" colspan="6">Menghitung ranking SAW...</td>
                    </tr>
                    <tr v-else-if="error">
                        <td class="px-3 py-8 text-center text-rosewood" colspan="6">{{ error }}</td>
                    </tr>
                    <tr v-else-if="ranking.length === 0">
                        <td class="px-3 py-8 text-center text-slate-500" colspan="6">Belum ada data untuk dihitung.</td>
                    </tr>
                    <template v-else>
                        <tr v-for="item in ranking" :key="item.medicine_id">
                            <td class="px-3 py-3 font-semibold text-leaf">#{{ item.ranking }}</td>
                            <td class="px-3 py-3">
                                <p class="font-medium">{{ item.nama_obat }}</p>
                                <p class="text-xs text-slate-500">{{ item.kode_obat }}</p>
                            </td>
                            <td class="px-3 py-3">{{ item.stok }}</td>
                            <td class="px-3 py-3">
                                <p>{{ item.tanggal_expired }}</p>
                                <p class="text-xs text-slate-500">{{ item.sisa_hari_expired }} hari</p>
                            </td>
                            <td class="px-3 py-3 font-medium">{{ item.prioritas_owner }}/5</td>
                            <td class="px-3 py-3 font-semibold">{{ item.nilai_preferensi }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </section>
    </AppLayout>
</template>
