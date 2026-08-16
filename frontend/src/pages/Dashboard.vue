<script setup>
import {
    AlertTriangle,
    BarChart3,
    Bell,
    CalendarDays,
    CheckCircle2,
    FileText,
    Info,
    PackagePlus,
    Pill,
    ReceiptText,
    ShoppingCart,
    Users,
    WalletCards,
} from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import http from '../api/http'
import StatCard from '../components/StatCard.vue'
import AppLayout from '../layouts/AppLayout.vue'

const dashboard = ref(null)
const loading = ref(true)
const error = ref('')

const stats = computed(() => dashboard.value?.stats || {})
const trend = computed(() => dashboard.value?.sales_trend || [])
const maxTrend = computed(() => Math.max(...trend.value.map((item) => Number(item.total)), 1))

const quickActions = [
    { label: 'Tambah Obat', helper: 'Tambah data obat baru', to: '/obat', icon: PackagePlus, tone: 'emerald' },
    { label: 'Input Penjualan', helper: 'Catat penjualan baru', to: '/penjualan', icon: ShoppingCart, tone: 'blue' },
    { label: 'Lihat Laporan', helper: 'Lihat laporan penjualan', to: '/laporan', icon: FileText, tone: 'violet' },
]

function rupiah(value) {
    return `Rp ${Number(value || 0).toLocaleString('id-ID')}`
}

function daysUntil(date) {
    return Math.max(0, Math.ceil((new Date(date) - new Date()) / 86400000))
}

function timeAgo(value) {
    const seconds = Math.max(1, Math.floor((Date.now() - new Date(value).getTime()) / 1000))
    if (seconds < 60) return `${seconds} detik lalu`
    if (seconds < 3600) return `${Math.floor(seconds / 60)} menit lalu`
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam lalu`
    return `${Math.floor(seconds / 86400)} hari lalu`
}

function activityIcon(type) {
    return type === 'sale' ? CheckCircle2 : type === 'stock' ? AlertTriangle : Info
}

function activityTone(type) {
    return type === 'sale'
        ? 'bg-emerald-50 text-emerald-600'
        : type === 'stock'
            ? 'bg-amber-50 text-amber-600'
            : 'bg-blue-50 text-blue-600'
}

onMounted(async () => {
    try {
        const { data } = await http.get('/dashboard')
        dashboard.value = data.data
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Dashboard gagal dimuat.'
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <AppLayout title="Dashboard" subtitle="Ringkasan stok, transaksi, dan notifikasi operasional.">
        <div v-if="loading" class="rounded-lg border border-slate-200 bg-white px-5 py-16 text-center text-sm text-slate-500">
            Memuat dashboard...
        </div>

        <div v-else-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-5 py-10 text-center text-sm text-rose-700">
            {{ error }}
        </div>

        <div v-else class="space-y-5">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total Obat" :value="stats.total_obat" helper="Semua obat terdaftar" :icon="Pill" />
                <StatCard label="Supplier" :value="stats.total_supplier" helper="Mitra aktif" :icon="Users" />
                <StatCard label="Transaksi Hari Ini" :value="stats.transaksi_hari_ini" :helper="`${stats.transaksi_hari_ini} transaksi`" :icon="ReceiptText" />
                <StatCard label="Penjualan Bulan Ini" :value="rupiah(stats.penjualan_bulan_ini)" helper="Total penjualan" :icon="WalletCards" highlighted />
            </section>

            <section class="grid items-start gap-5 xl:grid-cols-[1.25fr_1fr]">
                <div class="space-y-5">
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-emerald-600" />
                            <h2 class="text-sm font-semibold text-slate-900">Trend Penjualan <span class="font-normal text-slate-500">(30 Hari Terakhir)</span></h2>
                        </div>
                        <span class="rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-500">30 Hari Terakhir</span>
                    </div>
                    <div class="p-5">
                        <div class="flex h-52 items-end gap-1.5 border-b border-l border-slate-200 px-2 pt-4">
                            <div
                                v-for="item in trend"
                                :key="item.date"
                                class="group relative flex h-full min-w-0 flex-1 items-end"
                            >
                                <div
                                    class="w-full rounded-t-sm bg-emerald-500 transition hover:bg-emerald-600"
                                    :style="{ height: `${Math.max((Number(item.total) / maxTrend) * 100, item.total > 0 ? 4 : 0)}%` }"
                                ></div>
                                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-[10px] text-white group-hover:block">
                                    {{ item.label }} - {{ rupiah(item.total) }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-between text-[10px] text-slate-500">
                            <span>{{ trend[0]?.label }}</span>
                            <span>{{ trend[Math.floor(trend.length / 2)]?.label }}</span>
                            <span>{{ trend[trend.length - 1]?.label }}</span>
                        </div>
                        <div class="mt-3 flex justify-center gap-2 text-xs text-slate-500">
                            <span class="h-3 w-3 rounded-sm bg-emerald-500"></span>
                            Penjualan (Rp)
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 2xl:grid-cols-2">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <AlertTriangle class="h-5 w-5 text-amber-500" />
                                <h2 class="text-sm font-semibold">Stok Menipis</h2>
                            </div>
                            <RouterLink to="/obat" class="text-xs font-medium text-emerald-600">Lihat semua</RouterLink>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr><th class="px-4 py-3">Nama Obat</th><th class="px-3 py-3">Satuan</th><th class="px-3 py-3">Sisa Stok</th><th class="px-3 py-3">Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in dashboard.notifications.stok_menipis" :key="item.id">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ item.nama_obat }}</td>
                                        <td class="px-3 py-3 capitalize">{{ item.satuan }}</td>
                                        <td class="px-3 py-3">{{ item.stok }}</td>
                                        <td class="px-3 py-3"><span class="rounded border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] text-amber-700">Menipis</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <CalendarDays class="h-5 w-5 text-rose-500" />
                                <h2 class="text-sm font-semibold">Kedaluwarsa Dekat</h2>
                            </div>
                            <RouterLink to="/obat" class="text-xs font-medium text-emerald-600">Lihat semua</RouterLink>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr><th class="px-4 py-3">Nama Obat</th><th class="px-3 py-3">Satuan</th><th class="px-3 py-3">Kedaluwarsa</th><th class="px-3 py-3">Sisa Hari</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in dashboard.notifications.expired_dekat" :key="item.id">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ item.nama_obat }}</td>
                                        <td class="px-3 py-3 capitalize">{{ item.satuan }}</td>
                                        <td class="px-3 py-3">{{ new Date(item.tanggal_expired).toLocaleDateString('id-ID') }}</td>
                                        <td class="px-3 py-3"><span class="rounded border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] text-rose-700">{{ daysUntil(item.tanggal_expired) }} hari</span></td>
                                    </tr>
                                    <tr v-if="!dashboard.notifications.expired_dekat.length">
                                        <td colspan="4" class="px-4 py-10 text-center text-slate-500">Tidak ada obat yang segera kedaluwarsa.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>

                <div class="space-y-5">
                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h2 class="text-sm font-semibold text-slate-900">Aksi Cepat</h2>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-3">
                            <RouterLink
                                v-for="action in quickActions"
                                :key="action.to"
                                :to="action.to"
                                class="flex min-h-20 items-center gap-3 rounded-lg border p-3 transition hover:-translate-y-0.5 hover:shadow-sm"
                                :class="{
                                    'border-emerald-200 bg-emerald-50 text-emerald-700': action.tone === 'emerald',
                                    'border-blue-200 bg-blue-50 text-blue-700': action.tone === 'blue',
                                    'border-violet-200 bg-violet-50 text-violet-700': action.tone === 'violet',
                                }"
                            >
                                <component :is="action.icon" class="h-7 w-7 shrink-0" />
                                <div>
                                    <p class="text-xs font-semibold">{{ action.label }}</p>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ action.helper }}</p>
                                </div>
                            </RouterLink>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                            <Bell class="h-5 w-5 text-slate-600" />
                            <h2 class="text-sm font-semibold text-slate-900">Notifikasi &amp; Aktivitas Terbaru</h2>
                        </div>
                        <div class="divide-y divide-slate-100 px-4">
                            <div v-for="activity in dashboard.activities" :key="activity.id" class="flex gap-3 py-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="activityTone(activity.type)">
                                    <component :is="activityIcon(activity.type)" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-medium text-slate-800">{{ activity.title }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">{{ activity.description }}</p>
                                </div>
                                <span class="shrink-0 text-[10px] text-slate-400">{{ timeAgo(activity.occurred_at) }}</span>
                            </div>
                            <div v-if="!dashboard.activities.length" class="py-10 text-center text-xs text-slate-500">Belum ada aktivitas terbaru.</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
