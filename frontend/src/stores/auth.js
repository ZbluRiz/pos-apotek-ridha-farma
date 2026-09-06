import { defineStore } from 'pinia'
import axios from 'axios'
import http, { apiOrigin } from '../api/http'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        initialized: false,
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.user),
        isSuperAdmin: (state) => state.user?.role === 'super_admin',
    },
    actions: {
        async csrf() {
            await axios.get(`${apiOrigin}/sanctum/csrf-cookie`, {
                withCredentials: true,
                headers: {
                    Accept: 'application/json',
                },
            })
        },
        async login(credentials) {
            this.loading = true
            try {
                await this.csrf()
                const { data } = await http.post('/auth/login', credentials)
                this.user = data.data.user
                this.initialized = true
            } finally {
                this.loading = false
            }
        },
        async loadUser() {
            const { data } = await http.get('/auth/me')
            this.user = data.data
        },
        async updateProfile(payload) {
            const { data } = await http.put('/auth/profile', payload)
            this.user = data.data
            return data
        },
        async initialize() {
            if (this.initialized) {
                return
            }

            try {
                await this.loadUser()
            } catch {
                this.user = null
            } finally {
                this.initialized = true
            }
        },
        async logout() {
            try {
                await this.csrf()
                await http.post('/auth/logout')
            } finally {
                this.user = null
                this.initialized = true
            }
        },
    },
})
