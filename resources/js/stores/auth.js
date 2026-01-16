import { defineStore } from 'pinia';
import axios from 'axios';
import router from '../router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        errors: [],
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        can: (state) => (permission) => {
            if (!state.user || !state.user.all_permissions) return false;
            if (state.user.all_permissions.includes('*')) return true;
            return state.user.all_permissions.includes(permission);
        }
    },

    actions: {
        async login(credentials) {
            this.errors = [];
            try {
                // 1. Get CSRF Cookie (Sanctum)
                await axios.get('/sanctum/csrf-cookie');

                // 2. Login Request
                const response = await axios.post('/api/v1/auth/login', credentials);

                // 3. Store Token and User
                this.token = response.data.data.token;
                this.user = response.data.data.user;

                localStorage.setItem('token', this.token);

                // Configure axios defaults
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                // 4. Redirect
                router.push({ name: 'Dashboard' });

            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    console.error('Login error:', error);
                }
            }
        },

        async logout() {
            try {
                await axios.post('/api/v1/auth/logout');
            } catch (error) {
                console.error('Logout error', error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
                router.push({ name: 'Login' });
            }
        },

        async fetchUser() {
            if (!this.token) return;
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/api/v1/auth/me');
                this.user = response.data.data;
            } catch (error) {
                this.logout();
            }
        }
    }
});
