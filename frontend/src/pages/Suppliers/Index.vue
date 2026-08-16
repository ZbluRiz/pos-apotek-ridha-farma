<script setup>
import { AlertCircle, CheckCircle2, Edit, Save, Trash2 } from 'lucide-vue-next'
import { nextTick, onMounted, reactive, ref } from 'vue'
import http from '../../api/http'
import AppLayout from '../../layouts/AppLayout.vue'

const suppliers = ref([])
const editingId = ref(null)
const loading = ref(true)
const error = ref('')
const actionMessage = ref('')
const actionError = ref('')
const form = reactive({ nama_supplier: '', alamat: '', telepon: '' })

function rows(payload) {
    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    if (Array.isArray(payload?.data?.data)) {
        return payload.data.data
    }

    return []
}

async function load() {
    loading.value = true
    error.value = ''

    try {
        const { data } = await http.get('/suppliers', {
            params: { per_page: 100 },
        })
        suppliers.value = rows(data)
        await nextTick()
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Data supplier gagal dimuat.'
    } finally {
        loading.value = false
    }
}

function edit(item) {
    editingId.value = item.id
    Object.assign(form, item)
}

function reset() {
    editingId.value = null
    Object.assign(form, { nama_supplier: '', alamat: '', telepon: '' })
}

async function submit() {
    actionMessage.value = ''
    actionError.value = ''

    try {
        editingId.value ? await http.put(`/suppliers/${editingId.value}`, form) : await http.post('/suppliers', form)
        actionMessage.value = editingId.value ? 'Data supplier berhasil diperbarui.' : 'Data supplier berhasil ditambahkan.'
        reset()
        await load()
    } catch (exception) {
        actionError.value = exception.response?.data?.message || 'Data supplier gagal disimpan.'
    }
}

async function destroy(item) {
    actionMessage.value = ''
    actionError.value = ''

    if (!confirm(`Hapus supplier ${item.nama_supplier}?`)) {
        return
    }

    try {
        await http.delete(`/suppliers/${item.id}`)
        actionMessage.value = 'Data supplier berhasil dihapus.'
        await load()
    } catch (exception) {
        actionError.value = exception.response?.data?.message || 'Data supplier gagal dihapus.'
    }
}

onMounted(load)
</script>

<template>
    <AppLayout>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Supplier</h1>
            <p class="text-sm text-slate-500">Kelola pemasok obat.</p>
        </div>

        <div v-if="actionMessage" class="alert-success">
            <CheckCircle2 class="h-6 w-6 shrink-0 text-emerald-600" />
            <span>{{ actionMessage }}</span>
        </div>
        <div v-if="actionError" class="alert-danger">
            <AlertCircle class="h-6 w-6 shrink-0 text-rosewood" />
            <span>{{ actionError }}</span>
        </div>

        <div class="grid gap-5 lg:grid-cols-[340px_1fr]">
            <form class="rounded-md border border-slate-200 bg-white p-4" @submit.prevent="submit">
                <div class="space-y-3.5">
                    <div>
                        <label class="label" for="nama_supplier">Nama Supplier</label>
                        <input id="nama_supplier" v-model="form.nama_supplier" class="input" placeholder="Nama supplier" required />
                    </div>
                    <div>
                        <label class="label" for="alamat">Alamat</label>
                        <textarea id="alamat" v-model="form.alamat" class="input min-h-24" placeholder="Alamat supplier"></textarea>
                    </div>
                    <div>
                        <label class="label" for="telepon">No. Telepon</label>
                        <input id="telepon" v-model="form.telepon" class="input" placeholder="Telepon (cth: 0812...)" />
                    </div>
                    <button class="btn-primary w-full" type="submit">
                        <Save class="h-4 w-4" />
                        Simpan
                    </button>
                </div>
            </form>

            <section class="overflow-x-auto rounded-md border border-slate-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Nama</th>
                            <th class="px-3 py-3">Telepon</th>
                            <th class="px-3 py-3">Obat</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="4">Memuat data supplier...</td>
                        </tr>
                        <tr v-else-if="error">
                            <td class="px-3 py-8 text-center text-rosewood" colspan="4">{{ error }}</td>
                        </tr>
                        <tr v-else-if="suppliers.length === 0">
                            <td class="px-3 py-8 text-center text-slate-500" colspan="4">Belum ada data supplier.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="item in suppliers" :key="item.id">
                                <td class="px-3 py-3 font-medium">{{ item.nama_supplier }}</td>
                                <td class="px-3 py-3">{{ item.telepon }}</td>
                                <td class="px-3 py-3">{{ item.medicines_count || 0 }}</td>
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
