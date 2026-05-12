import axios from "axios";

export const telegramMiniAppItemCategory = {
    namespaced: true,
    state: {
        lists: [],
        show: {},
        pagination: {},
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        pagination: function (state) {
            return state.pagination;
        },
        show: function (state) {
            return state.show;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "telegram-mini-app/item-category";
                if (payload) {
                    url = url + "?" + new URLSearchParams(payload);
                }
                axios.get(url).then((res) => {
                    if (typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit("lists", res.data.data);
                        context.commit("pagination", res.data);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`telegram-mini-app/item-category/show/${payload.slug}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            return new Promise((resolve) => {
                context.commit("reset");
                resolve();
            });
        }
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        show: function (state, payload) {
            state.show = payload;
        },
        reset: function (state) {
            state.lists = [];
            state.show = {};
            state.pagination = [];
        }
    }
};