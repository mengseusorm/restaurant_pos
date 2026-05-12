import axios from 'axios'
import appService from "../../services/appService"

export const branchDailySaleReport = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: {},
        show: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
    },

    getters: {
        lists: function (state) {
            return state.lists;
        },
        pagination: function (state) {
            return state.pagination;
        },
        page: function (state) {
            return state.page;
        },
        show: function (state) {
            return state.show;
        },
        temp: function (state) {
            return state.temp;
        }
    },

    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/branch-daily-sale-report";
                
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }

                axios.get(url).then((res) => {
                    if (typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit("lists", res.data.data);
                        context.commit("page", res.data.meta);
                        context.commit("pagination", res.data);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/branch-daily-sale-report/export";
                
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }

                axios.get(url, { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        pdf: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/branch-daily-sale-report/pdf";
                
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }

                axios.get(url, { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/branch-daily-sale-report/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        temp: function (context, payload) {
            context.commit("temp", payload);
        }
    },

    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },

        pagination: function (state, payload) {
            state.pagination = payload;
        },

        page: function (state, payload) {
            state.page = payload;
        },

        show: function (state, payload) {
            state.show = payload;
        },

        temp: function (state, payload) {
            state.temp = payload;
        }
    }
};
