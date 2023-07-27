import Vue from 'vue'

const state = {
    paysheet: {},
    paysheet_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
        search: '',
        year: null,
        month: null
    },
    currentPaysheet: {},
    showCreateEditPaysheet: false,
    _paysheet_loading: 0,
    _omit_changes_paysheet: ['attachments','staff','creator'],
    _importpaysheet_loading: 0,
    showImportPaysheet: false,
    readedPaysheetImport: null,
}

const getters = {
    getPaysheet: state => state.paysheet,
    getPaysheetOptions: state => state.paysheet_options,
    getCurrentPaysheet: state => state.currentPaysheet,
    showCreateEditPaysheet: state => state.showCreateEditPaysheet,
    paysheet_loading: state => state._paysheet_loading != 0,
    getOmitChangesPaysheet: state => state._omit_changes_paysheet,
    importpaysheet_loading: state => state._importpaysheet_loading !== 0,
    showImportPaysheet: state => state.showImportPaysheet,
    getReadedPaysheetImport: state => state.readedPaysheetImport,
}

const actions = {
    createPaysheet({ commit, dispatch, rootState }) {
        commit("START_PAYSHEET_LOADING");
        return new Promise((resolve, reject) => {
            if (state.currentPaysheet.id) {
                axios.put('/api/paysheet/' + state.currentPaysheet.id, state.currentPaysheet)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('fetchPaysheet');
                        }
                        dispatch('handleResponseMessage', res.data)
                        resolve(res.data)
                    })
                    .catch(err => {
                        dispatch('handleError', err);
                        reject(err)
                    })
                    .then(function () {
                        commit("STOP_PAYSHEET_LOADING")
                    });
            }
        })
    },
    fetchPaysheet({ commit, dispatch }) {
        commit('START_PAYSHEET_LOADING');
        axios.get('/api/paysheet', { params: state.paysheet_options })
            .then(res => {
                commit('setPaysheet', res.data);
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(() => {
                commit('STOP_PAYSHEET_LOADING');
            })
    },
    deletePaysheet({ commit, dispatch, rootState }, id) {
        commit('START_PAYSHEET_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/paysheet/' + id)
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_PAYSHEET_LOADING');
                    if (state._paysheet_loading == 0) {
                        dispatch('fetchPaysheet')
                    }
                });
        });
    },
    showPaysheetDetails({ commit, dispatch }) {
        commit('START_PAYSHEET_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/paysheet/' + state.currentPaysheet.id)
                .then((res) => {
                    dispatch('setCurrentModelForAttachment', {
                        object: res.data,
                        filecategory: 'paysheet'
                    })
                    resolve(res.data)
                })
                .catch((err) => {
                    dispatch('handleError', err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_PAYSHEET_LOADING")
                });
        })
    },
    importPaysheetRead({ commit, dispatch }, formData) {
        commit('START_IMPORTPAYSHEET_LOADING');
        const source = axios.CancelToken.source();
        commit('setSource', source)
        commit('setProgress', { total: 100, loaded: 0 })
        return new Promise((resolve, reject) => {
            axios.post('/api/paysheet/upload', formData, {
                cancelToken: source.token,
                onUploadProgress: (e) => {
                    commit('setProgress', { total: e.total, loaded: e.loaded });
                }
            })
                .then(res => {
                    if (res.data.object) {
                        commit('setReadedPaysheetImport', res.data.object)
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_IMPORTPAYSHEET_LOADING")
                });
        });
    },
    importPaysheetStore({ commit, dispatch }) {
        commit("START_IMPORTPAYSHEET_LOADING");
        return new Promise((resolve, reject) => {
            axios.post('/api/paysheet/store', {data: state.readedPaysheetImport})
                .then(res => {
                    if (res.data.paysheets != null) {
                        dispatch('hideAction', 'hideImportPaysheet')
                        dispatch('fetchPaysheet')
                    }
                    dispatch('handleResponseMessage', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_IMPORTPAYSHEET_LOADING")
                });
        })
    },
    setPaysheetAsDownloaded({ commit, dispatch }, id) {
        commit('START_PAYSHEET_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/paysheet/' + id + '/set-downloaded')
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_PAYSHEET_LOADING');
                    if (state._paysheet_loading == 0) {
                        dispatch('fetchPaysheet')
                    }
                });
        });
    },
}

const mutations = {
    setCurrentPaysheet(state, value) {
        Vue.set(state, 'currentPaysheet', value)
        state.currentPaysheet = JSON.parse(JSON.stringify(state.currentPaysheet))
    },
    START_PAYSHEET_LOADING(state) {
        state._paysheet_loading++
    },
    STOP_PAYSHEET_LOADING(state) {
        state._paysheet_loading--
    },
    setPaysheet(state, value) {
        Vue.set(state, 'paysheet', value)
        state.paysheet = JSON.parse(JSON.stringify(state.paysheet))
    },
    showCreateEditPaysheet(state) {
        state.showCreateEditPaysheet = true;
    },
    hideCreateEditPaysheet(state) {
        state.showCreateEditPaysheet = false;
    },
    setPaysheetOptions(state, value) {
        Vue.set(state, 'paysheet_options', value)
    },
    START_PAYSHEETTYPES_LOADING(state) {
        state._paysheettypes_loading++
    },
    STOP_PAYSHEETTYPES_LOADING(state) {
        state._paysheettypes_loading--
    },
    setPaysheetTypes(state, value) {
        Vue.set(state, 'paysheettypes', value)
    },
    START_PAYSHEETCALENDAR_LOADING(state) {
        state._paysheetcalendar_loading++
    },
    STOP_PAYSHEETCALENDAR_LOADING(state) {
        state._paysheetcalendar_loading--
    },
    setPaysheetCalendar(state, data) {
        let cloned = _.cloneDeep(data)
        Object.keys(cloned).forEach(elementkey => {
            cloned[elementkey].start = moment(cloned[elementkey].start, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DD HH:mm:ss');
            cloned[elementkey].end = moment(cloned[elementkey].end, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DD HH:mm:ss');
        })
        Vue.set(state, 'paysheetcalendar', cloned);
    },
    setPaysheetCalendarOptions(state, value) {
        Vue.set(state, 'paysheetcalendar_options', value)
    },
    setReadedPaysheetImport(state, value) {
        Vue.set(state, 'readedPaysheetImport', value)
    },
    START_IMPORTPAYSHEET_LOADING(state) {
        state._importpaysheet_loading++
    },
    STOP_IMPORTPAYSHEET_LOADING(state) {
        state._importpaysheet_loading--
    },
    showImportPaysheet(state) {
        state.showImportPaysheet = true
    },
    hideImportPaysheet(state) {
        state.showImportPaysheet = false
        Vue.set(state, 'readedPaysheetImport', null)
    },
}

export default {
    state,
    getters,
    actions,
    mutations,
}