import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { adminApi } from '@/api'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('token'))
    const user = ref(JSON.parse(localStorage.getItem('user')) || null)

    const isLoggedIn = computed(() => !!token.value)
    const isAdmin = computed(() => user.value?.role === 'admin')

    async function login(payload) {
        const { data } = await adminApi.login(payload)
        token.value = data.token
        user.value = data.user
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
    }

    async function fetchUser() {
        const { data } = await adminApi.me()
        user.value = data.user ?? data
        localStorage.setItem('user', JSON.stringify(user.value))
    }

    async function logout() {
        try {
            await adminApi.logout()
        } catch {
            // token may already be invalid, ignore the error
        }
        clear()
    }

    function clear() {
        token.value = null
        user.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    return { token, user, isLoggedIn, isAdmin, login, fetchUser, logout, clear }
})