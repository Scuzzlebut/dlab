import Vue from 'vue'

const state = {
    activity_types: null,
    projects: null,
    activity_loading: 0,
    activity_types_loading: 0,
    projects_loading: 0,
    activity_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['id'],
        sortDesc: [true],
        search: '',
        year: null,
        month: null,
        type: null,
        staff_ids: []
    },
    activities: {},
    currentActivity: {},
    showCreateEditActivity: false,
}

const getters = {
    getActivities: state => state.activities,
    getActivityTypes: state => state.activity_types,
    getProjects: state => state.projects,
    getActivityOptions: state => state.activity_options,
    activity_loading: state => state.activity_loading !== 0,
    activity_types_loading: state => state.activity_types_loading !== 0,
    projects_loading: state => state.projects_loading !== 0,
    getCurrentActivity: state => state.currentActivity,
    showCreateEditActivity: state => state.showCreateEditActivity,
}

const actions = {
    fetchActivities({ commit, dispatch }) {
        commit('START_ACTIVITY_LOADING');
        axios.get('/api/activities', { params: state.activity_options })
            .then(res => {
                commit('setActivities', res.data);
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(() => {
                commit('STOP_ACTIVITY_LOADING');
            })
    },
    fetchProjects({ commit, dispatch }) {
        commit('START_PROJECTS_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/utility/get-projects')
                .then(res => {
                    commit('setProjects', res.data);
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(() => {
                    commit('STOP_PROJECTS_LOADING');
                })
            })
    },
    fetchActivityTypes({ commit, dispatch }) {
        commit('START_ACTIVITY_TYPES_LOADING');
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
                    commit("STOP_ACTIVITY_TYPES_LOADING")
                });
        })
    },
    deleteActivity({ commit, dispatch }, id) {
        commit('START_ACTIVITY_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/activities/' + id)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('removeActivity', id)
                        dispatch('hideAction', 'hideCreateEditCommunication')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_ACTIVITY_LOADING');
                });
        });
    },
}

const mutations = {
    setActivityOptions(state, value) {
        Vue.set(state, 'activity_options', value)
    },
    setActivityTypes(state, value) {
        Vue.set(state, 'activity_types', value)
        // state.activity_types = JSON.parse(JSON.stringify(state.activity_types))
    },
    setProjects(state, value) {
        Vue.set(state, 'projects', value)
        // state.projects = JSON.parse(JSON.stringify(state.projects))
    },
    START_ACTIVITY_LOADING(state) {
        state.activity_loading++
    },
    STOP_ACTIVITY_LOADING(state) {
        state.activity_loading--
    },
    START_ACTIVITY_TYPES_LOADING(state) {
        state.activity_types_loading++
    },
    STOP_ACTIVITY_TYPES_LOADING(state) {
        state.activity_types_loading--
    },
    START_PROJECTS_LOADING(state) {
        state.projects_loading++
    },
    STOP_PROJECTS_LOADING(state) {
        state.projects_loading--
    },
    setCurrentActivity(state, value) {
        Vue.set(state, 'currentActivity', value)
        state.currentActivity = JSON.parse(JSON.stringify(state.currentActivity))
    },
    setActivities(state, value) {
        Vue.set(state, 'activities', value)
        state.activities = JSON.parse(JSON.stringify(state.activities))
    },
    showActivityDetails({ commit, dispatch }) {
        commit('START_ACTIVITY_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/activities/' + state.currentActivity.id)
                .then((res) => {
                    resolve(res.data)
                })
                .catch((err) => {
                    dispatch('handleError', err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_ACTIVITY_LOADING")
                });
        })
    },
    removeActivity(state, id) {
        if (state.activities.data) {
            let found = state.activities.data.findIndex(obj => obj.id === id)
            if (found !== -1) {
                state.activities.data.splice(found, 1)
                state.activities.data.total -= 1
                state.activities = JSON.parse(JSON.stringify(state.activities))
            }
        }
        state.activity_options.page = 1
        state.activity_options = JSON.parse(JSON.stringify(state.activity_options))
    },
    showCreateEditActivity(state) {
        state.showCreateEditActivity = true;
    },
    hideCreateEditActivity(state) {
        state.showCreateEditActivity = false;
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}
