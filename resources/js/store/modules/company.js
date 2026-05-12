import axios from 'axios'


export const company = {
    namespaced: true,
    state: {
        lists: [],
        lastCompanyUpdate: localStorage.getItem('lastCompanyUpdate') || null,
        lastQueryTime: null, // Track last query time for cache duration check
        cacheDuration: 3000000, // Cache duration in milliseconds (30 seconds default)
    },

    getters: {
        lists: function (state) {
            return state.lists;
        },
        lastCompanyUpdate: function (state) {
            return state.lastCompanyUpdate;
        },
        lastQueryTime: function (state) {
            return state.lastQueryTime;
        }
    },

    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                // Check if we should use cached company data
                const lastUpdate = context.state.lastCompanyUpdate;
                const lastQueryTime = context.state.lastQueryTime;
                const cacheDuration = context.state.cacheDuration;
                const checkForUpdates = !payload || payload.checkUpdates !== false;
                const now = Date.now();
                
                // Fast cache: Return data from state if queried recently (within cache duration)
                if (checkForUpdates && lastQueryTime && context.state.lists && Object.keys(context.state.lists).length > 0) {
                    const timeSinceLastQuery = now - lastQueryTime;
                    if (timeSinceLastQuery < cacheDuration) {
                        resolve({
                            data: {
                                data: context.state.lists,
                            },
                            fromStateCache: true,
                            cacheAge: timeSinceLastQuery
                        });
                        return;
                    }
                }
                
                // If we have a last update timestamp and company data in store, check for updates
                if (checkForUpdates && lastUpdate && context.state.lists && Object.keys(context.state.lists).length > 0) {
                    // Add last_updated parameter to check for updates
                    let url = 'admin/setting/company?last_updated=' + lastUpdate;
                    
                    axios.get(url).then((res) => {
                        // Check if response indicates no updates
                        if (res.data.has_updates === false) {
                            // Return cached company data from store
                            resolve({
                                data: {
                                    data: context.state.lists,
                                },
                                fromCache: true
                            });
                        } else {
                            // Company data was updated
                            context.commit('lists', res.data.data);
                            // Update the timestamp to current time
                            context.commit('setLastCompanyUpdate', new Date().toISOString());
                            context.commit('setLastQueryTime', Date.now());
                            resolve(res);
                        }
                    }).catch((err) => {
                        reject(err);
                    });
                } else {
                    // First time fetch or forced refresh
                    axios.get('admin/setting/company').then((res) => {
                        context.commit('lists', res.data.data);
                        // Set the timestamp for first fetch
                        context.commit('setLastCompanyUpdate', new Date().toISOString());
                        context.commit('setLastQueryTime', Date.now());
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.put(`/admin/setting/company`, payload).then(res => {
                    context.commit('lists', payload);
                    // Update timestamp after save
                    context.commit('setLastCompanyUpdate', new Date().toISOString());
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        forceRefresh: function (context, payload) {
            // Force refresh company data by bypassing the cache check
            const forcePayload = { ...payload, checkUpdates: false };
            return context.dispatch('lists', forcePayload);
        },
        clearCache: function (context) {
            // Clear the last update timestamp to force a full refresh on next fetch
            context.commit('setLastCompanyUpdate', null);
            localStorage.removeItem('lastCompanyUpdate');
        }
    },

    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        setLastCompanyUpdate: function(state, timestamp) {
            state.lastCompanyUpdate = timestamp;
            if (timestamp) {
                localStorage.setItem('lastCompanyUpdate', timestamp);
            } else {
                localStorage.removeItem('lastCompanyUpdate');
            }
        },
        setLastQueryTime: function(state, timestamp) {
            state.lastQueryTime = timestamp;
        }
    },
}
