<script setup>
import { Award, CalendarDays, FileBarChart, PackageCheck, Printer, Receipt, TrendingUp } from 'lucide-vue-next'
import { nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const filter = reactive({
    type: 'daily',
    date: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10),
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

function printReport() {
    window.print()
}

function formatDate(isoString) {
    if (!isoString) return '-'
    const d = new Date(isoString)
    return d.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatCurrency(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID')
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 no-print">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Laporan Penjualan</h1>
                <p class="text-sm text-slate-500">Rekap harian, bulanan, dan tahunan transaksi toko obat.</p>
            </div>
            <button v-if="report" class="btn-muted flex items-center gap-2" @click="printReport">
                <Printer class="h-4 w-4 text-slate-600" />
                Cetak Laporan
            </button>
        </div>

        <!-- Filter Card -->
        <section class="mb-5 rounded-md border border-slate-200 bg-white p-4 no-print">
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

        <!-- Loading State -->
        <section v-if="loading" class="rounded-md border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
            Memuat laporan penjualan...
        </section>

        <!-- Error State -->
        <section v-else-if="error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-8 text-center text-sm text-rosewood">
            {{ error }}
        </section>

        <!-- Report Content -->
        <section v-else-if="report" class="printable-area rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <!-- Header Print / View -->
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-slate-50/50">
                <div class="flex items-center gap-2 font-semibold text-slate-800 text-lg">
                    <FileBarChart class="h-5 w-5 text-emerald-600" />
                    {{ report.title }}
                </div>
                <div class="text-xs text-slate-500 print-only">
                    Dicetak pada: {{ formatDate(new Date().toISOString()) }}
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid gap-4 p-6 md:grid-cols-3 border-b border-slate-200">
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-emerald-700">Total Penjualan</p>
                        <TrendingUp class="h-4 w-4 text-emerald-600" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ formatCurrency(report.total_penjualan) }}</p>
                </div>
                <div class="rounded-lg border border-sky-100 bg-sky-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-sky-700">Total Transaksi</p>
                        <Receipt class="h-4 w-4 text-sky-600" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ report.total_transaksi }} <span class="text-sm font-normal text-slate-500">transaksi</span></p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-amber-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-amber-700">Total Item Terjual</p>
                        <PackageCheck class="h-4 w-4 text-amber-600" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ report.total_item_terjual }} <span class="text-sm font-normal text-slate-500">unit</span></p>
                </div>
            </div>

            <!-- Top 5 Medicines Section -->
            <div v-if="report.top_medicines && report.top_medicines.length" class="p-6 border-b border-slate-200">
                <div class="flex items-center gap-2 mb-3">
                    <Award class="h-4 w-4 text-amber-500" />
                    <h2 class="font-semibold text-slate-800 text-sm">Obat Terlaris (Top {{ report.top_medicines.length }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-y border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 w-12 text-center">Rank</th>
                                <th class="px-4 py-2.5">Kode Obat</th>
                                <th class="px-4 py-2.5">Nama Obat</th>
                                <th class="px-4 py-2.5 text-right">Terjual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(med, idx) in report.top_medicines" :key="med.id" class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 text-center font-bold text-xs">
                                    <span :class="[
                                        'inline-flex items-center justify-center h-6 w-6 rounded-full text-xs font-semibold',
                                        idx === 0 ? 'bg-amber-100 text-amber-800' :
                                        idx === 1 ? 'bg-slate-200 text-slate-700' :
                                        idx === 2 ? 'bg-amber-700/10 text-amber-900' : 'bg-slate-100 text-slate-600'
                                    ]">
                                        {{ idx + 1 }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ med.kode_obat }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ med.nama_obat }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-slate-700">{{ med.qty }} unit</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detailed Sales Transactions Table -->
            <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                    <Receipt class="h-4 w-4 text-slate-500" />
                    <h2 class="font-semibold text-slate-800 text-sm">Rincian Transaksi Penjualan</h2>
                </div>

                <div v-if="!report.sales || !report.sales.length" class="py-8 text-center text-sm text-slate-400">
                    Tidak ada data transaksi penjualan pada periode ini.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-y border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 w-12 text-center">No.</th>
                                <th class="px-4 py-2.5">No. Transaksi</th>
                                <th class="px-4 py-2.5">Tanggal / Waktu</th>
                                <th class="px-4 py-2.5">Kasir</th>
                                <th class="px-4 py-2.5 text-center">Qty Item</th>
                                <th class="px-4 py-2.5 text-right">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(sale, index) in report.sales" :key="sale.id" class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 text-center text-xs text-slate-400">{{ index + 1 }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs font-medium text-slate-800">{{ sale.nomor_transaksi }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ formatDate(sale.tanggal) }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-600">{{ sale.kasir }}</td>
                                <td class="px-4 py-2.5 text-center text-xs font-medium text-slate-700">{{ sale.jumlah_item }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-slate-800">{{ formatCurrency(sale.total_harga) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-semibold border-t-2 border-slate-200 text-slate-800">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right">Total Akhir:</td>
                                <td class="px-4 py-3 text-center text-xs">{{ report.total_item_terjual }} item</td>
                                <td class="px-4 py-3 text-right text-emerald-700">{{ formatCurrency(report.total_penjualan) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.print-only {
    display: none;
}

@media print {
    :deep(aside),
    :deep(header),
    :deep(nav),
    .no-print {
        display: none !important;
    }

    .printable-area {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .print-only {
        display: block !important;
    }
}
</style>

