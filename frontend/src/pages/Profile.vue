<script setup>
import { AlertCircle, CheckCircle2, Save, UserRound } from 'lucide-vue-next'
import { reactive, ref, watch } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const saving = ref(false)
const success = ref('')
const error = ref('')
const validationErrors = ref({})

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function fillForm() {
    form.name = auth.user?.name || ''
    form.email = auth.user?.email || ''
    form.password = ''
    form.password_confirmation = ''
}

function fieldError(field) {
    return validationErrors.value[field]?.[0]
}

async function submit() {
    success.value = ''
    error.value = ''
    validationErrors.value = {}
    saving.value = true

    const payload = {
        name: form.name,
        email: form.email,
    }

    if (form.password) {
        payload.password = form.password
        payload.password_confirmation = form.password_confirmation
    }

    try {
        const response = await auth.updateProfile(payload)
        success.value = response.message || 'Profil berhasil diperbarui.'
        form.password = ''
        form.password_confirmation = ''
    } catch (exception) {
        validationErrors.value = exception.response?.data?.errors || {}
        error.value = exception.response?.data?.message || 'Profil gagal diperbarui.'
    } finally {
        saving.value = false
    }
}

watch(() => auth.user, fillForm, { immediate: true })
</script>

<template>
    <AppLayout title="Edit Profil" subtitle="Perbarui nama, email, dan password akun.">
        <div class="max-w-3xl space-y-5">
            <div v-if="success" class="alert-success">
                <CheckCircle2 class="h-6 w-6 shrink-0 text-emerald-600" />
                <span>{{ success }}</span>
            </div>
            <div v-if="error" class="alert-danger">
                <AlertCircle class="h-6 w-6 shrink-0 text-rosewood" />
                <span>{{ error }}</span>
            </div>

            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <UserRound class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">{{ auth.user?.name || 'User' }}</h2>
                        <p class="mt-1 text-sm capitalize text-slate-500">{{ auth.user?.role?.replace('_', ' ') }}</p>
                    </div>
                </div>

                <form class="space-y-5 p-5" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Nama</span>
                            <input v-model="form.name" class="input mt-1" placeholder="Nama pengguna" required />
                            <span v-if="fieldError('name')" class="mt-1 block text-xs text-rosewood">{{ fieldError('name') }}</span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Email</span>
                            <input v-model="form.email" class="input mt-1" placeholder="email@apotek.test" type="email" required />
                            <span v-if="fieldError('email')" class="mt-1 block text-xs text-rosewood">{{ fieldError('email') }}</span>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Password Baru</span>
                            <input v-model="form.password" autocomplete="new-password" class="input mt-1" placeholder="Kosongkan jika tidak diganti" type="password" />
                            <span v-if="fieldError('password')" class="mt-1 block text-xs text-rosewood">{{ fieldError('password') }}</span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Konfirmasi Password</span>
                            <input v-model="form.password_confirmation" autocomplete="new-password" class="input mt-1" placeholder="Ulangi password baru" type="password" />
                        </label>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <button class="btn-muted" type="button" @click="fillForm">Reset</button>
                        <button class="btn-primary" :disabled="saving" type="submit">
                            <Save class="h-4 w-4" />
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>
