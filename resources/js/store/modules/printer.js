import axios from 'axios'
import appService from "../../services/appService";


export const printer = {
    namespaced: true,
    state: {
        lists: [],
        listsItemReportDaily:[],
        page: {},
        pagination: {},
        show: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
        lastPrintersUpdate: localStorage.getItem('lastPrintersUpdate') || null,
        lastQueryTime: null, // Track last query time for cache duration check
        cacheDuration: 300000, // Cache duration in milliseconds (30 seconds default)
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        pagination: function (state) {
            return state.pagination
        },
        page: function(state) {
            return state.page;
        },
        show: function (state) {
            return state.show;
        },
        temp: function (state) {
            return state.temp;
        },
        lastPrintersUpdate: function (state) {
            return state.lastPrintersUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) { 
            return new Promise((resolve, reject) => {
                let url = '/admin/setting/kitchen-printer';

                if (payload) {
                    url = url + appService.requestHandler(payload);
                }

                axios.get(url).then((res) => {
                    context.commit('lists', res.data.data);
                    context.commit('page', res.data.meta);
                    context.commit('pagination', res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
            // return new Promise((resolve, reject) => {
            //     // Check if we should use cached printers
            //     const lastUpdate = context.state.lastPrintersUpdate;
            //     const lastQueryTime = context.state.lastQueryTime;
            //     const cacheDuration = context.state.cacheDuration;
            //     const checkForUpdates = !payload || payload.checkUpdates !== false;
            //     const now = Date.now();

            //     // Fast cache: Return data from state if queried recently (within cache duration)
            //     if (checkForUpdates && lastQueryTime && context.state.lists.length > 0) {
            //         const timeSinceLastQuery = now - lastQueryTime;
            //         if (timeSinceLastQuery < cacheDuration) {
            //             // Data is fresh, return immediately from state
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

            //     // If we have a last update timestamp and printers in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new printers
            //         const checkPayload = payload ? { ...payload, last_updated: lastUpdate } : { last_updated: lastUpdate };
            //         let url = '/admin/setting/kitchen-printer' + appService.requestHandler(checkPayload);

            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached printers from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Printers were updated, merge with existing printers
            //                 if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                     // Merge updated printers with existing cached printers
            //                     context.commit('mergeUpdatedPrinters', res.data.data);
            //                     context.commit('page', res.data.meta);
            //                     context.commit('pagination', res.data);
            //                     // Update the timestamp to current time
            //                     context.commit('lastPrintersUpdate', new Date().toISOString());
            //                     // Update query time
            //                     context.commit('setLastQueryTime', Date.now());
            //                 }
            //                 resolve(res);
            //             }
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     } else {
            //         // First time fetch or forced refresh
            //         let url = '/admin/setting/kitchen-printer';
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios.get(url).then((res) => {
            //             if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                 context.commit('lists', res.data.data);
            //                 context.commit('page', res.data.meta);
            //                 context.commit('pagination', res.data);
            //                 // Set the timestamp for first fetch
            //                 context.commit('lastPrintersUpdate', new Date().toISOString());
            //                 // Update query time
            //                 context.commit('setLastQueryTime', Date.now());
            //             }
            //             resolve(res);
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     }
            // });
        },
        forceRefresh: function (context, payload) {
            // Force refresh printers by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('lastPrintersUpdate', null);
        },

        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = '/admin/setting/kitchen-printer'
                if (this.state['printer'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/setting/kitchen-printer/update/${this.state['printer'].temp.temp_id}`;
                }
                method(url, payload.form).then(res => {
                    if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.dispatch('lists'),
                        context.commit('reset');
                     }
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
                axios.delete(`/admin/setting/kitchen-printer/${payload.id}`).then((res) => {
                    context.dispatch('lists').then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/dining-table/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
    },

    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        listsItemReportDaily:function (state, payload){
            state.listsItemReportDaily = payload
        },
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        page: function (state, payload) {
            if(typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                }
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
        lastPrintersUpdate: function(state, timestamp) {
            state.lastPrintersUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastPrintersUpdate', timestamp);
            } else {
                localStorage.removeItem('lastPrintersUpdate');
            }
        },
        mergeUpdatedPrinters: function (state, payload) {
            // Create a map of existing printers for O(1) lookup
            const printerMap = new Map(state.lists.map(printer => [printer.id, printer]));

            // Update or add new printers
            payload.forEach(updatedPrinter => {
                printerMap.set(updatedPrinter.id, updatedPrinter);
            });

            // Convert map back to array
            state.lists = Array.from(printerMap.values());
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    },
}
