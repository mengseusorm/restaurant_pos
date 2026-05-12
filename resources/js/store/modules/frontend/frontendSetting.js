import axios from "axios";
import appService from "../../../services/appService";

export const frontendSetting = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        lastFrontendSettingsUpdate: localStorage.getItem('lastFrontendSettingsUpdate') || null,
        lastQueryTime: null, // Timestamp of last query for instant cache
        cacheDuration: 30000, // Cache duration in milliseconds (30 seconds)
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        page: function(state) {
            return state.page;
        },
        lastFrontendSettingsUpdate: function (state) {
            return state.lastFrontendSettingsUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {

                // First time fetch or forced refresh
                let url = "frontend/setting";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) { 
                        context.commit("lists", res.data.data);
                        context.commit('page', res.data.meta);
                        // Set the timestamp for first fetch
                        context.commit('setLastFrontendSettingsUpdate', new Date().toISOString());
                        // Update last query time for instant cache
                        context.commit('setLastQueryTime', Date.now());
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
                
                // Check if we should use cached frontend settings
                // const lastUpdate = context.state.lastFrontendSettingsUpdate;
                // const checkForUpdates = !payload || payload.checkUpdates !== false;
                // const now = Date.now();
                // const lastQueryTime = context.state.lastQueryTime;
                // const cacheDuration = context.state.cacheDuration;
                
                // // Fast cache: Return data from state if queried recently (within cache duration)
                // if (checkForUpdates && lastQueryTime && context.state.lists.length > 0) {
                //     const timeSinceLastQuery = now - lastQueryTime;
                //     if (timeSinceLastQuery < cacheDuration) {
                //         resolve({
                //             data: { data: context.state.lists, meta: context.state.page },
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
                //     let url = 'frontend/setting' + appService.requestHandler(checkPayload);
                    
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
                //                 context.commit('mergeUpdatedFrontendSettings', res.data.data);
                //                 context.commit('page', res.data.meta);
                //                 // Update the timestamp to current time
                //                 context.commit('setLastFrontendSettingsUpdate', new Date().toISOString());
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
                //     let url = "frontend/setting";
                //     if (payload) {
                //         url = url + appService.requestHandler(payload);
                //     }
                //     axios.get(url).then((res) => {
                //         if(!payload || typeof payload.vuex === "undefined" || payload.vuex === true) { 
                //             context.commit("lists", res.data.data);
                //             context.commit('page', res.data.meta);
                //             // Set the timestamp for first fetch
                //             context.commit('setLastFrontendSettingsUpdate', new Date().toISOString());
                //             // Update last query time for instant cache
                //             context.commit('setLastQueryTime', Date.now());
                //         }
                //         resolve(res);
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // }
            });
        },
        forceRefresh: function (context, payload) {
            // Force refresh frontend settings by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastFrontendSettingsUpdate', null);
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },
        page: function (state, payload) {
            if(typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                };
            }
        },
        setLastFrontendSettingsUpdate: function(state, timestamp) {
            state.lastFrontendSettingsUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastFrontendSettingsUpdate', timestamp);
            } else {
                localStorage.removeItem('lastFrontendSettingsUpdate');
            }
        },
        mergeUpdatedFrontendSettings: function (state, updatedSettings) {
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
        }
    },
};
