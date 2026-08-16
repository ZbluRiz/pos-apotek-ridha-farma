import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Login from '../pages/Auth/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import MedicinesIndex from '../pages/Medicines/Index.vue'
import PurchasesIndex from '../pages/Purchases/Index.vue'
import SuppliersIndex from '../pages/Suppliers/Index.vue'
import SalesIndex from '../pages/Sales/Index.vue'
import ReportsIndex from '../pages/Reports/Index.vue'
import SawIndex from '../pages/Saw/Index.vue'
import Profile from '../pages/Profile.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: Login },
        { path: '/', name: 'dashboard', component: Dashboard, meta: { auth: true } },
        { path: '/obat', name: 'medicines', component: MedicinesIndex, meta: { auth: true } },
        { path: '/supplier', name: 'suppliers', component: SuppliersIndex, meta: { auth: true, superAdmin: true } },
        { path: '/pembelian', name: 'purchases', component: PurchasesIndex, meta: { auth: true } },
        { path: '/penjualan', name: 'sales', component: SalesIndex, meta: { auth: true } },
        { path: '/laporan', name: 'reports', component: ReportsIndex, meta: { auth: true } },
        { path: '/saw-restock', name: 'saw', component: SawIndex, meta: { auth: true } },
        { path: '/profile', name: 'profile', component: Profile, meta: { auth: true } },
    ],
})

router.beforeEach(async (to) => {
    const auth = useAuthStore()
    await auth.initialize()

    if (to.meta.auth && !auth.isAuthenticated) {
        return { name: 'login' }
    }

    if (to.name === 'login' && auth.isAuthenticated) {
        return { name: 'dashboard' }
    }

    if (to.meta.superAdmin && !auth.isSuperAdmin) {
        return { name: 'dashboard' }
    }
})

export default router
