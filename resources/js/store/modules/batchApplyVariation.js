import axios from "axios";

export const batchApplyVariation = {
    namespaced: true,
    state: {},
    getters: {},
    actions: {
        getData: function () {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/setting/batch-apply-variation")
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        apply: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post("admin/setting/batch-apply-variation", payload)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        clearAll: function () {
            return new Promise((resolve, reject) => {
                axios
                    .delete("admin/setting/batch-apply-variation/clear-all")
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        updateItemPrice: function (context, { itemId, price }) {
            return new Promise((resolve, reject) => {
                axios
                    .put(`admin/setting/batch-apply-variation/item/${itemId}/price`, { price })
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
    },
    mutations: {},
};
