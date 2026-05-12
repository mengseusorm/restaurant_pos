import axios from "axios";
import appService from "../../services/appService";

export const language = {
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
        fileList: [],
        fileText: {},
        lastLanguagesUpdate: localStorage.getItem('lastLanguagesUpdate') || null,
        lastQueryTime: null, // Track last query time for cache duration check
        cacheDuration: 3000000, // Cache duration in milliseconds (30 seconds default)
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
        fileList: function (state) {
            return state.fileList;
        },
        fileText: function (state) {
            return state.fileText;
        },
        lastLanguagesUpdate: function (state) {
            return state.lastLanguagesUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/setting/language";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
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
                        reject(err);
                    });
            });
            // return new Promise((resolve, reject) => {
            //     // Check if we should use cached languages
            //     const lastUpdate = context.state.lastLanguagesUpdate;
            //     const lastQueryTime = context.state.lastQueryTime;
            //     const cacheDuration = context.state.cacheDuration;
            //     const checkForUpdates = !payload || payload.checkUpdates !== false;
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

            //     // If we have a last update timestamp and languages in store, check for updates
            //     if (checkForUpdates && lastUpdate && context.state.lists.length > 0) {
            //         // Add last_updated parameter to check for new languages
            //         const checkPayload = payload ? { ...payload, last_updated: lastUpdate } : { last_updated: lastUpdate };
            //         let url = "admin/setting/language" + appService.requestHandler(checkPayload);

            //         axios.get(url).then((res) => {
            //             // Check if response indicates no updates
            //             if (res.data.has_updates === false) {
            //                 // Return cached languages from store
            //                 resolve({
            //                     data: {
            //                         data: context.state.lists,
            //                         meta: context.state.page,
            //                     },
            //                     fromCache: true
            //                 });
            //             } else {
            //                 // Languages were updated, merge with existing languages
            //                 if (typeof payload.vuex === "undefined" || payload.vuex === true) {
            //                     // Merge updated languages with existing cached languages
            //                     context.commit("mergeUpdatedLanguages", res.data.data);
            //                     context.commit("page", res.data.meta);
            //                     context.commit("pagination", res.data);
            //                     // Update the timestamp to current time
            //                     context.commit("setLastLanguagesUpdate", new Date().toISOString());
            //                     context.commit("setLastQueryTime", Date.now());
            //                 }
            //                 resolve(res);
            //             }
            //         }).catch((err) => {
            //             reject(err);
            //         });
            //     } else {
            //         // First time fetch or forced refresh
            //         let url = "admin/setting/language";
            //         if (payload) {
            //             url = url + appService.requestHandler(payload);
            //         }
            //         axios
            //             .get(url)
            //             .then((res) => {
            //                 if (
            //                     typeof payload.vuex === "undefined" ||
            //                     payload.vuex === true
            //                 ) {
            //                     context.commit("lists", res.data.data);
            //                     context.commit("page", res.data.meta);
            //                     context.commit("pagination", res.data);
            //                     // Set the timestamp for first fetch
            //                     context.commit("setLastLanguagesUpdate", new Date().toISOString());
            //                     context.commit("setLastQueryTime", Date.now());
            //                 }
            //                 resolve(res);
            //             })
            //             .catch((err) => {
            //                 reject(err);
            //             });
            //     }
            // });
        },
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = "/admin/setting/language";
                if (this.state["language"].temp.isEditing) {
                    method = axios.post;
                    url = `/admin/setting/language/update/${this.state["language"].temp.temp_id}`;
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
                    .delete(`admin/setting/language/${payload.id}`)
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
                axios.get(`admin/setting/language/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        fileList: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = `/admin/setting/language/file-list/${payload}`;
                axios.get(url).then((res) => {
                    context.commit("fileList", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        fileText: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = `/admin/setting/language/file-text`;
                axios.post(url, payload).then((res) => {
                    context.commit("fileText", res.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        fileStore: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = `/admin/setting/language/file-text/store`;
                axios.post(url, payload).then((res) => {
                    context.commit("resetFileText");
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
            // Force refresh languages by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastLanguagesUpdate', null);
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
        fileList: function (state, payload) {
            state.fileList = payload;
        },
        fileText: function (state, payload) {
            state.fileText = payload;
        },
        resetFileText: function (state, payload) {
            state.fileText = {};
        },
        setLastLanguagesUpdate: function(state, timestamp) {
            state.lastLanguagesUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastLanguagesUpdate', timestamp);
            } else {
                localStorage.removeItem('lastLanguagesUpdate');
            }
        },
        mergeUpdatedLanguages: function (state, updatedLanguages) {
            // Create a map of existing languages by ID for quick lookup
            const languagesMap = new Map(state.lists.map(language => [language.id, language]));

            // Update or add languages from the updated list
            updatedLanguages.forEach(updatedLanguage => {
                languagesMap.set(updatedLanguage.id, updatedLanguage);
            });

            // Convert map back to array
            state.lists = Array.from(languagesMap.values());
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    }
};
