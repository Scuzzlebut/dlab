import Vue from 'vue'
import axios from 'axios';

const state = {
    _auth_loading: 0,
    access_token: null,
    user: {},
    _user_loading: 0,
}

const getters = {
    auth_loading: state => state._auth_loading!=0,
    isAuthenticated: state => state.access_token!=null,
    getUser: state => state.user,
    getMe: state => state.user.staff ?? null,
    user_loading: state => state._user_loading!=0
}


const actions = {
    login({ commit, dispatch }, payload) {
        commit("START_AUTH_LOADING");
        return new Promise((resolve, reject) => {
            axios.post('/api/login', payload)
                .then(res => {
                    if (res.data.token) {
                        let access_token = 'Bearer ' + res.data.token
                        localStorage.setItem('access_token', access_token)
                        commit('authSuccess', access_token);
                        commit('setUser', res.data.user);
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_AUTH_LOADING")
                });
        });
    },
    logout({ commit, dispatch }) {
        return new Promise((resolve, reject) => {
            axios.post('/api/logout').then(res => {
                commit('authLogout')
                resolve(res.data)
            }).catch(err => {
                dispatch('handleError', err);
                reject(err)
            })
        });
    },
    changePassword({ commit, dispatch },params) {
        commit("START_AUTH_LOADING");
        return new Promise((resolve, reject) => {
            axios.put('/api/changepassword/' + params.id, params)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_AUTH_LOADING")
                });
        });
    },
    fetchUser({ commit,dispatch }) {
        commit("START_USER_LOADING");
        return new Promise((resolve, reject) => {
            axios.get('/api/user').then(res => {
                if (res.data) {
                    commit('setUser', res.data.user);
                    resolve(res.data)
                }
            }).catch(err => {
                dispatch('handleError', err);
                reject(err)
            })
            .then(function () {
                commit("STOP_USER_LOADING")
            });
        });
    },
    askResetPassword({ commit, dispatch }, payload) {
        commit("START_AUTHLOADING");
        return new Promise((resolve, reject) => {
            axios.post('/api/password/askreset', payload)
                .then(res => {
                    if (res.data.errors) {
                        commit('handleError', res.data.errors)
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError',err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_AUTHLOADING")
                });
        });
    },
    resetPassword({ commit, dispatch }, payload) {
        commit("START_AUTHLOADING");
        return new Promise((resolve, reject) => {
            axios.post('/api/password/reset', payload)
                .then(res => {
                    dispatch('handleResponseMessage',res.data)
                    if (res.data.errors) {
                        commit('handleError', res.data.errors)
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError',err)
                    reject(err)
                })
                .then(function () {
                    commit("STOP_AUTHLOADING")
                });
        });
    },
}

const mutations = {
    getAccessFromStorage(state) {
        Vue.set(state, 'access_token', localStorage.getItem('access_token'))
        window.axios.defaults.headers.common.Authorization = localStorage.getItem('access_token')
    },
    START_AUTH_LOADING(state) {
        state._auth_loading++
    },
    STOP_AUTH_LOADING(state) {
        state._auth_loading--
    },
    authSuccess(state, access_token) {
        state.access_token = access_token
        window.axios.defaults.headers.common.Authorization = access_token
    },
    authLogout(state) {
        state.access_token = null
        Vue.set(state, 'user', {})
        localStorage.removeItem('access_token')
    },
    setUser(state, user) {
        Vue.set(state, 'user', user)
    },
    START_USER_LOADING(state) {
        state._user_loading++
    },
    STOP_USER_LOADING(state) {
        state._user_loading--
    },
}

export default {
    state,
    getters,
    actions,
    mutations,
}