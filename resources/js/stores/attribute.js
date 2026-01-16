import { defineStore } from 'pinia';

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
                const response = await window.axios.get('/api/v1/sizes');
                this.sizes = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch sizes';
            } finally {
                this.loading = false;
            }
        },

        async createSize(data) {
            try {
                const response = await window.axios.post('/api/v1/sizes', data);
                this.sizes.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create size';
                throw error;
            }
        },

        async updateSize(id, data) {
            try {
                const response = await window.axios.put(`/api/v1/sizes/${id}`, data);
                const index = this.sizes.findIndex(s => s.id === id);
                if (index !== -1) {
                    this.sizes[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update size';
                throw error;
            }
        },

        async deleteSize(id) {
            try {
                await window.axios.delete(`/api/v1/sizes/${id}`);
                this.sizes = this.sizes.filter(s => s.id !== id);
            } catch (error) {
                 this.error = error.response?.data?.message || 'Failed to delete size';
                throw error;
            }
        },

        async fetchColors() {
            this.loading = true;
            try {
                const response = await window.axios.get('/api/v1/colors');
                this.colors = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch colors';
            } finally {
                this.loading = false;
            }
        },

        async createColor(data) {
            try {
                const response = await window.axios.post('/api/v1/colors', data);
                this.colors.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create color';
                throw error;
            }
        },

        async updateColor(id, data) {
             try {
                const response = await window.axios.put(`/api/v1/colors/${id}`, data);
                const index = this.colors.findIndex(c => c.id === id);
                if (index !== -1) {
                    this.colors[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update color';
                throw error;
            }
        },

        async deleteColor(id) {
            try {
                await window.axios.delete(`/api/v1/colors/${id}`);
                this.colors = this.colors.filter(c => c.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete color';
                throw error;
            }
        }
    }
});
