import axios from "axios";

export const lostAndFound = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: {},
        show: {},
        temp: {
            id: null,
            isEditing: false
        }
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
                let url = 'admin/lost-and-found';
                if (payload) {
                    url = url + '?page=' + payload.page + '&paginate=' + payload.paginate + '&order_column=' + payload.order_column + '&order_type=' + payload.order_type;
                    
                    if (payload.from_date) {
                        url = url + '&from_date=' + payload.from_date;
                    }
                    if (payload.to_date) {
                        url = url + '&to_date=' + payload.to_date;
                    }
                    if (payload.status) {
                        url = url + '&status=' + payload.status;
                    }
                    if (payload.per_page) {
                        url = url + '&per_page=' + payload.per_page;
                    }
                }
                axios.get(url).then((res) => {
                    if (typeof payload.vuex === "undefined" || payload.vuex === true) {
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
                let method = axios.post;
                let url = '/admin/lost-and-found';
                if (this.state.lostAndFound.temp.isEditing) {
                    method = axios.post;
                    url = '/admin/lost-and-found/' + this.state.lostAndFound.temp.id;
                    payload.form.append('_method', 'PUT');
                }
                method(url, payload.form).then(res => {
                    context.dispatch('lists', payload.search).then().catch();
                    context.commit('reset');
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        edit: function (context, payload) {
            context.commit('temp', payload);
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete('/admin/lost-and-found/' + payload.id).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('/admin/lost-and-found/show/' + payload).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        markAsClaimed: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('/admin/lost-and-found/' + payload.id + '/mark-as-claimed', payload.form, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then((res) => {
                    if (payload.search) {
                        context.dispatch('lists', payload.search).then().catch();
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        markAsDisposed: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('/admin/lost-and-found/' + payload.id + '/mark-as-disposed', payload.form, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then((res) => {
                    if (payload.search) {
                        context.dispatch('lists', payload.search).then().catch();
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            context.commit('reset');
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
            state.temp.id = payload;
            state.temp.isEditing = true;
        },
        reset: function (state) {
            state.temp.id = null;
            state.temp.isEditing = false;
        }
    }
}
