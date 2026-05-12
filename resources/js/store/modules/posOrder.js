import axios from 'axios'
import appService from "../../services/appService";

export const posOrder = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: {},
        show: {},
        orderItems: {},
        orderBranch: {},
        orderUser: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
        pendingCount: 0,
    },
    getters: {
        lists: function (state) {
            return state.lists;
        }, 
        pagination: function (state) {
            return state.pagination
        },
        page: function(state) {
            return state.page;
        },
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
        },
        temp: function (state) {
            return state.temp;
        },
        addOrderShow: function (state) {
            return state.addOrderShow;
        },
        pendingCount: function (state) {
            return state.pendingCount;
        },
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/pos-order';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit('lists', res.data.data);
                        context.commit('page', res.data.meta);
                        context.commit('pagination', res.data);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                if(payload.id != null){  // if add menu item to existing order
                    axios.post("admin/pos/addOrderStore", payload).then((res) => {
                        context.commit('addOrderShow', res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                } else {
                    axios.post("admin/pos", payload).then((res) => {
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },

        checkPaymentOrderStatus: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'huione-payment/check-payment-order-status';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        // PayWay Integration Actions
        initiatePayWayQRPayment: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/payway/generate-qr', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        checkPayWayTransactionStatus: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/payway/check-transaction', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        cancelPayWayTransaction: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/payway/close-transaction', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },



        updateOrderItem: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/pos-order/order-item/${payload.orderItem.id}`, payload.orderItem).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        destroyPosItemOrder: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/pos-order/order-item/${payload.orderItemId}`, { data: { reason: payload.reason } }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/pos-order/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    context.commit("orderItems", res.data.data.order_items);
                    // context.commit("orderItems", res.data.data.order_items_unique);
                    context.commit("orderBranch", res.data.data.branch);
                    context.commit("orderUser", res.data.data.user);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        updateOrderInfo: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/pos-order/order-info/${payload.id}`, payload.order).then((res) => {
                    // context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        destroy: function (context, payload) {
            // return new Promise((resolve, reject) => {
            //     axios.delete(`admin/pos-order/${payload.id}`).then((res) => {
            //         // context.dispatch('lists', payload.search).then().catch();
            //         resolve(res);
            //     }).catch((err) => {
            //         reject(err);
            //     });
            // });
            return new Promise((resolve, reject) => {
                axios.delete(`admin/pos-order/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        changeStatus: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/pos-order/change-status/${payload.id}`,payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        payOrder: function (context, payload) { //payload will include all necessary data to pay order
            return new Promise((resolve, reject) => {
                axios.post(`admin/pos-order/pay-order`,payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        changePaymentStatus: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/pos-order/change-payment-status/${payload.id}`,payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        changePaymentMethod:function (context,payload){
            return new Promise((resolve, reject) => {
                axios.post(`admin/pos-order/change-payment-method/${payload.id}`,payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        addDiningTable: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/pos-order/add-dining-table/${payload.id}`, payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        // releaseDiningTable_: function (context, payload) {
        //     return new Promise((resolve, reject) => {
        //         axios.post(`admin/pos-order/release-dining-table/${payload.id}`, payload).then((res) => {
        //             context.commit('show', res.data.data);
        //             resolve(res);
        //         }).catch((err) => {
        //             reject(err);
        //         });
        //     });
        // },
        reset: function (context) {
            context.commit('reset');
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/pos-order/export';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url, {responseType: 'blob'}).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        saveCustomer: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = "/admin/pos/customer";
                method(url, payload.form)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        setMember: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = `/admin/pos-order/${payload.id}/set-member`;
                method(url, payload)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        removeMember: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = `/admin/pos-order/${payload.id}/remove-member`;
                method(url, payload)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        discount: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = `admin/pos-order/discount/${payload.id}`;
                method(url, payload)
                    .then((res) => {
                        context.commit('show', res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        countPending: function (context) { // All order pending count
            return new Promise((resolve, reject) => {
                axios.get('admin/pos-order/count-pending').then((res) => {
                    if (res.data.status) {
                        context.commit('pendingCount', res.data.data.count);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }, 
        combineOrders: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/pos-order/combine', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        transferItems: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/pos-order/transfer-items', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        refundTransaction: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/payway/refund', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        voidTransaction: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/transactions/void', payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        }, 
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        page: function (state, payload) {
            if(typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                }
            }
        },
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
        },
        reset: function(state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        },
        addOrderShow: function (state, payload) {
            state.addOrderShow = payload;
        },
        pendingCount: function (state, payload) {
            state.pendingCount = payload;
        },
    },
}
