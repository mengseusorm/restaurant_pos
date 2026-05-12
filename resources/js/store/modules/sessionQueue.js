import axios from 'axios'
import appService from "../../services/appService";

export const sessionQueue = {
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
        lists:      (state) => state.lists,
        pagination: (state) => state.pagination,
        page:       (state) => state.page,
        show:       (state) => state.show,
        temp:       (state) => state.temp,
    },
    actions: {
        lists(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/session-queue';
                if (payload) { url = url + appService.requestHandler(payload); }
                axios.get(url).then((res) => {
                    if (typeof payload.vuex === 'undefined' || payload.vuex === true) {
                        context.commit('lists', res.data.data ?? []);
                        context.commit('page', res.data.meta ?? {});
                        context.commit('pagination', res.data ?? {});
                    }
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        save(context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url    = '/admin/session-queue';
                if (this.state['sessionQueue'].temp.isEditing) {
                    method = axios.post;
                    url    = `/admin/session-queue/${this.state['sessionQueue'].temp.temp_id}`;
                }
                method(url, payload.form).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    context.commit('reset');
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        edit(context, payload) { context.commit('temp', payload); },
        destroy(context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/session-queue/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        show(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/session-queue/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        call(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/session-queue/${payload.id}/call`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        seat(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/session-queue/${payload.id}/seat`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        cancel(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/session-queue/${payload.id}/cancel`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        reset(context) { context.commit('reset'); },
    },
    mutations: {
        lists(state, payload)      { state.lists      = payload; },
        pagination(state, payload) { state.pagination = payload; },
        page(state, payload)       { state.page       = payload; },
        show(state, payload)       { state.show       = payload; },
        temp(state, payload) {
            state.temp.temp_id   = payload;
            state.temp.isEditing = true;
        },
        reset(state) {
            state.temp.temp_id   = null;
            state.temp.isEditing = false;
        },
    },
};
