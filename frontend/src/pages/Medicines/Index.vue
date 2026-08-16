<script setup>
import { AlertCircle, CheckCircle2, Edit, Plus, Save, Trash2, X } from 'lucide-vue-next'
import { nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const medicines = ref([])
const suppliers = ref([])
const editingId = ref(null)
const medicinesLoading = ref(true)
const medicinesError = ref('')
const actionMessage = ref('')
const actionError = ref('')
const searchQuery = ref('')
const form = reactive({
    supplier_id: '',
    kode_obat: '',
    nama_obat: '',
    kategori: '',
    satuan: '',
    harga_beli: '',
    harga_jual: '',
    stok: '',
    stok_minimum: '',
    tanggal_expired: '',
    prioritas_owner: '',
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

async function loadMedicines() {
    medicinesLoading.value = true
    medicinesError.value = ''

    try {
        const { data } = await http.get('/medicines', {
            params: {
                per_page: 100,
                search: searchQuery.value || undefined,
            },
        })
        medicines.value = rows(data)
        await nextTick()
    } catch (error) {
        medicinesError.value = error.response?.data?.message || 'Data obat gagal dimuat.'
    } finally {
        medicinesLoading.value = false
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

async function load() {
    await Promise.all([
        loadMedicines(),
        loadSuppliers(),
    ])
}

async function searchMedicines() {
    await loadMedicines()
}

async function clearSearch() {
    searchQuery.value = ''
    await loadMedicines()
}

function reset() {
    editingId.value = null
    Object.assign(form, {
        supplier_id: '',
        kode_obat: '',
        nama_obat: '',
        kategori: '',
        satuan: '',
        harga_beli: '',
        harga_jual: '',
        stok: '',
        stok_minimum: '',
        tanggal_expired: '',
        prioritas_owner: '',
    })
}

function edit(item) {
    editingId.value = item.id
    Object.assign(form, item, { supplier_id: item.supplier_id || '' })
}

async function submit() {
    actionMessage.value = ''
    actionError.value = ''
    const payload = { ...form, supplier_id: form.supplier_id || null }
    try {
        if (editingId.value) {
            await http.put(`/medicines/${editingId.value}`, payload)
            actionMessage.value = 'Data obat berhasil diperbarui.'
        } else {
            await http.post('/medicines', payload)
            actionMessage.value = 'Data obat berhasil ditambahkan.'
        }
        reset()
        await load()
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Data obat gagal disimpan.'
    }
}

async function destroy(item) {
    actionMessage.value = ''
    actionError.value = ''

    if (!confirm(`Hapus obat ${item.nama_obat}?`)) {
        return
    }

    try {
        await http.delete(`/medicines/${item.id}`)
        actionMessage.value = 'Data obat berhasil dihapus.'
        await load()
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Data obat gagal dihapus.'
    }
}

onMounted(async () => {
    await loadMedicines()
    void loadSuppliers()
})
</script>

<template>
    <AppLayout>
        <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="text-xl font-semibold">Data Obat</h1>
                <p class="text-sm text-slate-500">Kelola obat, stok minimum, expired, dan prioritas owner.</p>
            </div>
            <button class="btn-muted" @click="reset">
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

        <div class="grid gap-5 xl:grid-cols-[360px_1fr]">
            <form class="rounded-md border border-slate-200 bg-white p-4" @submit.prevent="submit">
                <div class="grid gap-3.5">
                    <div>
                        <label class="label" for="kode_obat">Kode Obat</label>
                        <input id="kode_obat" v-model="form.kode_obat" class="input" placeholder="Kode obat" required />
                    </div>
                    <div>
                        <label class="label" for="nama_obat">Nama Obat</label>
                        <input id="nama_obat" v-model="form.nama_obat" class="input" placeholder="Nama obat" required />
                    </div>
                    <div>
                        <label class="label" for="kategori">Kategori</label>
                        <input id="kategori" v-model="form.kategori" class="input" placeholder="Kategori" required />
                    </div>
                    <div>
                        <label class="label" for="supplier_id">Supplier</label>
                        <select id="supplier_id" v-model="form.supplier_id" class="input">
                            <option value="">Tanpa supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.nama_supplier }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="satuan">Satuan Obat</label>
                            <select id="satuan" v-model="form.satuan" class="input" required>
                                <option value="">Pilih satuan obat</option>
                                <option value="strip">Strip</option>
                                <option value="botol">Botol</option>
                                <option value="box">Box / Dus</option>
                                <option value="tube">Tube</option>
                                <option value="sachet">Sachet</option>
                                <option value="tablet">Tablet</option>
                                <option value="kapsul">Kapsul</option>
                                <option value="ampul">Ampul</option>
                                <option value="vial">Vial</option>
                                <option value="pack">Pack</option>
                                <option value="pcs">Pcs</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="prioritas_owner">Prioritas Owner (1-5)</label>
                            <input
                                id="prioritas_owner"
                                v-model.number="form.prioritas_owner"
                                class="input"
                                min="1"
                                max="5"
                                placeholder="Prioritas owner (1-5)"
                                type="number"
                                required
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="harga_beli">Harga Beli</label>
                            <input id="harga_beli" v-model.number="form.harga_beli" class="input" min="0" placeholder="Harga beli" type="number" required />
                        </div>
                        <div>
                            <label class="label" for="harga_jual">Harga Jual</label>
                            <input id="harga_jual" v-model.number="form.harga_jual" class="input" min="0" placeholder="Harga jual" type="number" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="stok">Jumlah Stok</label>
                            <input id="stok" v-model.number="form.stok" class="input" min="0" placeholder="Jumlah stok" type="number" required />
                        </div>
                        <div>
                            <label class="label" for="stok_minimum">Stok Minimum</label>
                            <input id="stok_minimum" v-model.number="form.stok_minimum" class="input" min="0" placeholder="Stok minimum" type="number" required />
                        </div>
                    </div>
                    <div>
                        <label class="label" for="tanggal_expired">Tanggal Kedaluwarsa</label>
                        <input id="tanggal_expired" v-model="form.tanggal_expired" class="input" type="date" required />
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button class="btn-primary flex-1" type="submit">
                            <Save class="h-4 w-4" />
                            Simpan
                        </button>
                        <button class="btn-muted" type="button" @click="reset">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </form>

            <section class="overflow-x-auto rounded-md border border-slate-200 bg-white">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Daftar Obat</h2>
                        <p class="text-xs text-slate-500">Cari berdasarkan kode, nama, atau kategori obat.</p>
                    </div>
                    <form class="flex flex-col gap-2 sm:flex-row md:w-96" @submit.prevent="searchMedicines">
                        <div class="flex-1">
                            <label class="label" for="search_query">Cari Obat</label>
                            <input id="search_query" v-model="searchQuery" class="input" placeholder="Cari obat..." />
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
                            <th class="px-3 py-3">Kode</th>
                            <th class="px-3 py-3">Obat</th>
                            <th class="px-3 py-3">Stok</th>
                            <th class="px-3 py-3">Harga</th>
                            <th class="px-3 py-3">Expired</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="medicinesLoading">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="6">Memuat data obat...</td>
                        </tr>
                        <tr v-else-if="medicinesError">
                            <td class="px-3 py-8 text-center text-rosewood" colspan="6">{{ medicinesError }}</td>
                        </tr>
                        <tr v-else-if="medicines.length === 0">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="6">Belum ada data obat.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="item in medicines" :key="item.id">
                                <td class="px-3 py-3 font-medium">{{ item.kode_obat }}</td>
                                <td class="px-3 py-3">{{ item.nama_obat }}</td>
                                <td class="px-3 py-3" :class="item.is_low_stock ? 'text-amber font-semibold' : ''">{{ item.stok }}</td>
                                <td class="px-3 py-3">Rp {{ Number(item.harga_jual).toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-3" :class="item.is_near_expired ? 'text-rosewood font-semibold' : ''">{{ item.tanggal_expired }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button class="btn-muted" title="Edit" type="button" @click="edit(item)"><Edit class="h-4 w-4" /></button>
                                        <button class="btn-danger" title="Hapus" type="button" @click="destroy(item)"><Trash2 class="h-4 w-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>
