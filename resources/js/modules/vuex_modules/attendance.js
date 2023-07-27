import Vue from 'vue'

const state = {
    attendance: {},
    attendance_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['date_start'],
        sortDesc: [true],
        search: '',
        year: null,
        month: null,
        type: null,
        accepted: null,
        staff_ids: []
    },
    currentAttendance: {},
    showCreateEditAttendance: false,
    _attendance_loading: 0,
    _omit_changes_attendance: ['attachments'],
    attendancetypes: null,
    _attendancetypes_loading: 0,
    attendancecalendar: [],
    attendancecalendar_options: {
        date_start: null,
        date_end: null,
        type: null,
        accepted: null,
        staff_ids: []
    },
    _attendancecalendar_loading: 0,
    attendanceDragData: null,
    attendance_exporting: 0
}

const getters = {
    getAttendance: state => state.attendance,
    getAttendanceOptions: state => state.attendance_options,
    getCurrentAttendance: state => state.currentAttendance,
    showCreateEditAttendance: state => state.showCreateEditAttendance,
    attendance_loading: state => state._attendance_loading != 0,
    getOmitChangesAttendance: state => state._omit_changes_attendance,
    getAttendanceTypes: state => state.attendancetypes,
    attendancetypes_loading: state => state._attendancetypes_loading != 0,
    getAttendanceCalendar: state => state.attendancecalendar,
    getAttendanceCalendarOptions: state => state.attendancecalendar_options,
    attendancecalendar_loading: state => state._attendancecalendar_loading !== 0,
    getAttendanceDragData: state => state.attendanceDragData,
    attendance_exporting: state => state.attendance_exporting != 0
}

const actions = {
    createAttendance({ commit, dispatch, rootState }) {
        commit("START_ATTENDANCE_LOADING");
        return new Promise((resolve, reject) => {
            if (!state.currentAttendance.id) {
                axios.post('/api/attendances', state.currentAttendance)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('setCurrentModelForAttachment', {
                                object: res.data.object,
                                filecategory: 'attendance',
                            })
                            if (!_.isEmpty(rootState.attachment.temporaryAttachment)) {
                                Object.keys(rootState.attachment.temporaryAttachment).forEach(newattachmentindex => {
                                    dispatch('uploadTemporaryAttachment', rootState.attachment.temporaryAttachment[newattachmentindex]).then(attachmentres => {
                                        if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                            dispatch('fetchAttendance')
                                        }
                                    })
                                        .catch(err => {
                                            if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                                dispatch('fetchAttendance')
                                            }
                                        })
                                })
                            }
                            else {
                                dispatch('fetchAttendance')
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
                        commit("STOP_ATTENDANCE_LOADING")
                    });
            }
            else {
                axios.put('/api/attendances/' + state.currentAttendance.id, state.currentAttendance)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('fetchAttendance');
                        }
                        dispatch('handleResponseMessage', res.data)
                        resolve(res.data)
                    })
                    .catch(err => {
                        dispatch('handleError', err);
                        reject(err)
                    })
                    .then(function () {
                        commit("STOP_ATTENDANCE_LOADING")
                    });
            }
        })
    },
    fetchAttendance({ commit, dispatch }) {
        commit('START_ATTENDANCE_LOADING');
        axios.get('/api/attendances', { params: state.attendance_options })
            .then(res => {
                commit('setAttendance', res.data);
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(() => {
                commit('STOP_ATTENDANCE_LOADING');
            })
    },
    fetchAttendanceCalendar({ commit, dispatch }) {
        commit('START_ATTENDANCECALENDAR_LOADING');
        axios.get('/api/attendances/calendar', { params: state.attendancecalendar_options })
            .then(res => {
                commit('setAttendanceCalendar', res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_ATTENDANCECALENDAR_LOADING")
            });
    },
    deleteAttendance({ commit, dispatch, rootState }, id) {
        commit('START_ATTENDANCE_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/attendances/' + id)
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_ATTENDANCE_LOADING');
                    if (state._attendance_loading == 0) {
                        dispatch('fetchAttendance')
                    }
                });
        });
    },
    fetchAttendanceTypes({ commit, dispatch }) {
        commit('START_ATTENDANCETYPES_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/utility/get-attendance-types')
                .then(res => {
                    commit('setAttendanceTypes', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_ATTENDANCETYPES_LOADING")
                });
        })
    },
    acceptAttendance({ commit, dispatch }) {
        commit('START_ATTENDANCE_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/attendances/' + state.currentAttendance.id + '/accepted')
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    commit('setChanges_not_saved', false)
                    dispatch('hideAction', 'hideCreateEditAttendance')

                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_ATTENDANCE_LOADING');
                    if (state._attendance_loading == 0) {
                        dispatch('fetchAttendance')
                    }
                });
        });
    },
    resetAttendance({ commit, dispatch }) {
        commit('START_ATTENDANCE_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/attendances/' + state.currentAttendance.id + '/reset')
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    commit('setChanges_not_saved', false)
                    dispatch('hideAction', 'hideCreateEditAttendance')

                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_ATTENDANCE_LOADING');
                    if (state._attendance_loading == 0) {
                        dispatch('fetchAttendance')
                    }
                });
        });
    },
    showAttendanceDetails({ commit, dispatch }) {
        commit('START_ATTENDANCE_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/attendances/' + state.currentAttendance.id)
                .then((res) => {
                    dispatch('setCurrentModelForAttachment', {
                        object: res.data,
                        filecategory: 'attendance'
                    })
                    resolve(res.data)
                })
                .catch((err) => {
                    dispatch('handleError', err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_ATTENDANCE_LOADING")
                });
        })
    },
    exportAttendance({ commit, dispatch }, type) {
        commit("START_ATTENDANCE_EXPORTING");
        axios.post('/api/attendances/export-download', state.attendance_options, { responseType: 'arraybuffer' })
            .then(res => {
                dispatch('downloadFromResponse', res)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_ATTENDANCE_EXPORTING")
            });
    },
}

const mutations = {
    setCurrentAttendance(state, value) {
        Vue.set(state, 'currentAttendance', value)
        state.currentAttendance = JSON.parse(JSON.stringify(state.currentAttendance))
    },
    START_ATTENDANCE_LOADING(state) {
        state._attendance_loading++
    },
    STOP_ATTENDANCE_LOADING(state) {
        state._attendance_loading--
    },
    setAttendance(state, value) {
        Vue.set(state, 'attendance', value)
        state.attendance = JSON.parse(JSON.stringify(state.attendance))
    },
    showCreateEditAttendance(state) {
        state.showCreateEditAttendance = true;
    },
    hideCreateEditAttendance(state) {
        state.showCreateEditAttendance = false;
    },
    setAttendanceOptions(state, value) {
        Vue.set(state, 'attendance_options', value)
    },
    START_ATTENDANCETYPES_LOADING(state) {
        state._attendancetypes_loading++
    },
    STOP_ATTENDANCETYPES_LOADING(state) {
        state._attendancetypes_loading--
    },
    setAttendanceTypes(state, value) {
        Vue.set(state, 'attendancetypes', value)
    },
    START_ATTENDANCECALENDAR_LOADING(state) {
        state._attendancecalendar_loading++
    },
    STOP_ATTENDANCECALENDAR_LOADING(state) {
        state._attendancecalendar_loading--
    },
    setAttendanceCalendar(state, data) {
        let cloned = _.cloneDeep(data)
        Object.keys(cloned).forEach(elementkey => {
            cloned[elementkey].start = moment(cloned[elementkey].start, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DD HH:mm:ss');
            cloned[elementkey].end = moment(cloned[elementkey].end, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DD HH:mm:ss');
        })
        Vue.set(state, 'attendancecalendar', cloned);
    },
    setAttendanceCalendarOptions(state, value) {
        Vue.set(state, 'attendancecalendar_options', value)
    },
    setAttendanceDragData(state, value) {
        Vue.set(state, 'attendanceDragData', value)
        state.attendanceDragData = JSON.parse(JSON.stringify(state.attendanceDragData))
    },
    START_ATTENDANCE_EXPORTING(state) {
        state.attendance_exporting++
    },
    STOP_ATTENDANCE_EXPORTING(state) {
        state.attendance_exporting--
    },
}

export default {
    state,
    getters,
    actions,
    mutations,
}