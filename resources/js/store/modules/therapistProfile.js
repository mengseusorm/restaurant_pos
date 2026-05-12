import axios from 'axios'
import appService from "../../services/appService";

export const therapistProfile = {
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
                let url = 'admin/therapist-profile';
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
                let url    = '/admin/therapist-profile';
                if (this.state['therapistProfile'].temp.isEditing) {
                    method = axios.post;
                    url    = `/admin/therapist-profile/${this.state['therapistProfile'].temp.temp_id}`;
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
                axios.delete(`admin/therapist-profile/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        show(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/therapist-profile/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        changeStatus(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/therapist-profile/${payload.id}/change-status`, { status: payload.status }).then((res) => {
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
