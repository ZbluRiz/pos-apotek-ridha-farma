<script setup>
import { ActivitySquare, Boxes, ChevronDown, FileBarChart, FileText, LayoutDashboard, LogOut, Menu, Pill, ReceiptText, ShieldCheck, Truck, UserRound, X } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

defineProps({
    title: String,
    subtitle: String,
})

const auth = useAuthStore()
const router = useRouter()
const sidebarOpen = ref(false)

const menus = [
    { name: 'Dashboard', to: '/', icon: LayoutDashboard },
    { name: 'Obat', to: '/obat', icon: Boxes },
    { name: 'Supplier', to: '/supplier', icon: Truck, superAdminOnly: true },
    { name: 'Pembelian', to: '/pembelian', icon: FileText },
    { name: 'Penjualan', to: '/penjualan', icon: ReceiptText },
    { name: 'Laporan', to: '/laporan', icon: FileBarChart },
    { name: 'SAW Restock', to: '/saw-restock', icon: ActivitySquare },
]

const visibleMenus = computed(() => menus.filter((menu) => !menu.superAdminOnly || auth.isSuperAdmin))

async function logout() {
    await auth.logout()
    router.push('/login')
}

function initials(name) {
    return (name || 'User')
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase()
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-slate-950/30 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-20 items-center justify-between px-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-600 text-white">
                        <Pill class="h-5 w-5" />
                    </div>
                    <p class="text-lg font-bold text-slate-900">Ridha Farma</p>
                </div>
                <button class="btn-muted lg:hidden" title="Tutup menu" @click="sidebarOpen = false">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <nav class="flex-1 space-y-2 px-3 py-5">
                <RouterLink
                    v-for="menu in visibleMenus"
                    :key="menu.to"
                    :to="menu.to"
                    class="flex min-h-12 items-center gap-4 rounded-lg px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                    active-class="bg-emerald-50 text-emerald-700"
                    @click="sidebarOpen = false"
                >
                    <component :is="menu.icon" class="h-5 w-5" />
                    {{ menu.name }}
                </RouterLink>
            </nav>

            <div class="m-4 rounded-lg border border-slate-200 p-4">
                <div class="flex gap-3">
                    <ShieldCheck class="h-6 w-6 shrink-0 text-emerald-600" />
                    <div>
                        <p class="text-xs font-semibold text-slate-800">Sistem aman &amp; terproteksi</p>
                        <p class="mt-1 text-[11px] leading-4 text-slate-500">Data disimpan dengan autentikasi dan kontrol akses.</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex min-h-20 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6">
                <div class="flex items-center gap-3">
                    <button class="btn-muted lg:hidden" title="Buka menu" @click="sidebarOpen = true">
                        <Menu class="h-5 w-5" />
                    </button>
                    <div v-if="title">
                        <h1 class="text-xl font-bold text-slate-900">{{ title }}</h1>
                        <p class="mt-0.5 text-sm text-slate-500">{{ subtitle }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <RouterLink to="/profile" class="hidden items-center gap-3 rounded-md px-2 py-1 transition hover:bg-slate-100 sm:flex" title="Edit Profil">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">
                            {{ initials(auth.user?.name) }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold text-slate-900">{{ auth.user?.name }}</p>
                            <p class="mt-1 text-xs capitalize text-slate-500">{{ auth.user?.role?.replace('_', ' ') }}</p>
                        </div>
                        <ChevronDown class="h-4 w-4 text-slate-400" />
                    </RouterLink>
                    <RouterLink to="/profile" class="btn-muted sm:hidden" title="Edit Profil">
                        <UserRound class="h-4 w-4" />
                    </RouterLink>
                    <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>
                    <button class="btn-muted" title="Logout" @click="logout">
                        <LogOut class="h-4 w-4" />
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </header>

            <main class="p-4 lg:p-6">
                <slot />
            </main>

            <footer class="flex flex-col gap-2 border-t border-slate-200 bg-white px-6 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <span>&copy; 2026 Ridha Farma. Semua hak dilindungi.</span>
                <span class="inline-flex items-center gap-2">Versi 1.0.0 <ShieldCheck class="h-4 w-4 text-emerald-600" /></span>
            </footer>
        </div>
    </div>
</template>
