import axios from 'axios'

export const frontDesk = {
    namespaced: true,
    state: {
        board: {
            rooms: [],
            therapists: [],
            group_sessions: [],
            summary: {},
        },
        loading: false,
    },
    getters: {
        board:         (state) => state.board,
        rooms:         (state) => state.board.rooms ?? [],
        therapists:    (state) => state.board.therapists ?? [],
        groupSessions: (state) => state.board.group_sessions ?? [],
        summary:       (state) => state.board.summary ?? {},
        loading:       (state) => state.loading,
    },
    actions: {
        loadBoard(context, branchId) {
            return new Promise((resolve, reject) => {
                context.commit('setLoading', true);
                let url = 'admin/front-desk/board';
                if (branchId) { url += `?branch_id=${branchId}`; }
                axios.get(url).then((res) => {
                    context.commit('setBoard', res.data.data);
                    resolve(res);
                }).catch((err) => { reject(err); })
                  .finally(() => { context.commit('setLoading', false); });
            });
        },
        openSession(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/massage-session', payload).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        createGroup(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/group-session', payload).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        startSession(context, id) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${id}/start`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        completeSession(context, id) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${id}/complete`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        addItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/add-item`, payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        updateItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.patch(`admin/massage-session/${payload.id}/update-item/${payload.sessionItemId}`, payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        changeStartTime(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${payload.id}/change-start-time`, payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        removeItem(context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/massage-session/${payload.id}/remove-item/${payload.sessionServiceItemId}`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
        checkout(context, { id }) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/massage-session/${id}/checkout`).then((res) => {
                    resolve(res);
                }).catch((err) => { reject(err); });
            });
        },
    },
    mutations: {
        setBoard(state, payload)   { state.board   = payload; },
        setLoading(state, payload) { state.loading = payload; },
    },
};
