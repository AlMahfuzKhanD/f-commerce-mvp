import { defineStore } from 'pinia';
import axios from 'axios';

export const useSupplierStore = defineStore('supplier', {
    state: () => ({
        suppliers: [],
        pagination: {},
        loading: false,
        error: null,
    }),

    actions: {
        async fetchSuppliers(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/suppliers', { params });
                this.suppliers = response.data.data;
                this.pagination = response.data; // Pagination meta is on root for simple paginate(), check controller return
                // Check if it's Paginator resource
                if (response.data.per_page) {
                     this.pagination = response.data;
                } else if (response.data.meta) {
                    this.pagination = response.data.meta;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching suppliers';
            } finally {
                this.loading = false;
            }
        },

        async createSupplier(payload) {
            try {
                const response = await axios.post('/api/v1/suppliers', payload);
                await this.fetchSuppliers(); // Refresh list
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async updateSupplier(id, payload) {
             try {
                const response = await axios.put(`/api/v1/suppliers/${id}`, payload);
                await this.fetchSuppliers();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteSupplier(id) {
            try {
                await axios.delete(`/api/v1/suppliers/${id}`);
                await this.fetchSuppliers();
            } catch (error) {
                throw error;
            }
        }
    }
});
