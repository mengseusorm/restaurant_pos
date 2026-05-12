import axios from 'axios'
import appService from "../../services/appService";

export const orderPending = {
    namespaced: true,
    state: {
        lists: [], 
        page: {},
        pagination: {},  
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
    },
    actions: { 
        lists: function (context, payload) { 
            return new Promise((resolve, reject) => { 
                let url = 'admin/order-pending'; 
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
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/order-pending/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
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
    },
}
