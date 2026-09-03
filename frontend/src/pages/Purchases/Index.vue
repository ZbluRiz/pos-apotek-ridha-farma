<script setup>
import { AlertCircle, CheckCircle2, Edit, ExternalLink, Eye, ImageIcon, Plus, RotateCcw, Save, Trash2, X } from 'lucide-vue-next'
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import http, { apiOrigin } from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const purchases = ref([])
const medicines = ref([])
const suppliers = ref([])
const editingId = ref(null)
const loading = ref(true)
const error = ref('')
const saving = ref(false)
const actionMessage = ref('')
const actionError = ref('')
const fileInputKey = ref(0)
const currentInvoiceFile = ref(null)
const searchQuery = ref('')
const filterSupplierId = ref('')
const filterReturStatus = ref('')
const filterStartDate = ref('')
const filterEndDate = ref('')
const invoicePreview = ref(null)

const emptyDetail = () => ({
    medicine_id: '',
    nomor_batch: '',
    qty: 1,
    harga_beli: '',
    tanggal_expired: '',
})

const form = reactive({
    supplier_id: '',
    nomor_faktur: '',
    tanggal_faktur: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10),
    file_faktur: null,
    catatan: '',
    details: [emptyDetail()],
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

function decoratePurchase(purchase) {
    return {
        ...purchase,
        details: (purchase.details || []).map((detail) => ({
            ...detail,
            retur_qty_input: detail.qty_retur || detail.qty,
        })),
    }
}

async function loadPurchases() {
    loading.value = true
    error.value = ''

    try {
        const { data } = await http.get('/purchases', {
            params: {
                per_page: 50,
                search: searchQuery.value || undefined,
                supplier_id: filterSupplierId.value || undefined,
                retur_status: filterReturStatus.value || undefined,
                start_date: filterStartDate.value || undefined,
                end_date: filterEndDate.value || undefined,
            },
        })
        purchases.value = rows(data).map(decoratePurchase)
        await nextTick()
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Data faktur pembelian gagal dimuat.'
    } finally {
        loading.value = false
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

async function loadSuppliers() {
    try {
        const { data } = await http.get('/suppliers', {
            params: { per_page: 100 },
        })
        suppliers.value = rows(data)
    } catch {
        suppliers.value = []
    }
}

function selectedMedicine(id) {
    return medicines.value.find((medicine) => medicine.id === Number(id))
}

async function searchPurchases() {
    await loadPurchases()
}

async function clearSearch() {
    searchQuery.value = ''
    filterSupplierId.value = ''
    filterReturStatus.value = ''
    filterStartDate.value = ''
    filterEndDate.value = ''
    await loadPurchases()
}

const totalPreview = computed(() => form.details.reduce((total, detail) => (
    total + Number(detail.qty || 0) * Number(detail.harga_beli || 0)
), 0))

function addDetail() {
    form.details.push(emptyDetail())
}

function removeDetail(index) {
    if (form.details.length === 1) {
        return
    }

    form.details.splice(index, 1)
}

function reset() {
    editingId.value = null
    Object.assign(form, {
        supplier_id: '',
        nomor_faktur: '',
        tanggal_faktur: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10),
        file_faktur: null,
        catatan: '',
        details: [emptyDetail()],
    })
    currentInvoiceFile.value = null
    fileInputKey.value += 1
}

function edit(purchase) {
    editingId.value = purchase.id
    currentInvoiceFile.value = purchase
    Object.assign(form, {
        supplier_id: purchase.supplier_id,
        nomor_faktur: purchase.nomor_faktur,
        tanggal_faktur: purchase.tanggal_faktur,
        file_faktur: null,
        catatan: purchase.catatan || '',
        details: purchase.details.map((detail) => ({
            medicine_id: detail.medicine_id,
            nomor_batch: detail.nomor_batch || '',
            qty: detail.qty,
            harga_beli: detail.harga_beli,
            tanggal_expired: detail.tanggal_expired,
        })),
    })
    fileInputKey.value += 1
}

function handleInvoiceFile(event) {
    form.file_faktur = event.target.files?.[0] || null
}

function buildPurchaseFormData() {
    const payload = new FormData()

    payload.append('supplier_id', Number(form.supplier_id))
    payload.append('nomor_faktur', form.nomor_faktur)
    payload.append('tanggal_faktur', form.tanggal_faktur)
    payload.append('catatan', form.catatan || '')

    if (form.file_faktur) {
        payload.append('file_faktur', form.file_faktur)
    }

    form.details.forEach((detail, index) => {
        payload.append(`details[${index}][medicine_id]`, Number(detail.medicine_id))
        payload.append(`details[${index}][nomor_batch]`, detail.nomor_batch || '')
        payload.append(`details[${index}][qty]`, Number(detail.qty))
        payload.append(`details[${index}][harga_beli]`, Number(detail.harga_beli))
        payload.append(`details[${index}][tanggal_expired]`, detail.tanggal_expired)
    })

    return payload
}

async function submit() {
    saving.value = true
    actionMessage.value = ''
    actionError.value = ''
    const payload = buildPurchaseFormData()

    try {
        if (editingId.value) {
            payload.append('_method', 'PUT')
            await http.post(`/purchases/${editingId.value}`, payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            actionMessage.value = 'Faktur pembelian berhasil diperbarui.'
        } else {
            await http.post('/purchases', payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            actionMessage.value = 'Faktur pembelian berhasil ditambahkan.'
        }

        reset()
        await loadPurchases()
        void loadMedicines()
    } catch (exception) {
        actionError.value = exception.response?.data?.message || 'Faktur pembelian gagal disimpan.'
    } finally {
        saving.value = false
    }
}

async function destroy(purchase) {
    actionMessage.value = ''
    actionError.value = ''

    if (!confirm(`Hapus faktur ${purchase.nomor_faktur}?`)) {
        return
    }

    try {
        await http.delete(`/purchases/${purchase.id}`)
        actionMessage.value = 'Faktur pembelian berhasil dihapus.'
        await loadPurchases()
        void loadMedicines()
    } catch (exception) {
        actionError.value = exception.response?.data?.message || 'Faktur pembelian gagal dihapus.'
    }
}

async function updateReturn(purchase, detail, status) {
    actionMessage.value = ''
    actionError.value = ''

    try {
        await http.patch(`/purchases/${purchase.id}/details/${detail.id}/return`, {
            status_retur: status,
            qty_retur: Number(detail.retur_qty_input || 0),
            tanggal_retur: ['diretur', 'ditolak'].includes(status) ? new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10) : null,
            catatan_retur: status === 'diretur' ? 'Retur ke supplier berdasarkan faktur pembelian.' : null,
        })

        actionMessage.value = 'Status retur berhasil diperbarui.'
        await loadPurchases()
        void loadMedicines()
    } catch (exception) {
        actionError.value = exception.response?.data?.message || 'Status retur gagal diperbarui.'
    }
}

function formatCurrency(value) {
    return `Rp ${Number(value || 0).toLocaleString('id-ID')}`
}

function invoiceFileName(path) {
    return path?.split('/').pop() || 'File faktur'
}

function invoiceFileUrl(purchase) {
    // file_faktur_url dari backend adalah path relatif seperti "/storage/faktur-pembelian/xxx.jpg"
    // Backend ada di apiOrigin, jadi kita prefix dengan apiOrigin
    const url = purchase?.file_faktur_url

    if (!url) {
        return ''
    }

    // Sudah full URL, pakai langsung
    if (/^https?:\/\//i.test(url)) {
        return url
    }

    // Path relatif: gabungkan dengan domain backend (api.apotekridhafarma.biz.id)
    return `${apiOrigin}${url.startsWith('/') ? '' : '/'}${url}`
}

function isInvoiceImage(purchase) {
    const source = `${purchase?.file_faktur || purchase?.file_faktur_url || ''}`.toLowerCase()

    return /\.(jpg|jpeg|png|webp)(\?.*)?$/.test(source)
}

function openInvoicePreview(purchase) {
    if (!purchase?.file_faktur_url) {
        return
    }

    invoicePreview.value = purchase
}

function closeInvoicePreview() {
    invoicePreview.value = null
}

function returnStatusClass(status) {
    return {
        belum_retur: 'bg-slate-100 text-slate-600',
        diajukan_retur: 'bg-amber/10 text-amber',
        diretur: 'bg-leaf/10 text-leaf',
        ditolak: 'bg-rosewood/10 text-rosewood',
    }[status] || 'bg-slate-100 text-slate-600'
}

function returnStatusLabel(status) {
    return {
        belum_retur: 'Belum retur',
        diajukan_retur: 'Diajukan',
        diretur: 'Diretur',
        ditolak: 'Ditolak',
    }[status] || status
}

onMounted(async () => {
    await loadPurchases()
    void loadMedicines()
    void loadSuppliers()
})
</script>

<template>
    <AppLayout>
        <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="text-xl font-semibold">Faktur Pembelian</h1>
                <p class="text-sm text-slate-500">Simpan faktur, batch, expired, dan status retur obat.</p>
            </div>
            <button class="btn-muted" type="button" @click="reset">
                <Plus class="h-4 w-4" />
                Baru
            </button>
        </div>

        <div v-if="actionMessage" class="alert-success">
            <CheckCircle2 class="h-6 w-6 shrink-0 text-emerald-600" />
            <span>{{ actionMessage }}</span>
        </div>
        <div v-if="actionError" class="alert-danger">
            <AlertCircle class="h-6 w-6 shrink-0 text-rosewood" />
            <span>{{ actionError }}</span>
        </div>

        <div class="grid gap-5 xl:grid-cols-[430px_1fr]">
            <form class="rounded-md border border-slate-200 bg-white p-4" @submit.prevent="submit">
                <div class="grid gap-3.5">
                    <div>
                        <label class="label" for="supplier_faktur">Supplier Faktur</label>
                        <select id="supplier_faktur" v-model="form.supplier_id" class="input" required>
                            <option value="">Pilih supplier faktur</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.nama_supplier }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="nomor_faktur">Nomor Faktur</label>
                            <input id="nomor_faktur" v-model="form.nomor_faktur" class="input" placeholder="Nomor faktur pembelian" required />
                        </div>
                        <div>
                            <label class="label" for="tanggal_faktur">Tanggal Faktur</label>
                            <input id="tanggal_faktur" v-model="form.tanggal_faktur" class="input" type="date" required />
                        </div>
                    </div>
                    <div>
                        <label class="label" for="file_faktur">Upload File Faktur (Foto / PDF)</label>
                        <input
                            id="file_faktur"
                            :key="fileInputKey"
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            class="input"
                            type="file"
                            @change="handleInvoiceFile"
                        />
                        <p class="mt-1 text-xs text-slate-500">Upload foto faktur atau PDF. Maksimal 2MB.</p>
                        <p v-if="form.file_faktur" class="mt-1 text-xs font-medium text-emerald-700">
                            File baru: {{ form.file_faktur.name }}
                        </p>
                        <p v-else-if="currentInvoiceFile?.file_faktur" class="mt-1 text-xs text-slate-500">
                            File tersimpan: {{ invoiceFileName(currentInvoiceFile.file_faktur) }}
                        </p>
                        <button
                            v-if="currentInvoiceFile?.file_faktur_url"
                            class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:underline"
                            type="button"
                            @click="openInvoicePreview(currentInvoiceFile)"
                        >
                            <Eye class="h-3.5 w-3.5" />
                            Lihat file tersimpan
                        </button>
                    </div>
                    <div>
                        <label class="label" for="catatan_faktur">Catatan Faktur</label>
                        <textarea id="catatan_faktur" v-model="form.catatan" class="input min-h-20" placeholder="Catatan faktur pembelian"></textarea>
                    </div>

                    <div class="rounded-md border border-slate-200">
                        <div class="flex items-center justify-between border-b border-slate-200 px-3 py-2">
                            <p class="text-sm font-semibold text-slate-800">Detail Obat</p>
                            <button class="btn-muted min-h-8 px-2 py-1 text-xs" type="button" @click="addDetail">
                                <Plus class="h-3.5 w-3.5" />
                                Item
                            </button>
                        </div>

                        <div class="space-y-3 p-3">
                            <div v-for="(detail, index) in form.details" :key="index" class="rounded-md bg-slate-50 p-3">
                                <div class="grid gap-2.5">
                                    <div>
                                        <label class="label" :for="'medicine_id_' + index">Pilih Obat</label>
                                        <select :id="'medicine_id_' + index" v-model="detail.medicine_id" class="input" required @change="detail.harga_beli = selectedMedicine(detail.medicine_id)?.harga_beli || detail.harga_beli">
                                            <option value="">Pilih obat</option>
                                            <option v-for="medicine in medicines" :key="medicine.id" :value="medicine.id">
                                                {{ medicine.nama_obat }} - stok {{ medicine.stok }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <div>
                                            <label class="label" :for="'nomor_batch_' + index">Nomor Batch</label>
                                            <input :id="'nomor_batch_' + index" v-model="detail.nomor_batch" class="input" placeholder="Nomor batch" />
                                        </div>
                                        <div>
                                            <label class="label" :for="'tanggal_expired_' + index">Tanggal Expired</label>
                                            <input :id="'tanggal_expired_' + index" v-model="detail.tanggal_expired" class="input" type="date" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_1fr_44px]">
                                        <div>
                                            <label class="label" :for="'qty_' + index">Qty Beli</label>
                                            <input :id="'qty_' + index" v-model.number="detail.qty" class="input" min="1" placeholder="Qty beli" type="number" required />
                                        </div>
                                        <div>
                                            <label class="label" :for="'harga_beli_' + index">Harga Beli / Item</label>
                                            <input :id="'harga_beli_' + index" v-model.number="detail.harga_beli" class="input" min="0" placeholder="Harga beli/item" type="number" required />
                                        </div>
                                        <div class="flex items-end">
                                            <button class="btn-danger w-full sm:w-auto" title="Hapus item" type="button" @click="removeDetail(index)">
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold">
                        <span>Total Faktur</span>
                        <span>{{ formatCurrency(totalPreview) }}</span>
                    </div>

                    <div class="flex gap-2">
                        <button class="btn-primary flex-1" :disabled="saving" type="submit">
                            <Save class="h-4 w-4" />
                            {{ editingId ? 'Update Faktur' : 'Simpan Faktur' }}
                        </button>
                        <button class="btn-muted" type="button" @click="reset">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </form>

            <section class="rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="font-semibold text-slate-900">Riwayat Faktur & Retur</h2>
                    <p class="text-xs text-slate-500">Detail batch expired bisa ditandai untuk retur ke supplier.</p>
                </div>
                <form class="border-b border-slate-200 p-4" @submit.prevent="searchPurchases">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 items-end">
                        <div>
                            <label class="label" for="search_purchases">Cari Faktur</label>
                            <input id="search_purchases" v-model="searchQuery" class="input" placeholder="Obat, supplier, atau nomor faktur..." />
                        </div>
                        <div>
                            <label class="label" for="filter_purchase_supplier">Supplier</label>
                            <select id="filter_purchase_supplier" v-model="filterSupplierId" class="input" @change="searchPurchases">
                                <option value="">Semua Supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.nama_supplier }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="filter_retur_status">Status Retur</label>
                            <select id="filter_retur_status" v-model="filterReturStatus" class="input" @change="searchPurchases">
                                <option value="">Semua Status Retur</option>
                                <option value="belum_retur">Belum Retur</option>
                                <option value="diajukan_retur">Diajukan Retur</option>
                                <option value="diretur">Diretur</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="filter_purchase_start_date">Tanggal Faktur (Mulai)</label>
                            <input id="filter_purchase_start_date" v-model="filterStartDate" class="input" type="date" />
                        </div>
                        <div>
                            <label class="label" for="filter_purchase_end_date">Tanggal Faktur (Selesai)</label>
                            <input id="filter_purchase_end_date" v-model="filterEndDate" class="input" type="date" />
                        </div>
                        <div class="flex items-end gap-2 lg:col-span-3 justify-end pt-1">
                            <button class="btn-primary" type="submit">Cari / Filter</button>
                            <button class="btn-muted" type="button" @click="clearSearch">Reset</button>
                        </div>
                    </div>
                </form>

                <div v-if="loading" class="px-4 py-10 text-center text-sm text-slate-500">Memuat faktur pembelian...</div>
                <div v-else-if="error" class="px-4 py-10 text-center text-sm text-rosewood">{{ error }}</div>
                <div v-else-if="purchases.length === 0" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada faktur pembelian.</div>

                <div v-else class="divide-y divide-slate-200">
                    <article v-for="purchase in purchases" :key="purchase.id" class="p-4">
                        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-slate-900">{{ purchase.nomor_faktur }}</h3>
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">{{ formatCurrency(purchase.total_harga) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ purchase.supplier?.nama_supplier || '-' }} &middot; {{ purchase.tanggal_faktur }}
                                </p>
                                <div v-if="purchase.file_faktur_url" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <button
                                        v-if="isInvoiceImage(purchase)"
                                        class="group flex w-full max-w-xs items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-2 text-left transition hover:border-emerald-300 hover:bg-emerald-50 sm:w-72"
                                        type="button"
                                        @click="openInvoicePreview(purchase)"
                                    >
                                        <img
                                            class="h-14 w-20 rounded border border-slate-200 object-cover"
                                            :src="invoiceFileUrl(purchase)"
                                            alt="Preview faktur"
                                        />
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-xs font-semibold text-slate-800">{{ invoiceFileName(purchase.file_faktur) }}</span>
                                            <span class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-700">
                                                <Eye class="h-3.5 w-3.5" />
                                                Preview faktur
                                            </span>
                                        </span>
                                    </button>

                                    <button
                                        v-else
                                        class="btn-muted min-h-9 px-3 py-1.5 text-xs"
                                        type="button"
                                        @click="openInvoicePreview(purchase)"
                                    >
                                        <ImageIcon class="h-3.5 w-3.5" />
                                        Lihat faktur
                                    </button>

                                    <a
                                        class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-emerald-700 hover:underline"
                                        :href="invoiceFileUrl(purchase)"
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        <ExternalLink class="h-3.5 w-3.5" />
                                        Buka tab baru
                                    </a>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn-muted" title="Edit faktur" type="button" @click="edit(purchase)">
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button class="btn-danger" title="Hapus faktur" type="button" @click="destroy(purchase)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[840px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Obat</th>
                                        <th class="px-3 py-2">Batch</th>
                                        <th class="px-3 py-2">Qty</th>
                                        <th class="px-3 py-2">Harga</th>
                                        <th class="px-3 py-2">Expired</th>
                                        <th class="px-3 py-2">Retur</th>
                                        <th class="px-3 py-2">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="detail in purchase.details" :key="detail.id">
                                        <td class="px-3 py-2 font-medium">{{ detail.medicine?.nama_obat || '-' }}</td>
                                        <td class="px-3 py-2">{{ detail.nomor_batch || '-' }}</td>
                                        <td class="px-3 py-2">{{ detail.qty }}</td>
                                        <td class="px-3 py-2">{{ formatCurrency(detail.harga_beli) }}</td>
                                        <td class="px-3 py-2" :class="detail.is_expired ? 'font-semibold text-rosewood' : ''">
                                            {{ detail.tanggal_expired }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="returnStatusClass(detail.status_retur)">
                                                    {{ returnStatusLabel(detail.status_retur) }}
                                                </span>
                                                <input v-model.number="detail.retur_qty_input" class="input h-8 w-20 py-1" min="0" :max="detail.qty" type="number" />
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <button class="btn-muted min-h-8 px-2 py-1 text-xs" type="button" @click="updateReturn(purchase, detail, 'diajukan_retur')">
                                                    <RotateCcw class="h-3.5 w-3.5" />
                                                    Ajukan
                                                </button>
                                                <button class="btn-primary min-h-8 px-2 py-1 text-xs" type="button" @click="updateReturn(purchase, detail, 'diretur')">
                                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                                    Diretur
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <div
            v-if="invoicePreview"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
            @click.self="closeInvoicePreview"
        >
            <section class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold text-slate-900">
                            Faktur {{ invoicePreview.nomor_faktur }}
                        </h2>
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ invoiceFileName(invoicePreview.file_faktur) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            class="btn-muted min-h-9 px-3 py-1.5 text-xs"
                            :href="invoiceFileUrl(invoicePreview)"
                            target="_blank"
                            rel="noreferrer"
                        >
                            <ExternalLink class="h-3.5 w-3.5" />
                            Tab baru
                        </a>
                        <button class="btn-muted min-h-9 px-3 py-1.5" title="Tutup preview" type="button" @click="closeInvoicePreview">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-auto bg-slate-100 p-4">
                    <img
                        v-if="isInvoiceImage(invoicePreview)"
                        class="mx-auto max-h-[72vh] max-w-full rounded-md bg-white object-contain shadow"
                        :src="invoiceFileUrl(invoicePreview)"
                        alt="Preview faktur pembelian"
                    />
                    <iframe
                        v-else
                        class="h-[72vh] w-full rounded-md border border-slate-200 bg-white"
                        :src="invoiceFileUrl(invoicePreview)"
                        title="Preview faktur pembelian"
                    ></iframe>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
