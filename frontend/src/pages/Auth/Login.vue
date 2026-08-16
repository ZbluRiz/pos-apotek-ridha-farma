<script setup>
import { AlertCircle, LogIn } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const error = ref('')
const form = reactive({
    email: '',
    password: '',
})

async function submit() {
    error.value = ''
    try {
        await auth.login(form)
        router.push('/')
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Login gagal.'
    }
}
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
        <form autocomplete="off" class="w-full max-w-sm rounded-md border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
            <h1 class="text-xl font-semibold text-ink">POS Apotek</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk ke panel admin</p>

            <div class="mt-6 space-y-4">
                <label class="block">
                    <span class="text-sm font-medium">Email</span>
                    <input v-model="form.email" autocomplete="new-password" class="input mt-1" name="pos_login_email" type="email" required />
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Password</span>
                    <input v-model="form.password" autocomplete="new-password" class="input mt-1" name="pos_login_secret" type="password" required />
                </label>
                <div v-if="error" class="alert-danger">
                    <AlertCircle class="h-6 w-6 shrink-0 text-rosewood" />
                    <span>{{ error }}</span>
                </div>
                <button class="btn-primary w-full" :disabled="auth.loading">
                    <LogIn class="h-4 w-4" />
                    Login
                </button>
            </div>
        </form>
    </main>
</template>
