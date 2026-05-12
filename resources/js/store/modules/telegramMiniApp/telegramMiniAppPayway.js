import axios from 'axios'

export const telegramMiniAppPayway = {
    namespaced: true,
    state: {
        currentTransaction: null,
        qrData: null,
        paymentStatus: null,
        isLoading: false,
        error: null,
    },
    getters: {
        currentTransaction: function (state) {
            return state.currentTransaction;
        },
        qrData: function (state) {
            return state.qrData;
        },
        paymentStatus: function (state) {
            return state.paymentStatus;
        },
        isLoading: function (state) {
            return state.isLoading;
        },
        error: function (state) {
            return state.error;
        },
        isPending: function (state) {
            return state.paymentStatus === 'PENDING' || state.currentTransaction?.payment_status_code === 2;
        },
        isApproved: function (state) {
            return state.paymentStatus === 'APPROVED' || state.currentTransaction?.payment_status_code === 0;
        },
        isCancelled: function (state) {
            return state.paymentStatus === 'CANCELLED' || state.currentTransaction?.payment_status_code === 7;
        },
    },
    actions: {
        generateQR: function (context, payload) {
            return new Promise((resolve, reject) => {
                context.commit('setLoading', true);
                context.commit('setError', null);

                axios.post('telegram-mini-app/payway/generate-qr', payload).then((res) => {
                    if (res.data.status.code === '0') {
                        context.commit('setQrData', res.data.data);
                        context.commit('setCurrentTransaction', {
                            tran_id: res.data.data.tran_id,
                            payment_status_code: 2,
                            payment_status: 'PENDING'
                        });
                        context.commit('setPaymentStatus', 'PENDING');
                    }
                    context.commit('setLoading', false);
                    resolve(res);
                }).catch((err) => {
                    context.commit('setLoading', false);
                    context.commit('setError', err.response?.data?.status?.message || 'Failed to generate QR');
                    reject(err);
                });
            });
        },

        checkTransaction: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('telegram-mini-app/payway/check-transaction', payload).then((res) => {
                    if (res.data.status.code === '00') {
                        context.commit('setCurrentTransaction', res.data.data);
                        context.commit('setPaymentStatus', res.data.data.payment_status);
                    }
                    resolve(res);
                }).catch((err) => {
                    context.commit('setError', err.response?.data?.status?.message || 'Failed to check transaction');
                    reject(err);
                });
            });
        },

        closeTransaction: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('telegram-mini-app/payway/close-transaction', payload).then((res) => {
                    if (res.data.status.code === '00') {
                        context.commit('setPaymentStatus', 'CANCELLED');
                        context.commit('setCurrentTransaction', {
                            ...context.state.currentTransaction,
                            payment_status_code: 7,
                            payment_status: 'CANCELLED'
                        });
                    }
                    resolve(res);
                }).catch((err) => {
                    context.commit('setError', err.response?.data?.status?.message || 'Failed to close transaction');
                    reject(err);
                });
            });
        },

        linkTransactionToOrder: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('telegram-mini-app/payway/link-transaction', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    context.commit('setError', err.response?.data?.status?.message || 'Failed to link transaction');
                    reject(err);
                });
            });
        },

        resetPayment: function (context) {
            context.commit('setCurrentTransaction', null);
            context.commit('setQrData', null);
            context.commit('setPaymentStatus', null);
            context.commit('setError', null);
            context.commit('setLoading', false);
        },
    },
    mutations: {
        setCurrentTransaction: function (state, payload) {
            state.currentTransaction = payload;
        },
        setQrData: function (state, payload) {
            state.qrData = payload;
        },
        setPaymentStatus: function (state, payload) {
            state.paymentStatus = payload;
        },
        setLoading: function (state, payload) {
            state.isLoading = payload;
        },
        setError: function (state, payload) {
            state.error = payload;
        },
    }
};

export default telegramMiniAppPayway;
