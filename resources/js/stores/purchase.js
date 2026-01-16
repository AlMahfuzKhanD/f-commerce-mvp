import { defineStore } from 'pinia';
import axios from 'axios';

export const usePurchaseStore = defineStore('purchase', {
    state: () => ({
        purchases: [],
        pagination: {},
        loading: false,
        error: null,
    }),

    actions: {
        async fetchPurchases(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/purchases', { params });
                this.purchases = response.data.data;
                if (response.data.per_page) {
                     this.pagination = response.data;
                } else if (response.data.meta) {
                    this.pagination = response.data.meta;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching purchases';
            } finally {
                this.loading = false;
            }
        },

        async createPurchase(payload) {
            try {
                const response = await axios.post('/api/v1/purchases', payload);
                await this.fetchPurchases();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async updatePurchase(id, payload) {
             try {
                const response = await axios.put(`/api/v1/purchases/${id}`, payload);
                await this.fetchPurchases();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deletePurchase(id) {
            // Confirmation handled in component
            try {
                await axios.delete(`/api/v1/purchases/${id}`);
                await this.fetchPurchases();
            } catch (error) {
                throw error;
            }
        },

        async addPayment(id, amount) {
            try {
                await axios.post(`/api/v1/purchases/${id}/payment`, { amount });
                await this.fetchPurchases();
            } catch (error) {
                throw error;
            }
        }
    }
});
