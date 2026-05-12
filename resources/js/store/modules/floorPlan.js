import axios from "axios";
import appService from "../../services/appService";

export const floorPlan = {
    namespaced: true,
    state: {
        groups: [],
        tablesForGroups: {},
        analytics: {
            overall: {
                total_tables: 0,
                occupied_tables: 0,
                available_tables: 0,
                occupancy_rate: 0
            },
            groups: []
        },
        page: {},
        pagination: {},
        show: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
    },
    getters: {
        groups: function (state) {
            return state.groups || [];
        },
        tablesForGroup: function (state) {
            return function (groupId) {
                if (!state.tablesForGroups || !groupId) return [];
                return state.tablesForGroups[groupId] || [];
            };
        },
        analytics: function (state) {
            return state.analytics;
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
    },

    actions: {
        loadGroups: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/floor-plan/groups";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("groups", res.data.data);
                    if (res.data.meta) {
                        context.commit("page", res.data.meta);
                        context.commit("pagination", res.data);
                    }
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        loadTablesForGroup: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/floor-plan/groups/${payload.groupId}/tables`)
                    .then((res) => {
                        context.commit("tablesForGroup", {
                            groupId: payload.groupId,
                            tables: res.data.data
                        });
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        createGroup: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post("admin/floor-plan/groups", payload)
                    .then((res) => {
                        context.dispatch("loadGroups", payload.search).then().catch();
                        context.commit("reset");
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        updateGroup: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/floor-plan/groups/${payload.groupId}`, payload.form)
                    .then((res) => {
                        context.dispatch("loadGroups", payload.search).then().catch();
                        context.commit("reset");
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        deleteGroup: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/floor-plan/groups/${payload.id}`)
                    .then((res) => {
                        context.dispatch("loadGroups", payload.search).then().catch();
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        updateTablePosition: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/floor-plan/tables/${payload.tableId}/position`, payload.form)
                    .then((res) => {
                        context.commit("updateTableInGroup", res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        updateTableProperties: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/floor-plan/tables/${payload.tableId}/properties`, payload.form)
                    .then((res) => {
                        context.commit("updateTableInGroup", res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        updateCurrentGuests: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/floor-plan/tables/${payload.tableId}/guests`, payload.form)
                    .then((res) => {
                        context.commit("updateTableInGroup", res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        loadAnalytics: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/floor-plan/analytics";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("analytics", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        getTableDetails: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/floor-plan/tables/${payload}`)
                    .then((res) => {
                        context.commit("show", res.data.data);
                        resolve(res);
                    }).catch((err) => {
                        reject(err);
                    });
            });
        },

        // releaseTable: function (context, payload) {
        //     return new Promise((resolve, reject) => {
        //         // Use the PosOrder API with the order ID
        //         const orderId = payload.orderId;
        //         axios.post(`admin/pos-order/release-dining-table/${orderId}`, payload)
        //             .then((res) => {
        //                 context.commit("updateTableInGroup", res.data.data);
        //                 resolve(res);
        //             }).catch((err) => {
        //                 reject(err);
        //             });
        //     });
        // },

        changeGroupImage: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/floor-plan/groups/change-image/${payload.id}`, payload.form, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }).then((res) => {
                    // Update the group in state
                    const groups = context.state.groups.map(group =>
                        group.id === payload.id ? res.data.data : group
                    );
                    context.commit("groups", groups);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        changeTableImage: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/floor-plan/tables/change-image/${payload.id}`, payload.form, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }).then((res) => {
                    context.commit("updateTableInGroup", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        edit: function (context, payload) {
            context.commit("temp", payload);
        },

        reset: function (context) {
            context.commit("reset");
        },
    },

    mutations: {
        groups: function (state, payload) {
            state.groups = payload;
        },
        tablesForGroup: function (state, payload) {
            state.tablesForGroups = {
                ...state.tablesForGroups,
                [payload.groupId]: payload.tables
            };
        },
        updateTableInGroup: function (state, updatedTable) {
            // Find the group that contains this table and update it
            for (const groupId in state.tablesForGroups) {
                const tables = state.tablesForGroups[groupId];
                const tableIndex = tables.findIndex(table => table.id === updatedTable.id);

                if (tableIndex !== -1) {
                    // Update the table in the current group
                    tables.splice(tableIndex, 1, updatedTable);

                    // If the table was moved to a different group, remove it from current group
                    if (updatedTable.floor_plan_group_id && updatedTable.floor_plan_group_id != groupId) {
                        tables.splice(tableIndex, 1);

                        // Add to the new group if it's loaded
                        if (state.tablesForGroups[updatedTable.floor_plan_group_id]) {
                            state.tablesForGroups[updatedTable.floor_plan_group_id].push(updatedTable);
                        }
                    }
                    break;
                }
            }
        },
        updateTableInStore: function (state, { tableId, data }) {
            // Update table position in store without API call - for local changes only
            for (const groupId in state.tablesForGroups) {
                const tables = state.tablesForGroups[groupId];
                const table = tables.find(table => table.id === tableId);

                if (table) {
                    // Update only the position-related fields
                    Object.assign(table, data);
                    break;
                }
            }
        },
        analytics: function (state, payload) {
            state.analytics = payload;
        },
        clearTablesForGroup: function (state, groupId) {
            if (state.tablesForGroups[groupId]) {
                delete state.tablesForGroups[groupId];
            }
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
    },
};
