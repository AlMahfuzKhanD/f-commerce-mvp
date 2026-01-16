import { defineStore } from 'pinia';
import axios from 'axios';

export const useCategoryStore = defineStore('category', {
    state: () => ({
        categories: [],
        loading: false,
        error: null,
    }),
    actions: {
        async fetchCategories(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/categories', { params });
                this.categories = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching categories';
            } finally {
                this.loading = false;
            }
        },
        async createCategory(data) {
            await axios.post('/api/v1/categories', data);
            await this.fetchCategories();
        },
        async updateCategory(id, data) {
            await axios.put(`/api/v1/categories/${id}`, data);
            await this.fetchCategories();
        },
        async deleteCategory(id) {
            await axios.delete(`/api/v1/categories/${id}`);
            await this.fetchCategories();
        }
    }
});
