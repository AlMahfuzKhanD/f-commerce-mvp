import { defineStore } from 'pinia';
import axios from 'axios';

export const useAttributeStore = defineStore('attribute', {
    state: () => ({
        sizes: [],
        colors: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchSizes() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/sizes');
                this.sizes = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch sizes';
            } finally {
                this.loading = false;
            }
        },

        async createSize(data) {
            try {
                const response = await axios.post('/api/v1/sizes', data);
                this.sizes.push(response.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteSize(id) {
            try {
                await axios.delete(`/api/v1/sizes/${id}`);
                this.sizes = this.sizes.filter(s => s.id !== id);
            } catch (error) {
                throw error;
            }
        },

        async fetchColors() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/colors');
                this.colors = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch colors';
            } finally {
                this.loading = false;
            }
        },

        async createColor(data) {
            try {
                const response = await axios.post('/api/v1/colors', data);
                this.colors.push(response.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteColor(id) {
            try {
                await axios.delete(`/api/v1/colors/${id}`);
                this.colors = this.colors.filter(c => c.id !== id);
            } catch (error) {
                throw error;
            }
        }
    }
});
