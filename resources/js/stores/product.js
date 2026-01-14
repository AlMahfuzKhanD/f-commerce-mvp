import { defineStore } from 'pinia';
import axios from 'axios';

export const useProductStore = defineStore('product', {
    state: () => ({
        products: [],
        loading: false,
        error: null,
        pagination: {},
    }),

    actions: {
        async fetchProducts(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/products', { params });
                this.products = response.data.data;
                 if (response.data.per_page) {
                     this.pagination = response.data;
                } else if (response.data.meta) {
                    this.pagination = response.data.meta;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching products';
            } finally {
                this.loading = false;
            }
        },

        async createProduct(data) {
            try {
                await axios.post('/api/v1/products', data);
                await this.fetchProducts(); // Refresh list
                return true;
            } catch (error) {
                throw error; // Let component handle validation errors
            }
        },

        async updateProduct(id, data) {
            try {
                await axios.put(`/api/v1/products/${id}`, data);
                await this.fetchProducts();
                return true;
            } catch (error) {
                throw error;
            }
        },

        async deleteProduct(id) {
            if (!confirm('Are you sure?')) return;
            try {
                await axios.delete(`/api/v1/products/${id}`);
                await this.fetchProducts();
            } catch (error) {
                alert('Failed to delete product');
            }
        }
    }
});
