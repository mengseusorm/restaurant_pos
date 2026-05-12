import axios from 'axios'
import appService from "../../services/appService";


export const itemCategory = {
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
        lastItemCategoryUpdate: localStorage.getItem('lastItemCategoryUpdate') || null,
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
        lastItemCategoryUpdate: function (state) {
            return state.lastItemCategoryUpdate;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/setting/item-category';
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
            //     // Check if we should use cached categories
            //     const lastUpdate = context.state.lastItemCategoryUpdate;
            //     const checkForUpdates = payload && payload.checkUpdates !== false;

            //     // If we have a last update timestamp and categories in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new categories
            //         const checkPayload = { ...payload, last_updated: lastUpdate };
            //         let url = 'admin/setting/item-category' + appService.requestHandler(checkPayload);

            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached categories from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Categories were updated, merge with existing categories
            //                 if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                     // Merge updated categories with existing cached categories
            //                     context.commit('mergeUpdatedCategories', res.data.data);
            //                     context.commit('page', res.data.meta);
            //                     context.commit('pagination', res.data);
            //                     // Update the timestamp to current time
            //                     context.commit('setLastItemCategoryUpdate', new Date().toISOString());
            //                 }
            //                 resolve(res);
            //             }
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     } else {
            //         // First time fetch or forced refresh
            //         let url = 'admin/setting/item-category';
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios.get(url).then((res) => {
            //             if(typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                 context.commit('lists', res.data.data);
            //                 context.commit('page', res.data.meta);
            //                 context.commit('pagination', res.data);
            //                 // Set the timestamp for first fetch
            //                 context.commit('setLastItemCategoryUpdate', new Date().toISOString());
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
                let url = '/admin/setting/item-category';
                if (this.state['itemCategory'].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/setting/item-category/${this.state['itemCategory'].temp.temp_id}`;
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
                axios.delete(`admin/setting/item-category/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        destroyFromItem: function (context, payload) {
            console.log('payload', payload);
            return new Promise((resolve, reject) => {
                axios.delete(`admin/setting/item-category/destroy-from-item/${payload.id}`).then((res) => {
                    context.dispatch('lists', payload.search).then().catch();
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/setting/item-category/show/${payload}`).then((res) => {
                    context.commit('show', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        sortCategory: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = '/admin/setting/item-category/sort/category';
                method(url, payload.form).then(res => {
                    context.dispatch('lists', payload.search).then().catch();
                    context.commit('reset');
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/setting/item-category/export';
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
                let url = 'admin/setting/item-category/download-sample/';
                axios.get(url, { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        import: function (context, payload) {
            console.log('payload', payload);
            return new Promise((resolve, reject) => {
                axios.post('/admin/setting/item-category/import/file', payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        uploadImages: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post( '/admin/setting/item-category/upload-images',
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
            // Force refresh categories by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastItemCategoryUpdate', null);
            localStorage.removeItem('lastItemCategoryUpdate');
        },
        applyItemCategoryPrinter: function (context, payload) {
            return new Promise((resolve, reject) => {
                    let method = axios.post;
                    let url = '/admin/apply-printer';
                    method(url, payload.form).then(res => {
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                });
            }
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
        setLastItemCategoryUpdate: function(state, timestamp) {
            state.lastItemCategoryUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastItemCategoryUpdate', timestamp);
            } else {
                localStorage.removeItem('lastItemCategoryUpdate');
            }
        }
    },
}
