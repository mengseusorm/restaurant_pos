import axios from 'axios'
import appService from "../../services/appService";


export const item = {
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
        lastItemsUpdate: localStorage.getItem('lastItemsUpdate') || null,
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
        lastItemsUpdate: function (state) {
            return state.lastItemsUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/item';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit('lists', res.data.data);
                        context.commit('page', res.data.meta);
                        context.commit('pagination', res.data);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
            // return new Promise((resolve, reject) => {
            //     // Check if we should use cached items
            //     const lastUpdate = context.state.lastItemsUpdate;
            //     const lastQueryTime = context.state.lastQueryTime;
            //     const cacheDuration = context.state.cacheDuration;
            //     const checkForUpdates = payload && payload.checkUpdates !== false;
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

            //     // If we have a last update timestamp and items in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new items
            //         const checkPayload = { ...payload, last_updated: lastUpdate };
            //         let url = 'admin/item' + appService.requestHandler(checkPayload);

            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached items from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Items were updated, merge with existing items
            //                 if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                     // Merge updated items with existing cached items
            //                     context.commit('mergeUpdatedItems', res.data.data);
            //                     context.commit('page', res.data.meta);
            //                     context.commit('pagination', res.data);
            //                     // Update the timestamp to current time
            //                     context.commit('setLastItemsUpdate', new Date().toISOString());
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
            //         let url = 'admin/item';
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios.get(url).then((res) => {
            //             if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                 context.commit('lists', res.data.data);
            //                 context.commit('page', res.data.meta);
            //                 context.commit('pagination', res.data);
            //                 // Set the timestamp for first fetch
            //                 context.commit('setLastItemsUpdate', new Date().toISOString());
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
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = '/admin/item';
                if (this.state['item'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/item/${this.state['item'].temp.temp_id}`;
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
                axios.delete(`admin/item/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/item/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        changeImage: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post(
                        `/admin/item/change-image/${payload.id}`,
                        payload.form,
                        {
                            headers: {
                                "Content-Type": "multipart/form-data",
                            },
                        }
                    )
                    .then((res) => {
                        context.commit("show", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        uploadImages: function (context, payload) { 
            return new Promise((resolve, reject) => {
                axios.post( '/admin/item/upload-images',
                        payload.form,
                        {
                            headers: {
                                "Content-Type": "multipart/form-data",
                            },
                        }
                    )
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        reset: function (context) {
            context.commit('reset');
        },
        forceRefresh: function (context, payload) {
            // Force refresh items by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastItemsUpdate', null);
            localStorage.removeItem('lastItemsUpdate');
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/item/export';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url, {responseType: 'blob'}).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        downloadSample: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/item/download-sample/';
                axios.get(url, { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        import: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('/admin/item/import/file', payload.form).then((res) => {
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
        mergeUpdatedItems: function (state, updatedItems) {
            // Create a map of existing items by ID for quick lookup
            const existingItemsMap = new Map(state.lists.map(item => [item.id, item]));

            // Update or add items from the updated list
            updatedItems.forEach(updatedItem => {
                existingItemsMap.set(updatedItem.id, updatedItem);
            });

            // Convert map back to array
            state.lists = Array.from(existingItemsMap.values());
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
        setLastItemsUpdate: function(state, timestamp) {
            state.lastItemsUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastItemsUpdate', timestamp);
            } else {
                localStorage.removeItem('lastItemsUpdate');
            }
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    },
}
