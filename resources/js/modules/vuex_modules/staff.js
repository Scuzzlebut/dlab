import Vue from 'vue'

const state = {
    staff: {},
    staff_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: [],
        sortDesc: [true],
        search: '',
        all: false
    },
    currentStaff: {},
    showCreateEditStaff: false,
    _staff_loading: 0,
    _omit_changes_staff: ['attachments'],
    relatedstaff: null,
    _relatedstaff_loading: 0,
    staffroles: null,
    _staffroles_loading: 0,
    stafftypes: null,
    _stafftypes_loading: 0
}

const getters = {
    getStaff: state => state.staff,
    getStaffOptions: state => state.staff_options,
    getCurrentStaff: state => state.currentStaff,
    showCreateEditStaff: state => state.showCreateEditStaff,
    staff_loading: state => state._staff_loading != 0,
    getOmitChangesStaff: state => state._omit_changes_staff,
    getRelatedStaff: state => state.relatedstaff,
    relatedstaff_loading: state => state._relatedstaff_loading != 0,
    getStaffRoles: state => state.staffroles,
    staffroles_loading: state => state._staffroles_loading != 0,
    getStaffTypes: state => state.stafftypes,
    stafftypes_loading: state => state._stafftypes_loading != 0
}

const actions = {
    createStaff({ commit, dispatch, rootState }) {
        commit("START_STAFF_LOADING");
        return new Promise((resolve, reject) => {
            if (!state.currentStaff.id) {
                axios.post('/api/staff', state.currentStaff)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('setCurrentModelForAttachment', {
                                object: res.data.object,
                                filecategory: 'staff',
                            })
                            if (!_.isEmpty(rootState.attachment.temporaryAttachment)) {
                                Object.keys(rootState.attachment.temporaryAttachment).forEach(newattachmentindex => {
                                    dispatch('uploadTemporaryAttachment', rootState.attachment.temporaryAttachment[newattachmentindex]).then(attachmentres => {
                                        if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                            dispatch('fetchStaff')
                                        }
                                    })
                                        .catch(err => {
                                            if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                                dispatch('fetchStaff')
                                            }
                                        })
                                })
                            }
                            else {
                                dispatch('fetchStaff')
                            }
                        }
                        dispatch('handleResponseMessage', res.data)
                        resolve(res.data)
                    })
                    .catch(err => {
                        dispatch('handleError', err);
                        reject(err)
                    })
                    .then(function () {
                        commit("STOP_STAFF_LOADING")
                    });
            }
            else {
                if (rootState.global.currentroute == 'user') {
                    axios.put('/api/staff/' + state.currentStaff.id + '/edit', state.currentStaff)
                        .then(res => {
                            if (res.data.object != null) {
                                dispatch('fetchUser');
                            }
                            dispatch('handleResponseMessage', res.data)
                            resolve(res.data)
                        })
                        .catch(err => {
                            dispatch('handleError', err);
                            reject(err)
                        })
                        .then(function () {
                            commit("STOP_STAFF_LOADING")
                        });
                }
                else {
                    axios.put('/api/staff/' + state.currentStaff.id, state.currentStaff)
                        .then(res => {
                            if (res.data.object != null) {
                                dispatch('fetchStaff');
                            }
                            dispatch('handleResponseMessage', res.data)
                            resolve(res.data)
                        })
                        .catch(err => {
                            dispatch('handleError', err);
                            reject(err)
                        })
                        .then(function () {
                            commit("STOP_STAFF_LOADING")
                        });
                }
            }
        })
    },
    fetchStaff({ commit, dispatch }) {
        commit('START_STAFF_LOADING');
        axios.get('/api/staff', { params: state.staff_options })
            .then(res => {
                commit('setStaff', res.data);
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(() => {
                commit('STOP_STAFF_LOADING');
            })
    },
    deleteStaff({ commit, dispatch, rootState }, id) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/staff/' + id)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_STAFF_LOADING');
                    if (state._staff_loading == 0) {
                        dispatch('fetchStaff')
                    }
                });
        });
    },
    fetchRelatedStaff({ commit, dispatch, rootState }) {
        commit('START_RELATEDSTAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/staff/' + rootState.user.user.staff.id + '/get-staff')
                .then(res => {
                    commit('setRelatedStaff', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_RELATEDSTAFF_LOADING")
                });
        })
    },
    fetchStaffRoles({ commit, dispatch }) {
        commit('START_STAFFROLES_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/utility/get-staff-roles')
                .then(res => {
                    commit('setStaffRoles', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_STAFFROLES_LOADING")
                });
        })
    },
    fetchStaffTypes({ commit, dispatch }) {
        commit('START_STAFFTYPES_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/utility/get-staff-types')
                .then(res => {
                    commit('setStaffTypes', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_STAFFTYPES_LOADING")
                });
        })
    },
    createLogin({ commit, dispatch }, params) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/staff/' + state.currentStaff.id + '/activate-login', params)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('setChanges_not_saved', false)
                        dispatch('hideAction', 'hideCreateEditStaff')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_STAFF_LOADING');
                    if (state._staff_loading == 0) {
                        dispatch('fetchStaff')
                    }
                });
        });
    },
    disableLogin({ commit, dispatch }) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/staff/' + state.currentStaff.id + '/disable-login')
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('setChanges_not_saved', false)
                        dispatch('hideAction', 'hideCreateEditStaff')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_STAFF_LOADING');
                    if (state._staff_loading == 0) {
                        dispatch('fetchStaff')
                    }
                });
        });
    },
    disableStaff({ commit, dispatch }) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/staff/' + state.currentStaff.id + '/disable', state.currentStaff)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('setChanges_not_saved', false)
                        dispatch('hideAction', 'hideCreateEditStaff')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_STAFF_LOADING');
                    if (state._staff_loading == 0) {
                        dispatch('fetchStaff')
                    }
                });
        });
    },
    showStaffDetails({ commit, dispatch }) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/staff/' + state.currentStaff.id)
                .then((res) => {
                    dispatch('setCurrentModelForAttachment', {
                        object: res.data,
                        filecategory: 'staff'
                    })
                    resolve(res.data)
                })
                .catch((err) => {
                    dispatch('handleError', err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_STAFF_LOADING")
                });
        })
    },
    setManagers({ commit, dispatch }, params) {
        commit('START_STAFF_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/staff/' + state.currentStaff.id + '/set-managers', params)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('setChanges_not_saved', false)
                        dispatch('hideAction', 'hideCreateEditStaff')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_STAFF_LOADING');
                    if (state._staff_loading == 0) {
                        dispatch('fetchStaff')
                    }
                });
        });
    },
}

const mutations = {
    setCurrentStaff(state, value) {
        Vue.set(state, 'currentStaff', value)
        state.currentStaff = JSON.parse(JSON.stringify(state.currentStaff))
    },
    START_STAFF_LOADING(state) {
        state._staff_loading++
    },
    STOP_STAFF_LOADING(state) {
        state._staff_loading--
    },
    setStaff(state, value) {
        Vue.set(state, 'staff', value)
        state.staff = JSON.parse(JSON.stringify(state.staff))
    },
    showCreateEditStaff(state) {
        state.showCreateEditStaff = true;
    },
    hideCreateEditStaff(state) {
        state.showCreateEditStaff = false;
    },
    setStaffOptions(state, value) {
        Vue.set(state, 'staff_options', value)
    },
    START_RELATEDSTAFF_LOADING(state) {
        state._relatedstaff_loading++
    },
    STOP_RELATEDSTAFF_LOADING(state) {
        state._relatedstaff_loading--
    },
    setRelatedStaff(state, value) {
        Vue.set(state, 'relatedstaff', value)
    },
    START_STAFFROLES_LOADING(state) {
        state._staffroles_loading++
    },
    STOP_STAFFROLES_LOADING(state) {
        state._staffroles_loading--
    },
    setStaffRoles(state, value) {
        Vue.set(state, 'staffroles', value)
    },
    START_STAFFTYPES_LOADING(state) {
        state._stafftypes_loading++
    },
    STOP_STAFFTYPES_LOADING(state) {
        state._stafftypes_loading--
    },
    setStaffTypes(state, value) {
        Vue.set(state, 'stafftypes', value)
    },
}

export default {
    state,
    getters,
    actions,
    mutations,
}
