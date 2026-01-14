import { defineStore } from 'pinia';
import axios from 'axios';

export const useExpenseStore = defineStore('expense', {
    state: () => ({
        expenses: [],
        pagination: {},
        loading: false,
        error: null,
    }),

    actions: {
        async fetchExpenses(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/expenses', { params });
                this.expenses = response.data.data;
                 if (response.data.per_page) {
                     this.pagination = response.data;
                } else if (response.data.meta) {
                    this.pagination = response.data.meta;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching expenses';
            } finally {
                this.loading = false;
            }
        },

        async createExpense(payload) {
            try {
                const response = await axios.post('/api/v1/expenses', payload);
                await this.fetchExpenses();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

         async updateExpense(id, payload) {
            try {
                const response = await axios.put(`/api/v1/expenses/${id}`, payload);
                await this.fetchExpenses();
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteExpense(id) {
            try {
                await axios.delete(`/api/v1/expenses/${id}`);
                await this.fetchExpenses();
            } catch (error) {
               alert('Failed: ' + (error.response?.data?.message || 'Error deleting expense'));
            }
        }
    }
});
