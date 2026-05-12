import axios from 'axios'
import appService from "../../services/appService";


export const diningTable = {
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
        lastDiningTableUpdate: localStorage.getItem('lastDiningTableUpdate') || null,
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
        lastDiningTableUpdate: function (state) {
            return state.lastDiningTableUpdate;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/dining-table';
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
            //     // Check if we should use cached dining tables
            //     const lastUpdate = context.state.lastDiningTableUpdate;
            //     const checkForUpdates = payload && payload.checkUpdates !== false;
                
            //     // If we have a last update timestamp and dining tables in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new dining tables
            //         const checkPayload = { ...payload, last_updated: lastUpdate };
            //         let url = 'admin/dining-table' + appService.requestHandler(checkPayload);
                    
            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached dining tables from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Dining tables were updated, merge with existing dining tables
            //                 if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                     // Merge updated dining tables with existing cached dining tables
            //                     context.commit('mergeUpdatedDiningTables', res.data.data);
            //                     context.commit('page', res.data.meta);
            //                     context.commit('pagination', res.data);
            //                     // Update the timestamp to current time
            //                     context.commit('setLastDiningTableUpdate', new Date().toISOString());
            //                 }
            //                 resolve(res);
            //             }
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     } else {
            //         // First time fetch or forced refresh
            //         let url = 'admin/dining-table';
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios.get(url).then((res) => {
            //             if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                 context.commit('lists', res.data.data);
            //                 context.commit('page', res.data.meta);
            //                 context.commit('pagination', res.data);
            //                 // Set the timestamp for first fetch
            //                 context.commit('setLastDiningTableUpdate', new Date().toISOString());
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
                let url = '/admin/dining-table';
                if (this.state['diningTable'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/dining-table/${this.state['diningTable'].temp.temp_id}`;
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

        releaseDiningTable: function (context, payload) { 
            // return new Promise((resolve, reject) => {
            //     // Use the PosOrder API with the order ID from the table's current_order_id
            //     const orderId = payload.current_order_id || payload.id;
            //     axios.post(`/admin/pos-order/release-dining-table/${orderId}`, payload).then(res => {
            //         context.dispatch('lists').then().catch(); 
            //         resolve(res);
            //     }).catch((err) => {
            //         reject(err);
            //     });
            // });

            return new Promise((resolve, reject) => {

                // Note: Release dining table by its own ID
                axios.post(`/admin/pos-order/release-dining-table/${payload.id}`, payload).then(res => {
                    context.dispatch('lists').then().catch(); 
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
                axios.delete(`admin/dining-table/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
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
        changeImage: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/dining-table/change-image/${payload.id}`, payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            context.commit('reset');
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/dining-table/export';
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
        forceRefresh: function (context, payload) {
            // Force refresh dining tables by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastDiningTableUpdate', null);
            localStorage.removeItem('lastDiningTableUpdate');
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        mergeUpdatedDiningTables: function (state, updatedDiningTables) {
            // Create a map of existing dining tables by ID for quick lookup
            const existingDiningTablesMap = new Map(state.lists.map(table => [table.id, table]));
            
            // Update or add dining tables from the updated list
            updatedDiningTables.forEach(updatedTable => {
                existingDiningTablesMap.set(updatedTable.id, updatedTable);
            });
            
            // Convert map back to array
            state.lists = Array.from(existingDiningTablesMap.values());
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
        setLastDiningTableUpdate: function(state, timestamp) {
            state.lastDiningTableUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastDiningTableUpdate', timestamp);
            } else {
                localStorage.removeItem('lastDiningTableUpdate');
            }
        }
    },
}
