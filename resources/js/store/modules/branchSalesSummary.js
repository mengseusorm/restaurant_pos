import axios from "axios";

export const branchSalesSummary = {
    namespaced: true,
    state: {
        reportData: null,
        loading: false,
        error: null
    },
    getters: {
        reportData: (state) => state.reportData,
        loading: (state) => state.loading,
        error: (state) => state.error,
        hasData: (state) => state.reportData !== null
    },
    actions: {
        async fetchReport(context, payload) {
            try {
                context.commit('setLoading', true);
                context.commit('setError', null);
                const params = {
                    branch_id: payload.branch_id,
                    from_date: payload.from_date || null,
                    to_date: payload.to_date || null
                };
 
                const response = await axios.get('/admin/branch-sales-summary-report', { params });

                context.commit('setReportData', response.data.data);
                context.commit('setLoading', false);

                return response;
            } catch (error) {
                console.error('API Error:', error);
                context.commit('setError', error.response?.data?.message || 'Failed to load report data');
                context.commit('setLoading', false);
                context.commit('setReportData', null);
                throw error;
            }
        },

        async exportExcel(context, payload) {
            try {
                context.commit('setLoading', true);
                const params = {
                    branch_id: payload.branch_id,
                    from_date: payload.from_date || null,
                    to_date: payload.to_date || null
                };

                const response = await axios.get('/admin/branch-sales-summary-report/export', {
                    params,
                    responseType: 'blob'
                });

                // Create download link
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'branch-sales-summary-report.xlsx');
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                context.commit('setLoading', false);
                return response;

            } catch (error) {
                context.commit('setLoading', false);
                throw error;
            }
        },

        async exportPdf(context, payload) {
            try {
                context.commit('setLoading', true);
                const params = {
                    branch_id: payload.branch_id,
                    from_date: payload.from_date || null,
                    to_date: payload.to_date || null
                };

                const response = await axios.get('/admin/branch-sales-summary-report/pdf', {
                    params,
                    responseType: 'blob'
                });

                // Create download link
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'branch-sales-summary-report.pdf');
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                context.commit('setLoading', false);
                return response;

            } catch (error) {
                context.commit('setLoading', false);
                throw error;
            }
        },

        clearReport(context) {
            context.commit('setReportData', null);
            context.commit('setError', null);
        },

        clearError(context) {
            context.commit('setError', null);
        }
    },
    mutations: {
        setReportData(state, payload) {
            state.reportData = payload;
        },
        setLoading(state, payload) {
            state.loading = payload;
        },
        setError(state, payload) {
            state.error = payload;
        }
    }
};
