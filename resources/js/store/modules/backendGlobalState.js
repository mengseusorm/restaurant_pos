import axios from "axios";
import appService from "../../services/appService";

export const backendGlobalState = {
    namespaced: true,
    state: {
        branches: [],
        branchShow: {},
        printerShow:{},
        paymentMethods:{},
        showMenuType: parseInt(localStorage.getItem('showMenuType')) || 3, // Default to POS (menuTypeEnum.POS)
        lastBranchesUpdate: null,
        lastBranchShowUpdate: null,
        lastPrinterShowUpdate: null,
        lastPaymentMethodsUpdate: null,
    },
    getters: {
        branches: function (state) {
            return state.branches;
        },
        branchShow: function (state) {
            return state.branchShow;
        },
        printerShow: function (state) {
            return state.printerShow
        },
        paymentMethods: function (state) {
            return state.paymentMethods
        },
        showMenuType: function (state) {
            return state.showMenuType
        },
        lastBranchesUpdate: function (state) {
            return state.lastBranchesUpdate;
        },
        lastBranchShowUpdate: function (state) {
            return state.lastBranchShowUpdate;
        },
        lastPrinterShowUpdate: function (state) {
            return state.lastPrinterShowUpdate;
        },
        lastPaymentMethodsUpdate: function (state) {
            return state.lastPaymentMethodsUpdate;
        }
    },
    actions: {
        branches: function (context, payload = {}) {
            return new Promise((resolve, reject) => {
                // Check if we have cached data and last update timestamp
                const lastUpdate = context.state.lastBranchesUpdate;
                
                // If cache exists and no force refresh, check for updates
                if (lastUpdate && !payload?.forceRefresh && context.state.branches.length > 0) {
                    // Send last_updated parameter to backend
                    payload = { ...payload, last_updated: lastUpdate };
                }
                
                let url = "admin/setting/branch";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                
                axios.get(url).then((res) => {
                    // If no updates, return cached data
                    if (res.data.has_updates === false) {
                        resolve({
                            status: true,
                            data: { data: context.state.branches }
                        });
                        return;
                    }
                    
                    // Updates found - merge with existing data
                    if (res.data.data && res.data.data.length > 0) {
                        const timestamp = new Date().toISOString();
                        context.commit('lastBranchesUpdate', timestamp);
                        
                        // Merge new/updated branches with existing ones
                        if (lastUpdate && !payload?.forceRefresh) {
                            context.commit('mergeUpdatedBranches', res.data.data);
                        } else {
                            if (typeof payload.vuex === "undefined" || payload.vuex === true) {
                                context.commit("branches", res.data.data);
                            }
                        }
                    } else {
                        if (typeof payload.vuex === "undefined" || payload.vuex === true) {
                            context.commit("branches", res.data.data);
                        }
                    }
                    
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        forceRefreshBranches: function(context, payload) {
            return context.dispatch('branches', { ...payload, forceRefresh: true });
        },
        clearBranchesCache: function(context) {
            context.commit('lastBranchesUpdate', null);
        },
        branchShow: function (context, payload) {
            return new Promise((resolve, reject) => {
                // Extract the branch ID (could be object with id or just id)
                const branchId = typeof payload === 'object' ? payload.id : payload;
                const forceRefresh = typeof payload === 'object' ? payload.forceRefresh : false;
                
                // Check if we have cached data and last update timestamp
                const lastUpdate = context.state.lastBranchShowUpdate;
                const cachedBranch = context.state.branchShow;
                
                // If cache exists for this branch and no force refresh
                if (lastUpdate && !forceRefresh && cachedBranch && cachedBranch.id == branchId) {
                    // Build URL with last_updated parameter
                    let url = `admin/setting/branch/show/${branchId}?last_updated=${lastUpdate}`;
                    
                    axios.get(url).then((res) => {
                        // If no updates, return cached data
                        if (res.data.has_updates === false) {
                            resolve({
                                status: true,
                                data: { data: cachedBranch }
                            });
                            return;
                        }
                        
                        // Updates found - store new data
                        if (res.data.data) {
                            const timestamp = new Date().toISOString();
                            context.commit('lastBranchShowUpdate', timestamp);
                            context.commit("branchShow", res.data.data);
                        }
                        
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                } else {
                    // No cache or different branch - fetch fresh data
                    axios.get(`admin/setting/branch/show/${branchId}`).then((res) => {
                        const timestamp = new Date().toISOString();
                        context.commit('lastBranchShowUpdate', timestamp);
                        context.commit("branchShow", res.data.data);  
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },
        forceRefreshBranchShow: function(context, payload) {
            const branchId = typeof payload === 'object' ? payload.id : payload;
            return context.dispatch('branchShow', { id: branchId, forceRefresh: true });
        },
        clearBranchShowCache: function(context) {
            context.commit('lastBranchShowUpdate', null);
        },
        printerShow: function (context, payload) {  
            return new Promise((resolve, reject) => {
                // Extract the printer ID (could be object with id or just id)
                const printerId = typeof payload === 'object' ? payload.id : payload;
                const forceRefresh = typeof payload === 'object' ? payload.forceRefresh : false;
                
                // Check if we have cached data and last update timestamp
                const lastUpdate = context.state.lastPrinterShowUpdate;
                const cachedPrinter = context.state.printerShow;
                
                // If cache exists for this printer and no force refresh
                if (lastUpdate && !forceRefresh && cachedPrinter && cachedPrinter.id == printerId) {
                    // Build URL with last_updated parameter
                    let url = `admin/setting/kitchen-printer/show/${printerId}?last_updated=${lastUpdate}`;
                    
                    axios.get(url).then((res) => {
                        // If no updates, return cached data
                        if (res.data.has_updates === false) {
                            resolve({
                                status: true,
                                data: { data: cachedPrinter }
                            });
                            return;
                        }
                        
                        // Updates found - store new data
                        if (res.data.data) {
                            const timestamp = new Date().toISOString();
                            context.commit('lastPrinterShowUpdate', timestamp);
                            context.commit("printerShow", res.data.data);
                        }
                        
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                } else {
                    // No cache or different printer - fetch fresh data
                    axios.get(`admin/setting/kitchen-printer/show/${printerId}`).then((res) => {
                        const timestamp = new Date().toISOString();
                        context.commit('lastPrinterShowUpdate', timestamp);
                        context.commit("printerShow", res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },
        forceRefreshPrinterShow: function(context, payload) {
            const printerId = typeof payload === 'object' ? payload.id : payload;
            return context.dispatch('printerShow', { id: printerId, forceRefresh: true });
        },
        clearPrinterShowCache: function(context) {
            context.commit('lastPrinterShowUpdate', null);
        },
        paymentMethods: function (context, payload = {}) {
            return new Promise((resolve, reject) => {

                // No cache or force refresh - fetch fresh data
                axios.get('admin/setting/payment-method').then((res) => {
                    const timestamp = new Date().toISOString();
                    context.commit('lastPaymentMethodsUpdate', timestamp);
                    context.commit("paymentMethods", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
                
                // Check if we have cached data and last update timestamp
                // const lastUpdate = context.state.lastPaymentMethodsUpdate;
                
                // // If cache exists and no force refresh, check for updates
                // // Note: paymentMethods returns object, so check Object.keys().length
                // if (lastUpdate && !payload?.forceRefresh && Object.keys(context.state.paymentMethods).length > 0) {
                //     // Build URL with last_updated parameter
                //     let url = `admin/setting/payment-method?last_updated=${lastUpdate}`;
                    
                //     axios.get(url).then((res) => {
                //         // If no updates, return cached data
                //         if (res.data.has_updates === false) {
                //             resolve({
                //                 status: true,
                //                 data: { data: context.state.paymentMethods }
                //             });
                //             return;
                //         }
                        
                //         // Updates found - store new data
                //         if (res.data.data) {
                //             const timestamp = new Date().toISOString();
                //             context.commit('lastPaymentMethodsUpdate', timestamp);
                //             context.commit("paymentMethods", res.data.data);
                //         }
                        
                //         resolve(res);
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // } else {
                //     // No cache or force refresh - fetch fresh data
                //     axios.get('admin/setting/payment-method').then((res) => {
                //         const timestamp = new Date().toISOString();
                //         context.commit('lastPaymentMethodsUpdate', timestamp);
                //         context.commit("paymentMethods", res.data.data);
                //         resolve(res);
                //     }).catch((err) => {
                //         reject(err);
                //     });
                // }
            }); 
        },
        forceRefreshPaymentMethods: function(context, payload) {
            return context.dispatch('paymentMethods', { ...payload, forceRefresh: true });
        },
        clearPaymentMethodsCache: function(context) {
            context.commit('lastPaymentMethodsUpdate', null);
        },
        updateShowMenuType: function(context, payload) {
            context.commit('showMenuType', payload);
        }
    },
    mutations: {
        branches: function (state, payload) {
            state.branches = payload;
        },
        branchShow: function (state, payload) {
            state.branchShow = payload;
        },
        printerShow: function (state, payload) {
            state.printerShow = payload;
        },
        paymentMethods: function (state, payload) {
            state.paymentMethods = payload;
        },
        showMenuType: function (state, payload) {
            state.showMenuType = payload;
            // Persist to localStorage
            localStorage.setItem('showMenuType', payload);
        },
        lastBranchesUpdate: function (state, payload) {
            state.lastBranchesUpdate = payload;
            // Also persist to localStorage for cross-session caching
            if (payload) {
                localStorage.setItem('lastBranchesUpdate', payload);
            } else {
                localStorage.removeItem('lastBranchesUpdate');
            }
        },
        mergeUpdatedBranches: function (state, payload) {
            // Create a map of existing branches for O(1) lookup
            const branchMap = new Map(state.branches.map(branch => [branch.id, branch]));
            
            // Update or add new branches
            payload.forEach(updatedBranch => {
                branchMap.set(updatedBranch.id, updatedBranch);
            });
            
            // Convert map back to array
            state.branches = Array.from(branchMap.values());
        },
        lastBranchShowUpdate: function (state, payload) {
            state.lastBranchShowUpdate = payload;
            // Also persist to localStorage for cross-session caching
            if (payload) {
                localStorage.setItem('lastBranchShowUpdate', payload);
            } else {
                localStorage.removeItem('lastBranchShowUpdate');
            }
        },
        lastPrinterShowUpdate: function (state, payload) {
            state.lastPrinterShowUpdate = payload;
            // Also persist to localStorage for cross-session caching
            if (payload) {
                localStorage.setItem('lastPrinterShowUpdate', payload);
            } else {
                localStorage.removeItem('lastPrinterShowUpdate');
            }
        },
        lastPaymentMethodsUpdate: function (state, payload) {
            state.lastPaymentMethodsUpdate = payload;
            // Also persist to localStorage for cross-session caching
            if (payload) {
                localStorage.setItem('lastPaymentMethodsUpdate', payload);
            } else {
                localStorage.removeItem('lastPaymentMethodsUpdate');
            }
        }
    },
};
