import axios from 'axios';

export const onlineOrderHuionePayment = {
    namespaced: true,
    state: {

        // Add any additional state properties needed for Huione payment
        paymentData: null, // Placeholder for payment data
        // paymentError: null, // Placeholder for payment error
        // paymentStatus: null, // Placeholder for payment status
        // paymentLoading: false, // Placeholder for loading state
        // paymentSuccess: false, // Placeholder for success state
        // paymentErrorMessage: null, // Placeholder for error message
        paymentMethod: 'huione', //
    },
    getters: {
        paymentData: function (state) {
            return state.paymentData;
        },
        // paymentError: function (state) {
        //     return state.paymentError;
        // },
        // paymentStatus: function (state) {
        //     return state.paymentStatus;
        // },
        // paymentLoading: function (state) {
        //     return state.paymentLoading;
        // },
        // paymentSuccess: function (state) {
        //     return state.paymentSuccess;
        // },
        // paymentErrorMessage: function (state) {
        //     return state.paymentErrorMessage;
        // },
        paymentMethod: function (state) {
            return state.paymentMethod;
        },
    },
    actions: {
        placeOrder: function (context, payload) {
            //TODO: Implement the logic to place an order using Huione payment
            // Return a payment data like : qrCode, scheme, fee, ...

            // payload is orderId

            console.log('Placing order with Huione payment for order ID:', payload);

            return new Promise((resolve, reject) => {
                axios
                    .post(`huione-payment/${payload}/place-order`, payload)
                    .then((res) => {
                        console.log('Place order response:', res.data);
                        if (res.data.status === true && res.data.paymentOrder) {
                            context.commit('paymentData', res.data.paymentOrder);    
                        }
                        
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        paymentStatus: function (context, payload) {
            console.log('Checking payment status for order ID:', payload);
            return new Promise((resolve, reject) => {
                axios
                    .get(`huione-payment/${payload}/payment-status`, payload)
                    .then((res) => {
                        // console.log("Payment status response:", res.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
    },
    mutations: {
        paymentData: function (state, payload) {
            state.paymentData = payload;
        },
        // paymentError: function (state, payload) {
        //     state.paymentError = payload;
        // },
        // paymentStatus: function (state, payload) {
        //     state.paymentStatus = payload;
        // },
        // paymentLoading: function (state, payload) {
        //     state.paymentLoading = payload;
        // },
        // paymentSuccess: function (state, payload) {
        //     state.paymentSuccess = payload;
        // },
        // paymentErrorMessage: function (state, payload) {
        //     state.paymentErrorMessage = payload;
        // },
        paymentMethod: function (state, payload) {
            state.paymentMethod = payload;
        },
    },
};
