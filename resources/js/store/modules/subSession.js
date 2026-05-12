import axios from 'axios'
import appService from "../../services/appService";

export const subSession = {
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
                let url = 'admin/massage-session';
                if (payload) { url = url + appService.requestHandler(payload); }
                axios.get(url).then((res) => {
                    if (typeof payload.vuex === 'undefined' || payload.vuex === true) {
                        context.commit('lists', res.data.data);
                        context.commit('page', res.data.meta);
                        context.commit('pagination', res.data);
                    }
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        save(context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url    = '/admin/massage-session';
                if (this.state['subSession'].temp.isEditing) {
                    method = axios.post;
                    url    = `/admin/massage-session/${this.state['subSession'].temp.temp_id}`;
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
                axios.delete(`admin/massage-session/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        show(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/massage-session/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        start(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/start`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        startItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.sessionId}/start-item/${payload.itemId}`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        completeItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.sessionId}/complete-item/${payload.itemId}`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        complete(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/complete`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        addItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/add-item`, payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        removeItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/massage-session/${payload.id}/remove-item/${payload.sessionServiceItemId}`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        checkout(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/checkout`).then((res) => {
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
