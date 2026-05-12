import axios from 'axios';
import appService from "../../services/appService";

export const activityLog = {
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
        },
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/activity-log';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios
                    .get(url)
                    .then((response) => {
                        if (response.data.data) {
                            context.commit('lists', response.data.data);
                            if (response.data.pagination) {
                                context.commit('page', {
                                    current_page: response.data.pagination.current_page,
                                    last_page: response.data.pagination.last_page,
                                    per_page: response.data.pagination.per_page,
                                    total: response.data.pagination.total,
                                });
                                context.commit('pagination', {
                                    meta: {
                                        current_page: response.data.pagination.current_page,
                                        last_page: response.data.pagination.last_page,
                                        per_page: response.data.pagination.per_page,
                                        total: response.data.pagination.total,
                                    },
                                });
                            }
                        } else {
                            context.commit('lists', response.data);
                        }
                        resolve(response);
                    })
                    .catch((error) => {
                        console.error('Error in activity log lists:', error);
                        reject(error);
                    });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('admin/activity-log/show/' + payload)
                    .then((response) => {
                        console.log('Activity Log Details:', response.data.data);
                        context.commit('show', response.data.data);
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete('admin/activity-log/' + payload.id)
                    .then((response) => {
                        context.dispatch('lists', payload.search).then().catch();
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        statistics: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/activity-log/statistics';
                if (payload) {
                    let separator = '?';
                    if (payload.start_date) {
                        url = url + separator + 'start_date=' + payload.start_date;
                        separator = '&';
                    }
                    if (payload.end_date) {
                        url = url + separator + 'end_date=' + payload.end_date;
                        separator = '&';
                    }
                }

                axios
                    .get(url)
                    .then((response) => {
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        byType: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/activity-log/by-type/' + payload.logName;
                if (payload.per_page) {
                    url = url + '?per_page=' + payload.per_page;
                }

                axios
                    .get(url)
                    .then((response) => {
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        byUser: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/activity-log/by-user/' + payload.userId;
                if (payload.per_page) {
                    url = url + '?per_page=' + payload.per_page;
                }

                axios
                    .get(url)
                    .then((response) => {
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        clean: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post('admin/activity-log/clean', payload)
                    .then((response) => {
                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        },
        temp: function (context, payload) {
            context.commit('temp', payload);
        },
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
        },
    },
};
