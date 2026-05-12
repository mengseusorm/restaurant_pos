import axios from "axios";
import appService from "../../../services/appService";

export const telegramMiniAppPage = {
    namespaced: true,
    state: {
        lists: [],
        show: {},
        pageInfo: {}
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        show: function (state) {
            return state.show;
        },
        pageInfo: function(state) {
            return state.pageInfo;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "telegram-mini-app/page";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("lists", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`telegram-mini-app/page/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        pageInfo: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`telegram-mini-app/page/page-info/${payload}`).then((res) => {
                    context.commit("pageInfo", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            context.commit("reset");
        }
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },
        show: function (state, payload) {
            state.show = payload;
        },
        pageInfo: function (state, payload) {
            state.pageInfo = payload;
        },
        reset: function (state) {
            state.lists = [];
            state.show = {};
            state.pageInfo = {};
        }
    },
};