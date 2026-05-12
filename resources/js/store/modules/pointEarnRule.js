import axios from "axios";
import appService from "../../services/appService";

export const pointEarnRule = {
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
                // console.log("Fetching point earn rules with payload:", payload);
                let url = "admin/point-earn-rule";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                // console.log("Fetching point earn rules from:", url);
                axios
                    .get(url)
                    .then((res) => { 
                        if (
                            typeof payload.vuex === "undefined" ||
                            payload.vuex === true
                        ) {
                            context.commit("lists", res.data.data);
                            context.commit("page", res.data.meta);
                            context.commit("pagination", res.data);
                        }
                        
                        resolve(res);
                    })
                    .catch((err) => {
                        console.log("Error fetching point earn rules:", err);
                        reject(err);
                    });
            });
        },

        save: function (context, payload) {
            console.log("Starting save action with payload:", payload);
            return new Promise((resolve, reject) => {
                console.log("Preparing to save point earn rule with data:", payload.form);
                let method = axios.post;
                let url = "/admin/point-earn-rule";
                if (this.state['pointEarnRule'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/point-earn-rule/${this.state['pointEarnRule'].temp.temp_id}`;
                }
                console.log("Using method:", method, "and URL:", url);
                method(url, payload.form)
                    .then((res) => {
                        console.log("Point earn rule saved successfully:", res.data);

                        context
                            .dispatch("lists", payload.search)
                            .then()
                            .catch();
                        context.commit("reset");
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        edit: function (context, payload) {
            context.commit("temp", payload);
        },

        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete("admin/point-earn-rule/" + payload.id)
                    .then((res) => {
                        context.dispatch("lists", payload.search).then().catch();
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/point-earn-rule/" + payload)
                    .then((res) => {
                        context.commit("show", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/point-earn-rule/export";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios
                    .get(url, { responseType: "blob" })
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        reset: function (context) {
            return new Promise((resolve, reject) => {
                context.commit("reset");
                resolve();
            });
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
            if (typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total,
                };
            }
        },
        show: function (state, payload) {
            state.show = payload;
        },
        temp: function (state, payload) {
            state.temp.temp_id = payload;
            state.temp.isEditing = true;
        },
        reset: function(state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        }
    },
};
