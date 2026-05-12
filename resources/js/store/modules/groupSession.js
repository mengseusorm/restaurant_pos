import axios from 'axios'
import appService from "../../services/appService";

export const groupSession = {
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
                let url = 'admin/group-session';
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
                let url    = 'admin/group-session';
                if (context.state.temp.isEditing) {
                    method = axios.patch;
                    url    = `admin/group-session/${context.state.temp.temp_id}`;
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
                axios.delete(`admin/group-session/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        show(context, id) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/group-session/show/${id}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        addSubSession(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/group-session/${payload.groupId}/add-sub-session`, payload.form)
                    .then((res) => { resolve(res); }).catch((err) => { reject(err); });
            });
        },
        removeSubSession(context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/group-session/${payload.groupId}/remove-sub-session/${payload.subSessionId}`)
                    .then((res) => { resolve(res); }).catch((err) => { reject(err); });
            });
        },
        checkout(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/group-session/${payload.id}/checkout`, payload.form || {})
                    .then((res) => { resolve(res); }).catch((err) => { reject(err); });
            });
        },
        checkoutSplit(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/group-session/${payload.id}/checkout-split`, payload.form || {})
                    .then((res) => { resolve(res); }).catch((err) => { reject(err); });
            });
        },
        createMember(context, payload) {
            // payload: { groupId, form }
            return new Promise((resolve, reject) => {
                axios.post('admin/massage-session', {
                    ...payload.form,
                    group_session_id: payload.groupId,
                }).then((res) => { resolve(res); }).catch((err) => { reject(err); });
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
