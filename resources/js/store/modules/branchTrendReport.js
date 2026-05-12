import axios from 'axios';

export const branchTrendReport = {
    namespaced: true,
    state: {
        trendData: {},
        summaryData: [],
        loading: false
    },
    getters: {
        trendData: state => state.trendData,
        summaryData: state => state.summaryData,
        loading: state => state.loading
    },
    actions: {
        getTrendData(context, payload) {
            context.commit('loading', true);
            return new Promise((resolve, reject) => {
                axios.get('/admin/branch-trend-report/trend-data', { params: payload })
                    .then((response) => {
                        context.commit('trendData', response.data);
                        context.commit('loading', false);
                        resolve(response);
                    })
                    .catch((error) => {
                        context.commit('loading', false);
                        reject(error);
                    });
            });
        },
        
        getSummaryData(context, payload) {
            context.commit('loading', true);
            return new Promise((resolve, reject) => {
                axios.get('/admin/branch-trend-report/summary-data', { params: payload })
                    .then((response) => {
                        context.commit('summaryData', response.data);
                        context.commit('loading', false);
                        resolve(response);
                    })
                    .catch((error) => {
                        context.commit('loading', false);
                        reject(error);
                    });
            });
        },
        
        getReportData(context, payload) {
            context.commit('loading', true);
            return new Promise((resolve, reject) => {
                axios.get('/admin/branch-trend-report', { params: payload })
                    .then((response) => {
                        context.commit('trendData', response.data.data.trend_data);
                        context.commit('summaryData', response.data.data.summary_data);
                        context.commit('loading', false);
                        resolve(response);
                    })
                    .catch((error) => {
                        context.commit('loading', false);
                        reject(error);
                    });
            });
        },
        
        export(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('/admin/branch-trend-report/export', {
                    params: payload,
                    responseType: 'blob'
                })
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
            });
        },
        
        pdf(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('/admin/branch-trend-report/pdf', {
                    params: payload,
                    responseType: 'blob'
                })
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
            });
        }
    },
    mutations: {
        trendData(state, payload) {
            state.trendData = payload;
        },
        
        summaryData(state, payload) {
            state.summaryData = payload;
        },
        
        loading(state, payload) {
            state.loading = payload;
        }
    }
};
