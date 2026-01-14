import { defineStore } from 'pinia';
import axios from 'axios';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        customers: [],
        loading: false,
        error: null,
        pagination: {},
    }),

    actions: {
        async fetchCustomers(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/customers', { params });
                this.customers = response.data.data;
                this.pagination = response.data.meta || {};
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching customers';
            } finally {
                this.loading = false;
            }
        },

        async createCustomer(data) {
            try {
                await axios.post('/api/v1/customers', data);
                await this.fetchCustomers();
                return true;
            } catch (error) {
                throw error;
            }
        },

        async searchCustomers(query) {
            if (!query) return [];
            const response = await axios.get('/api/v1/customers', { params: { phone: query } });
            return response.data.data;
        }
    }
});
