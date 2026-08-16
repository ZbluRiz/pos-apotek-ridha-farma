<script setup>
import { AlertCircle, CheckCircle2, Plus, Printer, Save, Trash2, X } from 'lucide-vue-next'
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const medicines = ref([])
const sales = ref([])
const salesLoading = ref(true)
const salesError = ref('')
const actionMessage = ref('')
const actionError = ref('')
const searchQuery = ref('')
const selectedReceipt = ref(null)
const receiptLoading = ref(false)
const form = reactive({
    tanggal: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16),
    details: [{ medicine_id: '', qty: 1 }],
})

function rows(payload) {
    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    if (Array.isArray(payload?.data?.data)) {
        return payload.data.data
    }

    return []
}

async function loadSales() {
    salesLoading.value = true
    salesError.value = ''

    try {
        const { data } = await http.get('/sales', {
            params: {
                per_page: 50,
                search: searchQuery.value || undefined,
            },
        })
        sales.value = rows(data)
        await nextTick()
    } catch (error) {
        salesError.value = error.response?.data?.message || 'Data penjualan gagal dimuat.'
    } finally {
        salesLoading.value = false
    }
}

async function loadMedicines() {
    try {
        const { data } = await http.get('/medicines', {
            params: { per_page: 100 },
        })
        medicines.value = rows(data)
    } catch {
        medicines.value = []
    }
}

async function load() {
    await loadSales()
    await loadMedicines()
}

async function searchSales() {
    await loadSales()
}

async function clearSearch() {
    searchQuery.value = ''
    await loadSales()
}

function selectedMedicine(id) {
    return medicines.value.find((medicine) => medicine.id === Number(id))
}

const totalPreview = computed(() => form.details.reduce((total, detail) => {
    const medicine = selectedMedicine(detail.medicine_id)
    return total + (medicine ? Number(medicine.harga_jual) * Number(detail.qty || 0) : 0)
}, 0))

function addDetail() {
    form.details.push({ medicine_id: '', qty: 1 })
}

function removeDetail(index) {
    form.details.splice(index, 1)
}

async function submit() {
    actionMessage.value = ''
    actionError.value = ''

    try {
        await http.post('/sales', {
            tanggal: form.tanggal,
            details: form.details.map((detail) => ({
                medicine_id: Number(detail.medicine_id),
                qty: Number(detail.qty),
            })),
        })

        actionMessage.value = 'Transaksi berhasil disimpan.'
        form.tanggal = new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16)
        form.details = [{ medicine_id: '', qty: 1 }]
        await load()
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Transaksi gagal disimpan.'
    }
}

async function destroy(sale) {
    actionMessage.value = ''
    actionError.value = ''

    if (!confirm(`Hapus transaksi ${sale.nomor_transaksi}?`)) {
        return
    }

    try {
        await http.delete(`/sales/${sale.id}`)
        actionMessage.value = 'Transaksi berhasil dihapus.'
        await load()
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Transaksi gagal dihapus.'
    }
}

async function openReceipt(sale) {
    receiptLoading.value = true

    try {
        const { data } = await http.get(`/sales/${sale.id}`)
        selectedReceipt.value = data.data
        await nextTick()
    } finally {
        receiptLoading.value = false
    }
}

function closeReceipt() {
    selectedReceipt.value = null
}

async function printReceipt() {
    await nextTick()
    window.print()
}

function formatCurrency(value) {
    return `Rp ${Number(value || 0).toLocaleString('id-ID')}`
}

function formatDateTime(value) {
    const date = new Date(value)
    const formattedDate = date.toLocaleDateString('id-ID')
    const formattedTime = date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    })

    return `${formattedDate}, ${formattedTime}`
}

onMounted(async () => {
    await loadSales()
    void loadMedicines()
})
</script>

<template>
    <AppLayout>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Penjualan</h1>
            <p class="text-sm text-slate-500">Catat transaksi dan kurangi stok otomatis.</p>
        </div>

        <div v-if="actionMessage" class="alert-success">
            <CheckCircle2 class="h-6 w-6 shrink-0 text-emerald-600" />
            <span>{{ actionMessage }}</span>
        </div>
        <div v-if="actionError" class="alert-danger">
            <AlertCircle class="h-6 w-6 shrink-0 text-rosewood" />
            <span>{{ actionError }}</span>
        </div>

        <div class="grid gap-5 xl:grid-cols-[420px_1fr]">
            <form class="rounded-md border border-slate-200 bg-white p-4" @submit.prevent="submit">
                <div class="space-y-3.5">
                    <div>
                        <label class="label" for="tanggal_transaksi">Tanggal Transaksi</label>
                        <input id="tanggal_transaksi" v-model="form.tanggal" class="input" type="datetime-local" required />
                    </div>

                    <div class="space-y-3">
                        <label class="label mb-0">Detail Item Obat</label>
                        <div v-for="(detail, index) in form.details" :key="index" class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_90px_44px]">
                            <div>
                                <label class="sr-only" :for="'sale_med_' + index">Pilih Obat</label>
                                <select :id="'sale_med_' + index" v-model="detail.medicine_id" class="input" required>
                                    <option value="">Pilih obat</option>
                                    <option v-for="medicine in medicines" :key="medicine.id" :value="medicine.id">
                                        {{ medicine.nama_obat }} - stok {{ medicine.stok }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="sr-only" :for="'sale_qty_' + index">Qty</label>
                                <input :id="'sale_qty_' + index" v-model.number="detail.qty" class="input" min="1" placeholder="Qty" type="number" required />
                            </div>
                            <div class="flex items-end">
                                <button class="btn-danger w-full sm:w-auto" title="Hapus baris" type="button" @click="removeDetail(index)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <button class="btn-muted w-full" type="button" @click="addDetail">
                        <Plus class="h-4 w-4" />
                        Tambah Item
                    </button>

                    <div class="flex items-center justify-between rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold">
                        <span>Total</span>
                        <span>Rp {{ totalPreview.toLocaleString('id-ID') }}</span>
                    </div>

                    <button class="btn-primary w-full" type="submit">
                        <Save class="h-4 w-4" />
                        Simpan Transaksi
                    </button>
                </div>
            </form>

            <section class="overflow-x-auto rounded-md border border-slate-200 bg-white">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Riwayat Penjualan</h2>
                        <p class="text-xs text-slate-500">Cari transaksi berdasarkan nama/kode obat atau nomor transaksi.</p>
                    </div>
                    <form class="flex flex-col gap-2 sm:flex-row md:w-[430px]" @submit.prevent="searchSales">
                        <div class="flex-1">
                            <label class="label" for="search_sales">Cari Penjualan</label>
                            <input id="search_sales" v-model="searchQuery" class="input" placeholder="Cari obat di transaksi..." />
                        </div>
                        <div class="flex items-end gap-2">
                            <button class="btn-primary" type="submit">Cari</button>
                            <button class="btn-muted" type="button" @click="clearSearch">Reset</button>
                        </div>
                    </form>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Nomor</th>
                            <th class="px-3 py-3">Tanggal</th>
                            <th class="px-3 py-3">Total</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="salesLoading">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="4">Memuat data penjualan...</td>
                        </tr>
                        <tr v-else-if="salesError">
                            <td class="px-3 py-8 text-center text-rosewood" colspan="4">{{ salesError }}</td>
                        </tr>
                        <tr v-else-if="sales.length === 0">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="4">Belum ada transaksi penjualan.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="sale in sales" :key="sale.id">
                                <td class="px-3 py-3 font-medium">{{ sale.nomor_transaksi }}</td>
                                <td class="px-3 py-3">{{ formatDateTime(sale.tanggal) }}</td>
                                <td class="px-3 py-3">{{ formatCurrency(sale.total_harga) }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="btn-muted" title="Cetak struk" type="button" @click="openReceipt(sale)">
                                            <Printer class="h-4 w-4" />
                                        </button>
                                        <button class="btn-danger" title="Hapus" type="button" @click="destroy(sale)">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </section>
        </div>

        <div
            v-if="selectedReceipt"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4"
            @click.self="closeReceipt"
        >
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-md bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="font-semibold text-slate-900">Preview Struk</h2>
                        <p class="text-xs text-slate-500">{{ selectedReceipt.nomor_transaksi }}</p>
                    </div>
                    <button class="btn-muted" title="Tutup" type="button" @click="closeReceipt">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="p-4">
                    <div class="receipt-print-area mx-auto w-[300px] bg-white p-4 font-mono text-[12px] text-slate-950">
                        <div class="text-center">
                            <p class="text-base font-bold">APOTEK RIDHA FARMA</p>
                            <p>Klewer, Sraten, Kec. Gatak, Kabupaten Sukoharjo, Jawa Tengah</p>
                            <p>Telp. 021-123456</p>
                        </div>

                        <div class="my-3 border-t border-dashed border-slate-400"></div>

                        <div class="space-y-1">
                            <div class="flex justify-between gap-3">
                                <span>No</span>
                                <span class="text-right">{{ selectedReceipt.nomor_transaksi }}</span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span>Tanggal</span>
                                <span class="text-right">{{ formatDateTime(selectedReceipt.tanggal) }}</span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span>Kasir</span>
                                <!-- <span class="text-right">{{ selectedReceipt.user?.name || '-' }}</span> -->
                            </div>
                        </div>

                        <div class="my-3 border-t border-dashed border-slate-400"></div>

                        <div class="space-y-2">
                            <div v-for="detail in selectedReceipt.details" :key="detail.id">
                                <p class="font-semibold">{{ detail.medicine?.nama_obat || 'Obat' }}</p>
                                <div class="flex justify-between gap-3">
                                    <span>{{ detail.qty }} x {{ formatCurrency(detail.harga) }}</span>
                                    <span>{{ formatCurrency(detail.subtotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="my-3 border-t border-dashed border-slate-400"></div>

                        <div class="flex justify-between gap-3 text-sm font-bold">
                            <span>TOTAL</span>
                            <span>{{ formatCurrency(selectedReceipt.total_harga) }}</span>
                        </div>

                        <div class="my-3 border-t border-dashed border-slate-400"></div>

                        <div class="text-center">
                            <p>Terima kasih</p>
                            <p>Semoga lekas sembuh</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-slate-200 p-4">
                    <button class="btn-primary flex-1" type="button" @click="printReceipt">
                        <Printer class="h-4 w-4" />
                        Cetak Struk
                    </button>
                    <button class="btn-muted" type="button" @click="closeReceipt">Tutup</button>
                </div>
            </div>
        </div>

        <div v-if="receiptLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/30 no-print">
            <div class="rounded-md bg-white px-4 py-3 text-sm shadow-lg">Menyiapkan struk...</div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    :global(body *) {
        visibility: hidden;
    }

    :global(.receipt-print-area),
    :global(.receipt-print-area *) {
        visibility: visible;
    }

    :global(.receipt-print-area) {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm !important;
        min-height: auto;
        margin: 0 !important;
        padding: 6mm !important;
        color: #000 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    :global(.no-print) {
        display: none !important;
    }

    @page {
        size: 80mm auto;
        margin: 0;
    }
}
</style>
