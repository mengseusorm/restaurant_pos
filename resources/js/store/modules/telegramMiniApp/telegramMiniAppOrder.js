import axios from 'axios'
import appService from '../../../services/appService';

export const telegramMiniAppOrder = {
    namespaced: true,
    state: {
        show: {},
        orderItems: {},
        orderBranch: {},
        orderUser: {},
        lists: [],
        pagination: {},
        orderCount: 0,
        telegramUserData: {
            telegram_user_id: null,
            telegram_chat_id: null,
            telegram_username: null,
        },
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
        },
        lists: function (state) {
            return state.lists;
        },
        pagination: function (state) {
            return state.pagination;
        },
        orderCount: function (state) {
            return state.orderCount;
        },
        telegramUserData: function (state) {
            return state.telegramUserData;
        }
    },
    actions: {
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                // Log the payload being sent to debug
                console.log('TelegramMiniAppOrder store - payload being sent:', payload);
                
                // Send payload as-is since components should include Telegram data explicitly
                axios.post("telegram-mini-app/order", payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                // Validate payload is not undefined or 'undefined'
                if (!payload || payload === 'undefined' || String(payload).trim() === '') {
                    reject(new Error('Order ID is required and cannot be undefined'));
                    return;
                }
                
                const orderId = String(payload).trim();
                console.log('Fetching order with ID:', orderId);
                
                axios.get(`telegram-mini-app/order/show/${orderId}`).then((res) => {
                    context.commit("show", res.data.data);
                    context.commit("orderItems", res.data.data.order_items);
                    context.commit("orderBranch", res.data.data.branch);
                    context.commit("orderUser", res.data.data.user);
                    resolve(res);
                }).catch((err) => {
                    console.error('Error fetching order:', err);
                    reject(err);
                });
            });
        },
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get("telegram-mini-app/order", { params: payload }).then((res) => {
                    context.commit("lists", res.data.data);
                    context.commit("pagination", res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        update: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.put(`telegram-mini-app/order/${payload.id}`, payload).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`telegram-mini-app/order/${payload}`).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get("telegram-mini-app/order/export", { params: payload }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        changeStatus: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.put(`telegram-mini-app/order/${payload.id}/change-status`, payload).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        payment: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`telegram-mini-app/order/${payload.id}/payment`, payload).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        setTelegramUserData: function (context, payload) {
            return new Promise((resolve, reject) => {
                try {
                    context.commit("telegramUserData", payload);
                    resolve(payload);
                } catch (err) {
                    reject(err);
                }
            });
        },
        getOrderCount: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`telegram-mini-app/order/count/${payload.telegram_user_id}`).then((res) => {
                    context.commit("orderCount", res.data.data.count);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        getUserOrders: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = `telegram-mini-app/order/user/${payload.telegram_user_id}`;
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
        },
        lists: function (state, payload) {
            state.lists = payload;
        },
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        orderCount: function (state, payload) {
            state.orderCount = payload;
        },
        telegramUserData: function (state, payload) {
            state.telegramUserData = { ...state.telegramUserData, ...payload };
        }
    }
};

export default telegramMiniAppOrder;