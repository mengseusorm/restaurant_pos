import axios from 'axios'


export const huionePayment = {
    namespaced: true,
    state: {
        show: {},
        orderItems: {},
        orderBranch: {},
        orderUser: {},

        // Add any additional state properties needed for Huione payment
        paymentData: null, // Placeholder for payment data
        paymentError: null, // Placeholder for payment error
        paymentStatus: null, // Placeholder for payment status
        paymentLoading: false, // Placeholder for loading state
        paymentSuccess: false, // Placeholder for success state
        paymentErrorMessage: null, // Placeholder for error message
        paymentMethod: 'huione', //
    },
    getters: {
        show: function (state) {
            return state.show;
        },
        orderItems: function (state) {
            return state.orderItems;
        },
        orderBranch: function (state) {
            return state.orderBranch;
        },
        orderUser: function (state) {
            return state.orderUser;
        }
    },
    actions: {
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post("table/dining-order", payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`table/dining-order/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    context.commit("orderItems", res.data.data.order_items);
                    context.commit("orderBranch", res.data.data.branch);
                    context.commit("orderUser", res.data.data.user);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        placeOrder: function (context, payload) {
            //TODO: Implement the logic to place an order using Huione payment
            // Return a payment data like : qrCode, scheme, fee, ...

            // payload is orderId

            console.log("Placing order with Huione payment for order ID:", payload);

            return new Promise((resolve, reject) => {
                axios.post(`huione-payment/${payload}/place-order`, payload).then((res) => {
                    console.log("Place order response:", res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }, 
        paymentStatus: function (context, payload) { 

            console.log("Checking payment status for order ID:", payload);
            return new Promise((resolve, reject) => {
                axios.get(`huione-payment/${payload}/payment-status`, payload).then((res) => {
                    // console.log("Payment status response:", res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }
    },
    mutations: {
        show: function (state, payload) {
            state.show = payload;
        },
        orderItems: function (state, payload) {
            state.orderItems = payload;
        },
        orderBranch: function (state, payload) {
            state.orderBranch = payload;
        },
        orderUser: function (state, payload) {
            state.orderUser = payload;
        }
    },
}
