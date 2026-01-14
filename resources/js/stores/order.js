import { defineStore } from 'pinia';
import axios from 'axios';

export const useOrderStore = defineStore('order', {
    state: () => ({
        orders: [],
        currentOrder: null,
        loading: false,
        error: null,
        pagination: {},
    }),

    actions: {
        async fetchOrders(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/orders', { params });
                // Check if response is wrapped in 'data' (Laravel Resource standard)
                const responseData = response.data;
                this.orders = Array.isArray(responseData.data) ? responseData.data : (Array.isArray(responseData) ? responseData : []);
                this.pagination = responseData.meta || {};
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching orders';
            } finally {
                this.loading = false;
            }
        },

        async fetchOrder(id) {
            this.loading = true;
            try {
                const response = await axios.get(`/api/v1/orders/${id}`);
                this.currentOrder = response.data.data;
            } catch (error) {
                this.error = 'Error fetching order details';
            } finally {
                this.loading = false;
            }
        },

        async createOrder(payload) {
            try {
                const response = await axios.post('/api/v1/orders', payload);
                return response.data.data;
            } catch (error) {
                throw error;
            }
        },

        async updateStatus(id, status) {
            try {
                await axios.post(`/api/v1/orders/${id}/status`, { status });
                await this.fetchOrders(); // Refresh list
            } catch (error) {
                throw error;
            }
        },

        async updateOrder(id, payload) {
            try {
                const response = await axios.put(`/api/v1/orders/${id}`, payload);
                await this.fetchOrders();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteOrder(id) {
            try {
                await axios.delete(`/api/v1/orders/${id}`);
                await this.fetchOrders();
            } catch (error) {
                throw error;
            }
        }
    }
});
