import axios from "axios";
import appService from "../../services/appService";

export const printLabelSetting = {
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
        lastPrintLabelSettingsUpdate: localStorage.getItem('lastPrintLabelSettingsUpdate') || null,
        lastQueryTime: null, // Track last query time for cache duration check
        cacheDuration: 300000, // Cache duration in milliseconds (30 seconds default)
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
        showBranch:function(state) {
            return state.show
        },
        lastPrintLabelSettingsUpdate: function (state) {
            return state.lastPrintLabelSettingsUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) { 
            return new Promise((resolve, reject) => {

                // First time fetch or forced refresh
                let url = "admin/setting/printLabel";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) { 
                        context.commit("lists", res.data.data);
                        context.commit("page", res.data.meta);
                        context.commit("pagination", res.data);
                        // Set the timestamp for first fetch
                        context.commit('setLastPrintLabelSettingsUpdate', new Date().toISOString());
                        // Update last query time for instant cache
                        context.commit('setLastQueryTime', Date.now());
                    }
                    resolve(res);
                })
                .catch((err) => {
                    reject(err);
                });


                // Check if we should use cached print label settings
                // const lastUpdate = context.state.lastPrintLabelSettingsUpdate;
                // const lastQueryTime = context.state.lastQueryTime;
                // const cacheDuration = context.state.cacheDuration;
                // const checkForUpdates = !payload || payload.checkUpdates !== false;
                // const now = Date.now();
                
                // // Fast cache: Return data from state if queried recently (within cache duration)
                // if (checkForUpdates && lastQueryTime && context.state.lists.length > 0) {
                //     const timeSinceLastQuery = now - lastQueryTime;
                //     if (timeSinceLastQuery < cacheDuration) {
                //         // Data is fresh, return immediately from state
                //         resolve({
                //             data: {
                //                 data: context.state.lists,
                //                 meta: context.state.page,
                //             },
                //             fromStateCache: true,
                //             cacheAge: timeSinceLastQuery
                //         });
                //         return;
                //     }
                // }
                
                // // If we have a last update timestamp and settings in store, check for updates
                // if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
                //     // Add last_updated parameter to check for new settings
                //     const checkPayload = payload ? { ...payload, last_updated: lastUpdate } : { last_updated: lastUpdate };
                //     let url = 'admin/setting/printLabel' + appService.requestHandler(checkPayload);
                    
                //     axios.get(url).then((res) => {
                //         // Check if response indicates no updates
                //         if (res.data.has_updates === false) {
                //             // Return cached settings from store
                //             resolve({
                //                 data: {
                //                     data: context.state.lists,
                //                     meta: context.state.page,
                //                 },
                //                 fromCache: true
                //             });
                //         } else {
                //             // Settings were updated, merge with existing settings
                //             if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) { 
                //                 // Merge updated settings with existing cached settings
                //                 context.commit('mergeUpdatedPrintLabelSettings', res.data.data);
                //                 context.commit('page', res.data.meta);
                //                 context.commit('pagination', res.data);
                //                 // Update the timestamp to current time
                //                 context.commit('setLastPrintLabelSettingsUpdate', new Date().toISOString());
                //                 // Update last query time for instant cache
                //                 context.commit('setLastQueryTime', Date.now());
                //             }
                //             resolve(res);
                //         }
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // } else {
                //     // First time fetch or forced refresh
                //     let url = "admin/setting/printLabel";
                //     if (payload) {
                //         url = url + appService.requestHandler(payload);
                //     }
                //     axios.get(url).then((res) => {
                //         if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) { 
                //             context.commit("lists", res.data.data);
                //             context.commit("page", res.data.meta);
                //             context.commit("pagination", res.data);
                //             // Set the timestamp for first fetch
                //             context.commit('setLastPrintLabelSettingsUpdate', new Date().toISOString());
                //             // Update last query time for instant cache
                //             context.commit('setLastQueryTime', Date.now());
                //         }
                //         resolve(res);
                //     })
                //     .catch((err) => {
                //         reject(err);
                //     });
                // }
            });
        },
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = "/admin/setting/printLabel";
                if (this.state["printLabelSetting"].temp.isEditing) {
                    method = axios.put;
                    url = `/admin/setting/printLabel/${this.state["printLabelSetting"].temp.temp_id}`;
                }
                method(url, payload.form)
                    .then((res) => {
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
        update: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.put(`/admin/setting/printLabel/${payload.id}`, payload.form)
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
        edit: function (context, payload) {  
            context.commit("temp", payload);
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`admin/setting/printLabel/${payload.id}`)
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
                axios.get(`admin/setting/printLabel/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            context.commit("reset");
        },
        forceRefresh: function (context, payload) {
            // Force refresh print label settings by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastPrintLabelSettingsUpdate', null);
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
        reset: function (state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        },
        setLastPrintLabelSettingsUpdate: function(state, timestamp) {
            state.lastPrintLabelSettingsUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastPrintLabelSettingsUpdate', timestamp);
            } else {
                localStorage.removeItem('lastPrintLabelSettingsUpdate');
            }
        },
        mergeUpdatedPrintLabelSettings: function (state, updatedSettings) {
            // Create a map of existing settings by ID for quick lookup
            const settingsMap = new Map(state.lists.map(setting => [setting.id, setting]));
            
            // Update or add settings from the updated list
            updatedSettings.forEach(updatedSetting => {
                settingsMap.set(updatedSetting.id, updatedSetting);
            });
            
            // Convert map back to array
            state.lists = Array.from(settingsMap.values());
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        },
    },
};
