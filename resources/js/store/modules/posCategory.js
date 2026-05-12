import axios from 'axios'
import appService from "../../services/appService";


export const posCategory = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
        lastPosCategoryUpdate: localStorage.getItem('lastPosCategoryUpdate') || null,
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
        temp: function (state) {
            return state.temp;
        },
        lastPosCategoryUpdate: function (state) {
            return state.lastPosCategoryUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {

                // First time fetch or forced refresh
                let url = 'admin/pos-category';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit('lists', res.data.data);
                        context.commit('page', res.data.meta);
                        context.commit('pagination', res.data);
                        // Set the timestamp for first fetch
                        context.commit('setLastPosCategoryUpdate', new Date().toISOString());
                        context.commit('setLastQueryTime', Date.now());
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });

                // Check if we should use cached categories
                // const lastUpdate = context.state.lastPosCategoryUpdate;
                // const lastQueryTime = context.state.lastQueryTime;
                // const cacheDuration = context.state.cacheDuration;
                // const checkForUpdates = payload && payload.checkUpdates !== false;
                // const now = Date.now();
                
                // // Fast cache: Return data from state if queried recently (within cache duration)
                // if (checkForUpdates && lastQueryTime && context.state.lists.length > 0) {
                //     const timeSinceLastQuery = now - lastQueryTime;
                //     if (timeSinceLastQuery < cacheDuration) {
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
                
                // // If we have a last update timestamp and categories in store, check for updates
                // if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
                //     // Add last_updated parameter to check for new categories
                //     const checkPayload = { ...payload, last_updated: lastUpdate };
                //     let url = 'admin/pos-category' + appService.requestHandler(checkPayload);
                    
                //     axios.get(url).then((res) => {
                //         // Check if response indicates no updates
                //         if (res.data.has_updates === false) {
                //             // Return cached categories from store
                //             resolve({
                //                 data: {
                //                     data: context.state.lists,
                //                     meta: context.state.page,
                //                 },
                //                 fromCache: true
                //             });
                //         } else {
                //             // Categories were updated, merge with existing categories
                //             if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                //                 // Merge updated categories with existing cached categories
                //                 context.commit('mergeUpdatedCategories', res.data.data);
                //                 context.commit('page', res.data.meta);
                //                 context.commit('pagination', res.data);
                //                 // Update the timestamp to current time
                //                 context.commit('setLastPosCategoryUpdate', new Date().toISOString());
                //                 context.commit('setLastQueryTime', Date.now());
                //             }
                //             resolve(res);
                //         }
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // } else {
                //     // First time fetch or forced refresh
                //     let url = 'admin/pos-category';
                //     if (payload) {
                //         url = url + appService.requestHandler(payload);
                //     }
                //     axios.get(url).then((res) => {
                //         if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                //             context.commit('lists', res.data.data);
                //             context.commit('page', res.data.meta);
                //             context.commit('pagination', res.data);
                //             // Set the timestamp for first fetch
                //             context.commit('setLastPosCategoryUpdate', new Date().toISOString());
                //             context.commit('setLastQueryTime', Date.now());
                //         }
                //         resolve(res);
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // }
            });
        },
        reset: function (context) {
            context.commit('reset');
        },
        forceRefresh: function (context, payload) {
            // Force refresh categories by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastPosCategoryUpdate', null);
            localStorage.removeItem('lastPosCategoryUpdate');
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        mergeUpdatedCategories: function (state, updatedCategories) {
            // Create a map of existing categories by ID for quick lookup
            const existingCategoriesMap = new Map(state.lists.map(category => [category.id, category]));
            
            // Update or add categories from the updated list
            updatedCategories.forEach(updatedCategory => {
                existingCategoriesMap.set(updatedCategory.id, updatedCategory);
            });
            
            // Convert map back to array
            state.lists = Array.from(existingCategoriesMap.values());
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
        reset: function(state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        },
        setLastPosCategoryUpdate: function(state, timestamp) {
            state.lastPosCategoryUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastPosCategoryUpdate', timestamp);
            } else {
                localStorage.removeItem('lastPosCategoryUpdate');
            }
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    },
}
