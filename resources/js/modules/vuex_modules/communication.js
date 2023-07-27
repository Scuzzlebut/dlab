import Vue from 'vue'

const state = {
    communicationtoread_options: {
        page: 1,
        itemsPerPage: 1,
        sortBy: ['created_at'],
        sortDesc: [true],
        toread: true
    },
    communicationtoread: {},
    _communicationtoread_loading: 0,
    communication_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
        groupBy: [],
        groupDesc: [],
        mustSort: false,
        multiSort: false
    },
    communication: {
        data: [],
        total: 1
    },
    communicationarchived: {
        data: [],
        total: 1
    },
    communicationtotal: {
        data: [],
        total: 1
    },
    _communication_loading: 0,
    currentCommunication: {},
    showCreateEditCommunication: false,
    _omit_changes_communication: ['attachments'],
}

const getters = {
    getCommunicationToReadOptions: state => state.communicationtoread_options,
    getCommunicationToRead: state => state.communicationtoread,
    communicationtoread_loading: state => state._communicationtoread_loading !== 0,
    getCommunicationOptions: state => state.communication_options,
    getCommunication: state => state.communication,
    getCommunicationArchived: state => state.communicationarchived,
    getCommunicationTotal: state => state.communicationtotal,
    communication_loading: state => state._communication_loading !== 0,
    getCurrentCommunication: state => state.currentCommunication,
    showCreateEditCommunication: state => state.showCreateEditCommunication,
    getOmitChangesCommunication: state => state._omit_changes_communication,
}


const actions = {
    fetchCommunicationToRead({ commit, dispatch }) {
        commit('START_COMMUNICATIONTOREAD_LOADING');
        axios.get('/api/communications',{params: state.communicationtoread_options})
            .then(res => {
                commit('setCommunicationToRead', res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_COMMUNICATIONTOREAD_LOADING")
            });
    },
    closeCommunication({ commit, dispatch }, id) {
        commit('START_COMMUNICATIONTOREAD_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/communications/' + id + '/set-read-status',{read: true})
                .then(res => {
                    dispatch('fetchCommunicationToRead')
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_COMMUNICATIONTOREAD_LOADING")
                });
        });
    },
    createCommunication({ commit, dispatch, rootState }) {
        commit("START_COMMUNICATION_LOADING");
        return new Promise((resolve, reject) => {
            if (!state.currentCommunication.id) {
                axios.post('/api/communications', state.currentCommunication)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('setCurrentModelForAttachment', {
                                object: res.data.object,
                                filecategory: 'communication',
                            })
                            if (!_.isEmpty(rootState.attachment.temporaryAttachment)) {
                                Object.keys(rootState.attachment.temporaryAttachment).forEach(newattachmentindex => {
                                    dispatch('uploadTemporaryAttachment', rootState.attachment.temporaryAttachment[newattachmentindex]).then(attachmentres => {
                                        if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                            dispatch('fetchCommunicationTotal')
                                        }
                                    })
                                        .catch(err => {
                                            if (newattachmentindex == rootState.attachment.temporaryAttachment.length - 1) {
                                                dispatch('fetchCommunicationTotal')
                                            }
                                        })
                                })
                            }
                            else {
                                dispatch('fetchCommunicationTotal')
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
                        commit("STOP_COMMUNICATION_LOADING")
                    });
            }
            else {
                axios.put('/api/communications/' + state.currentCommunication.id, state.currentCommunication)
                    .then(res => {
                        if (res.data.object != null) {
                            dispatch('fetchCommunicationTotal');
                        }
                        dispatch('handleResponseMessage', res.data)
                        resolve(res.data)
                    })
                    .catch(err => {
                        dispatch('handleError', err);
                        reject(err)
                    })
                    .then(function () {
                        commit("STOP_COMMUNICATION_LOADING")
                    });
            }
        })
    },
    fetchCommunication({ commit, dispatch }) {
        commit('START_COMMUNICATION_LOADING');
        let options = state.communication_options
        options.toread = true
        axios.get('/api/communications', { params: options })
            .then(res => {
                commit('setCommunication', res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_COMMUNICATION_LOADING")
            });
    },
    fetchCommunicationArchived({ commit, dispatch }) {
        commit('START_COMMUNICATION_LOADING');
        let options = state.communication_options
        options.toread = false
        axios.get('/api/communications', { params: options })
            .then(res => {
                commit('setCommunicationArchived', res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_COMMUNICATION_LOADING")
            });
    },
    fetchCommunicationTotal({ commit, dispatch }) {
        commit('START_COMMUNICATION_LOADING');
        axios.get('/api/communications/all', { params: state.communication_options })
            .then(res => {
                commit('setCommunicationTotal', res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
            })
            .then(function () {
                commit("STOP_COMMUNICATION_LOADING")
            });
    },
    toggleArchiveCommunication({ commit, dispatch }, params) {
        commit('START_COMMUNICATION_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/communications/' + params.id + '/set-read-status',params)
                .then(res => {
                    commit('toggleCommunicationOnArchive', params)
                    dispatch('hideAction', 'hideCreateEditCommunication')   
                    dispatch('handleResponseMessage', res.data)
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_COMMUNICATION_LOADING")
                });
        });
    },
    deleteCommunication({ commit, dispatch }, id) {
        commit('START_COMMUNICATION_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/communications/' + id)
                .then(res => {
                    dispatch('handleResponseMessage', res.data)
                    if (res.data.object) {
                        commit('removeCommunication', id)
                        dispatch('hideAction', 'hideCreateEditCommunication')
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit('STOP_COMMUNICATION_LOADING');
                });
        });
    },
    showCommunicationDetails({commit,dispatch}){
        commit('START_COMMUNICATION_LOADING');
        return new Promise((resolve, reject) => {
            axios.get('/api/communications/' + state.currentCommunication.id)
            .then((res)=>{
                dispatch('setCurrentModelForAttachment', {
                    object: res.data,
                    filecategory: 'communication'
                })
                resolve(res.data)
            })
            .catch((err)=>{
                dispatch('handleError',err)
                reject(err)
            })
            .then(function () {
                commit("STOP_COMMUNICATION_LOADING")
            });
        })
    }
}

const mutations = {
    setCommunicationToReadOptions(state, value) {
        Vue.set(state, 'communicationtoread_options', value)
    },
    setCommunicationToRead(state, data) {
        if (!_.isEmpty(data.data)){
            Vue.set(state, 'communicationtoread', data.data[0]);
        }
        else {
            Vue.set(state, 'communicationtoread', []);
        }
    },
    START_COMMUNICATIONTOREAD_LOADING(state) {
        state._communicationtoread_loading++
    },
    STOP_COMMUNICATIONTOREAD_LOADING(state) {
        state._communicationtoread_loading--
    },
    setCommunicationOptions(state, value) {
        Vue.set(state, 'communication_options', value)
    },
    setCommunication(state, data) {
        let newnotificationresponse = _.cloneDeep(data)
        let olddata = state.communication.data || []
        newnotificationresponse.data.forEach(function (newnotification) {
            if (olddata.findIndex(x => x.id == newnotification.id) == -1) {
                olddata.push(newnotification)
            }
        })
        newnotificationresponse.data = olddata
        newnotificationresponse.total = data.total
        Vue.set(state, 'communication', newnotificationresponse)
    },
    resetCommunication(state) {
        Vue.set(state, 'communication', { data: [], total: 1 })
        state.communication_options.page = 1
        state.communication_opotions = JSON.parse(JSON.stringify(state.communication_options))
    },
    setCommunicationArchived(state, data) {
        let newnotificationresponse = _.cloneDeep(data)
        let olddata = state.communicationarchived.data || []
        newnotificationresponse.data.forEach(function (newnotification) {
            if (olddata.findIndex(x => x.id == newnotification.id) == -1) {
                olddata.push(newnotification)
            }
        })
        newnotificationresponse.data = olddata
        newnotificationresponse.total = data.total
        Vue.set(state, 'communicationarchived', newnotificationresponse)
    },
    resetCommunicationArchived(state) {
        Vue.set(state, 'communicationarchived', { data: [], total: 1 })
        state.communication_options.page = 1
        state.communication_opotions = JSON.parse(JSON.stringify(state.communication_options))
    },
    setCommunicationTotal(state, data) {
        let newnotificationresponse = _.cloneDeep(data)
        let olddata = state.communicationtotal.data || []
        newnotificationresponse.data.forEach(function (newnotification) {
            if (olddata.findIndex(x => x.id == newnotification.id) == -1) {
                olddata.push(newnotification)
            }
        })
        newnotificationresponse.data = olddata
        newnotificationresponse.total = data.total
        Vue.set(state, 'communicationtotal', newnotificationresponse)
    },
    resetCommunicationTotal(state) {
        Vue.set(state, 'communicationtotal', { data: [], total: 1 })
        state.communication_options.page = 1
        state.communication_opotions = JSON.parse(JSON.stringify(state.communication_options))
    },
    START_COMMUNICATION_LOADING(state) {
        state._communication_loading++
    },
    STOP_COMMUNICATION_LOADING(state) {
        state._communication_loading--
        if (state._communication_loading < 0) {
            state._communication_loading = 0
        }
    },
    toggleCommunicationOnArchive(state, object) {
        if (state.communication.data) {
            let finded = state.communication.data.findIndex(obj => obj.id == object.id)
            if (finded != -1) {
                if (!state.communicationarchived.data) {
                    state.communicationarchived = { data: [], total: 0 }
                }
                state.communicationarchived.data.push(object)
                state.communicationarchived.total += 1
                state.communication.data.splice(finded, 1)
                state.communication.data.total -= 1
                state.communication = JSON.parse(JSON.stringify(state.communication))
                state.communicationarchived = JSON.parse(JSON.stringify(state.communicationarchived))
            }
            else {
                finded = state.communicationarchived.data.findIndex(obj => obj.id == object.id)
                if (finded != -1) {
                    if (!state.communication.data) {
                        state.communication = { data: [], total: 0 }
                    }
                    state.communication.data.push(object)
                    state.communication.total += 1
                    state.communicationarchived.data.splice(finded, 1)
                    state.communicationarchived.data.total -= 1
                    state.communication = JSON.parse(JSON.stringify(state.communication))
                    state.communicationarchived = JSON.parse(JSON.stringify(state.communicationarchived))
                }
            }
        }
    },
    setCurrentCommunication(state, value) {
        Vue.set(state, 'currentCommunication', value)
        state.currentCommunication = JSON.parse(JSON.stringify(state.currentCommunication))
    },
    showCreateEditCommunication(state) {
        state.showCreateEditCommunication = true;
    },
    hideCreateEditCommunication(state) {
        state.showCreateEditCommunication = false;
    },
    removeCommunication(state, id) {
        if (state.communication.data) {
            let finded = state.communication.data.findIndex(obj => obj.id == id)
            if (finded != -1) {
                state.communication.data.splice(finded, 1)
                state.communication.data.total -= 1
                state.communication = JSON.parse(JSON.stringify(state.communication))
            }
        }
        if (state.communicationarchived.data) {
            let finded = state.communicationarchived.data.findIndex(obj => obj.id == id)
            if (finded != -1) {
                state.communicationarchived.data.splice(finded, 1)
                state.communicationarchived.data.total -= 1
                state.communicationarchived = JSON.parse(JSON.stringify(state.communicationarchived))
            }
        }
        if (state.communicationtotal.data) {
            let finded = state.communicationtotal.data.findIndex(obj => obj.id == id)
            if (finded != -1) {
                state.communicationtotal.data.splice(finded, 1)
                state.communicationtotal.data.total -= 1
                state.communicationtotal = JSON.parse(JSON.stringify(state.communicationtotal))
            }
        }
        state.communication_options.page = 1
        state.communication_opotions = JSON.parse(JSON.stringify(state.communication_options))
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}