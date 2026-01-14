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
                 if (response.data.per_page) {
                     this.pagination = response.data;
                } else if (response.data.meta) {
                    this.pagination = response.data.meta;
                }
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
        },

        async updateCustomer(id, data) {
            try {
                await axios.put(`/api/v1/customers/${id}`, data);
                await this.fetchCustomers();
            } catch (error) {
                throw error;
            }
        },

        async deleteCustomer(id) {
            try {
                await axios.delete(`/api/v1/customers/${id}`);
                await this.fetchCustomers();
            } catch (error) {
                throw error;
            }
        }
    }
});
