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
            // Confirmation handled in component
            try {
                await axios.delete(`/api/v1/products/${id}`);
                // Do NOT fetch here, let component handle it to ensure pagination state is respected or allow component to decide.
                // Or better: ensure we await this properly.
                // actually, fetchProducts call inside here is fine IF we await it.
                // Issue might be mixed calls.
                // Let's RETURN the promise so component awaits it.
            } catch (error) {
                // throw so component knows it failed
                 throw error;
            }
        }
    }
});
