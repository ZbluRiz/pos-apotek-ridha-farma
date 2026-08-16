<script setup>
import { CalendarDays, FileBarChart } from 'lucide-vue-next'
import { nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const filter = reactive({
    type: 'daily',
    date: new Date().toISOString().slice(0, 10),
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
})
const report = ref(null)
const loading = ref(true)
const error = ref('')

async function load() {
    const params = filter.type === 'daily'
        ? { date: filter.date }
        : filter.type === 'monthly'
            ? { month: filter.month, year: filter.year }
            : { year: filter.year }

    loading.value = true
    error.value = ''

    try {
        const { data } = await http.get(`/reports/${filter.type}`, { params })
        report.value = data.data
        await nextTick()
    } catch (exception) {
        report.value = null
        error.value = exception.response?.data?.message || 'Laporan gagal dimuat.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Laporan Penjualan</h1>
            <p class="text-sm text-slate-500">Rekap harian, bulanan, dan tahunan.</p>
        </div>

        <section class="mb-5 rounded-md border border-slate-200 bg-white p-4">
            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 items-end">
                <div>
                    <label class="label" for="report_type">Jenis Laporan</label>
                    <select id="report_type" v-model="filter.type" class="input">
                        <option value="daily">Harian</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div v-if="filter.type === 'daily'">
                    <label class="label" for="report_date">Pilih Tanggal</label>
                    <input id="report_date" v-model="filter.date" class="input" type="date" />
                </div>
                <div v-if="filter.type !== 'daily'">
                    <label class="label" for="report_month">Bulan (1-12)</label>
                    <input id="report_month" v-model.number="filter.month" class="input" max="12" min="1" type="number" />
                </div>
                <div>
                    <label class="label" for="report_year">Tahun</label>
                    <input id="report_year" v-model.number="filter.year" class="input" type="number" />
                </div>
                <div>
                    <button class="btn-primary w-full" @click="load">
                        <CalendarDays class="h-4 w-4" />
                        Tampilkan
                    </button>
                </div>
            </div>
        </section>

        <section v-if="loading" class="rounded-md border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
            Memuat laporan penjualan...
        </section>

        <section v-else-if="error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-8 text-center text-sm text-rosewood">
            {{ error }}
        </section>

        <section v-else-if="report" class="rounded-md border border-slate-200 bg-white">
            <div class="flex items-center gap-2 border-b border-slate-200 px-4 py-3 font-semibold">
                <FileBarChart class="h-4 w-4 text-river" />
                {{ report.title }}
            </div>
            <div class="grid gap-4 p-4 md:grid-cols-3">
                <div class="rounded-md bg-slate-100 p-3">
                    <p class="text-xs text-slate-500">Transaksi</p>
                    <p class="text-xl font-semibold">{{ report.total_transaksi }}</p>
                </div>
                <div class="rounded-md bg-slate-100 p-3">
                    <p class="text-xs text-slate-500">Total Penjualan</p>
                    <p class="text-xl font-semibold">Rp {{ Number(report.total_penjualan).toLocaleString('id-ID') }}</p>
                </div>
                <div class="rounded-md bg-slate-100 p-3">
                    <p class="text-xs text-slate-500">Item Terjual</p>
                    <p class="text-xl font-semibold">{{ report.total_item_terjual }}</p>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
