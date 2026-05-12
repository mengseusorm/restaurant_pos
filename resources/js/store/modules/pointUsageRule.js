import axios from "axios";
import appService from "../../services/appService";

export const pointUsageRule = {
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
                // console.log("Fetching point usage rules with payload:", payload);
                let url = "admin/point-usage-rule";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                // console.log("Fetching point usage rules from:", url);
                axios
                    .get(url)
                    .then((res) => {
                        // console.log("Point usage rules fetched successfully:", res.data.data);

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
                        console.log("Error fetching point usage rules:", err);
                        reject(err);
                    });
            });
        },
        save: function (context, payload) {
            console.log("Starting save action with payload:", payload);
            return new Promise((resolve, reject) => {
                console.log("Preparing to save point usage rule with data:", payload.form);
                let method = axios.post;
                let url = "/admin/point-usage-rule";
                if (this.state['pointUsageRule'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/point-usage-rule/${this.state['pointUsageRule'].temp.temp_id}`;
                }
                console.log("Using method:", method, "and URL:", url);
                method(url, payload.form)
                    .then((res) => {
                        console.log("Point usage rule saved successfully:", res.data);

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
                    .delete("admin/point-usage-rule/" + payload.id)
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
                    .get("admin/point-usage-rule/" + payload)
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
                let url = "admin/point-usage-rule/export";
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
        },
    },
};
