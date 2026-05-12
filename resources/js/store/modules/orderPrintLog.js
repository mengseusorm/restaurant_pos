import axios from "axios";
import appService from "../../services/appService";

export const orderPrintLog = {
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
                let url = "admin/order-print-logs";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                
                axios.get(url).then((response) => {
                    if (typeof response.data.data === "object") {
                        context.commit("lists", response.data.data);
                        context.commit("page", response.data.meta);
                        context.commit("pagination", response.data);
                    } else {
                        context.commit("lists", response.data);
                    }
                    resolve(response);
                }).catch((error) => {
                    reject(error);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get("admin/order-print-logs/" + payload.id).then((response) => {
                    context.commit("show", response.data.data);
                    resolve(response);
                }).catch((error) => {
                    reject(error);
                });
            });
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete("admin/order-print-logs/" + payload.id).then((response) => {
                    context.dispatch("lists", payload.search).then().catch();
                    resolve(response);
                }).catch((error) => {
                    reject(error);
                });
            });
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/order-print-logs/export";
                if (payload) {
                    let separator = "?";
                    if (payload.user_id) {
                        url = url + separator + "user_id=" + payload.user_id;
                        separator = "&";
                    }
                    if (payload.order_serial_number) {
                        url = url + separator + "order_serial_number=" + payload.order_serial_number;
                        separator = "&";
                    }
                    if (payload.print_type) {
                        url = url + separator + "print_type=" + payload.print_type;
                        separator = "&";
                    }
                    if (payload.print_success !== null && payload.print_success !== undefined) {
                        url = url + separator + "print_success=" + payload.print_success;
                        separator = "&";
                    }
                    if (payload.from_date) {
                        url = url + separator + "from_date=" + payload.from_date;
                        separator = "&";
                    }
                    if (payload.to_date) {
                        url = url + separator + "to_date=" + payload.to_date;
                        separator = "&";
                    }
                }
                
                axios.get(url, { responseType: "blob" }).then((response) => {
                    resolve(response);
                }).catch((error) => {
                    reject(error);
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
    },
};
