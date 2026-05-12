import axios from "axios";
import appService from "../../services/appService";

export const paymentMethod = {
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
        lastPaymentMethodUpdate: localStorage.getItem('lastPaymentMethodUpdate') || null,
        lastQueryTime: null, // Track last query time for cache duration check
        cacheDuration: 300000, // Cache duration in milliseconds (300 seconds default)
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
        lastPaymentMethodUpdate: function (state) {
            return state.lastPaymentMethodUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/setting/payment-method";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    // if ( payload || typeof payload.vuex === "undefined" || payload.vuex === true ) {
                    //     context.commit("lists", res.data.data);
                    //     context.commit("page", res.data.meta);
                    //     context.commit("pagination", res.data);
                    // }
                    context.commit("lists", res.data.data);
                    context.commit("page", res.data.meta);
                    context.commit("pagination", res.data);
                    resolve(res);
                })
                .catch((err) => {
                    reject(err);
                });
            });

            // return new Promise((resolve, reject) => {
            //     // Check if we should use cached payment methods
            //     const lastUpdate = context.state.lastPaymentMethodUpdate;
            //     const lastQueryTime = context.state.lastQueryTime;
            //     const cacheDuration = context.state.cacheDuration;
            //     const checkForUpdates = payload && payload.checkUpdates !== false;
            //     const now = Date.now();

            //     // Fast cache: Return data from state if queried recently (within cache duration)
            //     if (checkForUpdates && lastQueryTime && context.state.lists.length > 0) {
            //         const timeSinceLastQuery = now - lastQueryTime;
            //         if (timeSinceLastQuery < cacheDuration) {
            //             resolve({
            //                 data: {
            //                     data: context.state.lists,
            //                     meta: context.state.page,
            //                 },
            //                 fromStateCache: true,
            //                 cacheAge: timeSinceLastQuery
            //             });
            //             return;
            //         }
            //     }

            //     // If we have a last update timestamp and payment methods in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new payment methods
            //         const checkPayload = { ...payload, last_updated: lastUpdate };
            //         let url = "admin/setting/payment-method" + appService.requestHandler(checkPayload);

            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached payment methods from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Payment methods were updated, merge with existing
            //                 context.commit("mergeUpdatedPaymentMethods", res.data.data);
            //                 context.commit("page", res.data.meta);
            //                 context.commit("pagination", res.data);
            //                 // Update the timestamp to current time
            //                 context.commit("setLastPaymentMethodUpdate", new Date().toISOString());
            //                 context.commit("setLastQueryTime", Date.now());
            //                 resolve(res);
            //             }
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     } else {
            //         // First time fetch or forced refresh
            //         let url = "admin/setting/payment-method";
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios.get(url).then((res) => {
            //             context.commit("lists", res.data.data);
            //             context.commit("page", res.data.meta);
            //             context.commit("pagination", res.data);
            //             // Set the timestamp for first fetch
            //             context.commit("setLastPaymentMethodUpdate", new Date().toISOString());
            //             context.commit("setLastQueryTime", Date.now());
            //             resolve(res);
            //         })
            //         .catch((err) => {
            //             reject(err);
            //         });
            //     }
            // });
        },
        listOnlinePayment: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "payment-method/online-payment";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    // if ( payload || typeof payload.vuex === "undefined" || payload.vuex === true ) {
                    //     context.commit("lists", res.data.data);
                    //     context.commit("page", res.data.meta);
                    //     context.commit("pagination", res.data);
                    // }
                    context.commit("lists", res.data.data);
                    context.commit("page", res.data.meta);
                    context.commit("pagination", res.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        listTablePayment: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "payment-method/table-payment";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    // if ( payload || typeof payload.vuex === "undefined" || payload.vuex === true ) {
                    //     context.commit("lists", res.data.data);
                    //     context.commit("page", res.data.meta);
                    //     context.commit("pagination", res.data);
                    // }
                    context.commit("lists", res.data.data);
                    context.commit("page", res.data.meta);
                    context.commit("pagination", res.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = "admin/setting/payment-method";

                if (this.state["paymentMethod"].temp.isEditing) {
                    // For FormData updates, we need to use POST with _method override
                    method = axios.post;
                    url = `admin/setting/payment-method/${this.state["paymentMethod"].temp.temp_id}`;

                    // Add method override for Laravel to recognize this as PUT request
                    if (payload.form instanceof FormData) {
                        payload.form.append('_method', 'PUT');
                    } else {
                        // Fallback for non-FormData requests (though we're using FormData now)
                        method = axios.put;
                    }
                }

                method(url, payload.form)
                    .then((res) => {
                        context.dispatch("lists", payload.search).then().catch();
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
                    .delete(`admin/setting/payment-method/${payload.id}`)
                    .then((res) => {
                        context
                            .dispatch("lists", payload.search)
                            .then()
                            .catch();
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
                    .get(`admin/setting/payment-method/show/${payload}`)
                    .then((res) => {
                        context.commit("show", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        reset: function (context) {
            context.commit("reset");
        },
        forceRefresh: function (context, payload) {
            // Force refresh payment methods by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastPaymentMethodUpdate', null);
            localStorage.removeItem('lastPaymentMethodUpdate');
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },
        mergeUpdatedPaymentMethods: function (state, updatedMethods) {
            // Create a map of existing payment methods by ID for quick lookup
            const existingMethodsMap = new Map(state.lists.map(method => [method.id, method]));

            // Update or add payment methods from the updated list
            updatedMethods.forEach(updatedMethod => {
                existingMethodsMap.set(updatedMethod.id, updatedMethod);
            });

            // Convert map back to array
            state.lists = Array.from(existingMethodsMap.values());
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
        reset: function (state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        },
        setLastPaymentMethodUpdate: function(state, timestamp) {
            state.lastPaymentMethodUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastPaymentMethodUpdate', timestamp);
            } else {
                localStorage.removeItem('lastPaymentMethodUpdate');
            }
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    },
};
