

import Vue from 'vue'

const state = {
    primaryDrawer: JSON.parse(localStorage.getItem("primary_drawer")),
    _changes_not_saved: false,
    headerspreference: JSON.parse(localStorage.getItem("headerspreference")) || {},
    currentroute: null,
    isOffline: false,
    isTouch: false,
}

const getters = {
    getPrimaryDrawer: state => state.primaryDrawer,
    changes_not_saved: state => state._changes_not_saved,
    getHeadersPreference: state => state.headerspreference,
    currentroute: state => state.currentroute,
    isOffline: state => state.isOffline,
    isTouch: state => state.isTouch,
}
const actions = {

}
const mutations = {
    setPrimaryDrawer(state, value) {
        localStorage.setItem('primary_drawer', JSON.stringify(value))
        state.primaryDrawer = value
    },
    setChanges_not_saved(state, val) {
        Vue.set(state, '_changes_not_saved', val)
    },
    setHeadersPreference(state, value) {
        localStorage.setItem('headerspreference', JSON.stringify(value))
        state.headerspreference = value
    },
    setCurrentRoute(state, value) {
        Vue.set(state, 'currentroute', value)
    },
    setOfflineState(state, value) {
        Vue.set(state, 'isOffline', value)
    },
    setTouchState(state, value) {
        Vue.set(state, 'isTouch', value)
    },
}
export default {
    state,
    getters,
    actions,
    mutations,
}