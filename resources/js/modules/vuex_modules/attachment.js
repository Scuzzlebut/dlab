import axios from 'axios'
import _ from 'lodash'
import Vue from 'vue'
import i18n from '../i18n';

const state = {
    _attachment_loading: 0,
    _attachmentpreview_loading: 0,
    attachment: {},
    attachment_options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
        search: '',
        model_type: null,
        model_id: null
    },
    showAttachment: false,
    showCreateEditAttachment: false,
    showAddAttachment: false,
    source: null,
    progress: {
        total: 100,
        loaded: 0,
    },
    temporarySource: [],
    temporaryProgress: [],
    attachmentCategory: null,
    currentAttachmentModel: {},
    _uploadattachment_loading: 0,
    _attachment_downloading: 0,
    temporaryAttachment: [],
    currentAttachment: {},
    _omit_changes_attachment: ['thumbnail_link','thumbnail_path']
}

const getters = {
    attachment_loading: state => state._attachment_loading !== 0,
    attachmentpreview_loading: state => state._attachmentpreview_loading!==0,
    getAttachment: state => state.attachment,
    getAttachmentOptions: state => state.attachment_options,
    showAttachment: state => state.showAttachment,
    showAddAttachment: state => state.showAddAttachment,
    showCreateEditAttachment: state => state.showCreateEditAttachment,
    getProgress: state => state.progress,
    getSource: state => state.source,
    getTemporaryProgress: state => state.temporaryProgress,
    getTemporarySource: state => state.temporarySource,
    getAttachmentCategory: state => state.attachmentCategory,
    getCurrentAttachmentModel: state => state.currentAttachmentModel,
    uploadattachment_loading: state => state._uploadattachment_loading != 0,
    attachment_downloading: state => state._attachment_downloading != 0,
    getTemporaryAttachment: state => state.temporaryAttachment,
    getCurrentAttachment: state => state.currentAttachment,
    getOmitChangesAttachment: state => state._omit_changes_attachment
}


const actions = {
    handleAttachmentDeleted({ commit, dispatch, rootState }, attachment) {
        let temporaryAttachmentModel = {}
        let temporaryPaginatedList = {}
        let finalCommitAttachment = null
        let finalCommitPaginated = null
        switch (state.attachmentCategory) {
            case 'attendance':
                temporaryAttachmentModel = rootState.attendance.currentAttendance
                temporaryPaginatedList = rootState.attendance.attendance
                finalCommitAttachment = 'setCurrentAttendance'
                finalCommitPaginated = 'setAttendance'
                break;
            case 'communication':
                temporaryAttachmentModel = rootState.communication.currentCommunication
                temporaryPaginatedList = rootState.communication.communication
                finalCommitAttachment = 'setCurrentCommunication'
                finalCommitPaginated = 'setCommunication'
                break;
            case 'paysheet':
                temporaryAttachmentModel = rootState.paysheet.currentPaysheet
                temporaryPaginatedList = rootState.paysheet.paysheet
                finalCommitAttachment = 'setCurrentPaysheet'
                finalCommitPaginated = 'setPaysheet'
                break;
            case 'staff':
                temporaryAttachmentModel = rootState.staff.currentStaff
                temporaryPaginatedList = rootState.staff.staff
                finalCommitAttachment = 'setCurrentStaff'
                finalCommitPaginated = 'setStaff'
                break;
            default:
                break;
        }
        if (temporaryAttachmentModel.attachments) {
            let $finded = temporaryAttachmentModel.attachments.findIndex(x => x.id === parseInt(attachment.id));
            if ($finded != -1) {
                temporaryAttachmentModel.attachments.splice($finded, 1)
            }
            commit('setCurrentAttachmentModel', temporaryAttachmentModel)
            if (finalCommitAttachment) {
                commit(finalCommitAttachment, temporaryAttachmentModel)
            }
            if (temporaryPaginatedList.data) {
                let $finded = temporaryPaginatedList.data.findIndex(x => x.id === parseInt(temporaryAttachmentModel.id));
                if ($finded != -1) {
                    temporaryPaginatedList.data[$finded] = temporaryAttachmentModel
                }
                if (finalCommitPaginated) {
                    commit(finalCommitPaginated, temporaryPaginatedList)
                }
            }
        }
        else {
            if (temporaryPaginatedList.data) {
                temporaryPaginatedList.data.forEach(paginatedModel => {
                    if (paginatedModel.attachments){
                        let $finded = paginatedModel.attachments.findIndex(x => x.id === parseInt(attachment.id));
                        if ($finded != -1) {
                            paginatedModel.attachments.splice($finded, 1)
                        }
                    }
                });
            }
        }
        if (rootState.user.tenant_config.settings.AttachmentsViewStyle == 'table' || state.attachmentCategory == 'tenant') {
            dispatch('fetchAttachment')
        }
    },
    handleAttachmentAddOrUpdate({ commit, dispatch, rootState }, attachment) {
        let temporaryAttachmentModel = {}
        let temporaryPaginatedList = {}
        let finalCommitAttachment = null
        let finalCommitPaginated = null
        switch (state.attachmentCategory) {
            case 'attendance':
                temporaryAttachmentModel = rootState.attendance.currentAttendance
                temporaryPaginatedList = rootState.attendance.attendance
                finalCommitAttachment = 'setCurrentAttendance'
                finalCommitPaginated = 'setAttendance'
                break;
            case 'communication':
                temporaryAttachmentModel = rootState.communication.currentCommunication
                temporaryPaginatedList = rootState.communication.communication
                finalCommitAttachment = 'setCurrentCommunication'
                finalCommitPaginated = 'setCommunication'
                break;
            case 'paysheet':
                temporaryAttachmentModel = rootState.paysheet.currentPaysheet
                temporaryPaginatedList = rootState.paysheet.paysheet
                finalCommitAttachment = 'setCurrentPaysheet'
                finalCommitPaginated = 'setPaysheet'
                break;
            case 'staff':
                temporaryAttachmentModel = rootState.staff.currentStaff
                temporaryPaginatedList = rootState.staff.staff
                finalCommitAttachment = 'setCurrentStaff'
                finalCommitPaginated = 'setStaff'
                break;
            default:
                break;
        }
        if (!_.isEmpty(temporaryAttachmentModel)){
            if (!temporaryAttachmentModel.attachments) {
                temporaryAttachmentModel.attachments = []
            }
            if (temporaryAttachmentModel.attachments) {
                let $finded = temporaryAttachmentModel.attachments.findIndex(x => x.id === parseInt(attachment.id));
                if ($finded != -1) {
                    temporaryAttachmentModel.attachments[$finded] = attachment
                }
                else {
                    temporaryAttachmentModel.attachments.push(attachment)
                }
                commit('setCurrentAttachmentModel', temporaryAttachmentModel)
                if (finalCommitAttachment) {
                    commit(finalCommitAttachment, temporaryAttachmentModel)
                }
                if (temporaryPaginatedList.data) {
                    let $finded = temporaryPaginatedList.data.findIndex(x => x.id === parseInt(temporaryAttachmentModel.id));
                    if ($finded != -1) {
                        temporaryPaginatedList.data[$finded] = temporaryAttachmentModel
                    }
                }
            }
        }
    },
    deleteAttachment({ commit, dispatch, rootState }, id) {
        commit('START_ATTACHMENT_LOADING');
        return new Promise((resolve, reject) => {
            axios.delete('/api/attachment/' + id)
                .then(res => {
                    dispatch('handleResponseMessage', res.data);
                    if (res.data.object) {
                        dispatch('handleAttachmentDeleted', res.data.object);
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_ATTACHMENT_LOADING")
                });
        });
    },
    createAttachment({ commit, dispatch }) {
        commit('START_ATTACHMENT_LOADING');
        return new Promise((resolve, reject) => {
            axios.put('/api/attachment/' + state.currentAttachment.id, state.currentAttachment)
                .then(res => {
                    dispatch('handleResponseMessage', res.data);
                    if (res.data.object) {
                        dispatch('handleAttachmentAddOrUpdate', res.data.object);
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    dispatch('handleError', err);
                    reject(err)
                })
                .then(function () {
                    commit("STOP_ATTACHMENT_LOADING")
                });
        });
    },
    uploadTemporaryAttachment({ commit, dispatch }, formData) {
        commit('START_UPLOADATTACHMENT_LOADING');
        const source = axios.CancelToken.source();
        commit('setTemporarySource', { index: formData.get('index'), source: source })
        commit('setTemporaryProgress', { index: formData.get('index'), progress: { total: 100, loaded: 0, errors: null, complete: false } })
        let computedroute = null
        switch (state.attachmentCategory) {
            case 'attendance':
                computedroute = 'attendances/'
                break;
            case 'communication':
                computedroute = 'communications/'
                break;
            case 'paysheet':
                computedroute = 'paysheet/'
                break;
            case 'staff':
                computedroute = 'staff/'
                break;
            default:
                break;
        }
        return new Promise((resolve, reject) => {
            axios.post('/api/' + computedroute + state.currentAttachmentModel.id + '/attachment', formData, {
                cancelToken: source.token,
                onUploadProgress: (e) => {
                    commit('setTemporaryProgress', { index: formData.get('index'), progress: { total: e.total, loaded: e.loaded, errors: null, complete: false } });
                }
            })
                .then(res => {
                    commit('setTemporaryProgress', { index: formData.get('index'), progress: { total: 100, loaded: 100, errors: null, complete: true } });
                    if (res.data.object) {
                        dispatch('handleAttachmentAddOrUpdate', res.data.object);
                    }
                    resolve(res.data)
                })
                .catch(err => {
                    let errormessage = "Errore durante il caricamento"
                    if (err.response.data) {
                        if (err.response.data.errors) {
                            let errarray = err.response.data.errors
                            if (errarray.attachment) {
                                errormessage = errarray.attachment[0].replace('attachment', i18n.t('attachment.attachment'))
                            }
                        }
                        if (err.response.data.errormessage) {
                            errormessage = err.response.data.errormessage
                        }
                    }
                    else {
                        errormessage = err.data.message
                    }
                    commit('setTemporaryProgress', { index: formData.get('index'), progress: { total: 100, loaded: 100, errors: errormessage, complete: true } });
                    reject(err)
                })
                .then(function () {
                    commit("STOP_UPLOADATTACHMENT_LOADING")
                });
        });
    },
    uploadAttachment({ commit, dispatch }, formData) {
        commit('START_UPLOADATTACHMENT_LOADING');
        const source = axios.CancelToken.source();
        commit('setSource', source)
        commit('setProgress', { total: 100, loaded: 0 })
        let computedroute = null
        switch (state.attachmentCategory) {
            case 'attendance':
                computedroute = 'attendances/' + state.currentAttachmentModel.id + '/attachment'
                break;
            case 'communication':
                computedroute = 'communications/' + state.currentAttachmentModel.id + '/attachment'
                break;
            case 'paysheet':
                computedroute = 'paysheet/' + state.currentAttachmentModel.id + '/attachment'
                break;
            case 'staff':
                computedroute = 'staff/' + state.currentAttachmentModel.id + '/attachment'
                break;
            default:
                break;
        }
        return new Promise((resolve, reject) => {
            axios.post('/api/' + computedroute , formData, {
                cancelToken: source.token,
                onUploadProgress: (e) => {
                    commit('setProgress', { total: e.total, loaded: e.loaded });
                }
            })
            .then(res => {
                dispatch('handleResponseMessage', res.data);
                if (res.data.object) {
                    dispatch('handleAttachmentAddOrUpdate', res.data.object);
                }
                resolve(res.data)
            })
            .catch(err => {
                dispatch('handleError', err);
                reject(err)
            })
            .then(function () {
                commit("STOP_UPLOADATTACHMENT_LOADING")
            });
        });
    },
    setCurrentModelForAttachment({ commit,dispatch }, params) {
        if (!params.filecategory || !params.object) {
            commit('showSnackbar', {
                message: 'Errore configurazione gestione allegati'
            })
        }
        else {
            let can_go_on = true
            switch (params.filecategory) {
                case 'attendance':
                    commit('setCurrentAttendance', _.cloneDeep(params.object))
                    break;
                case 'communication':
                    commit('setCurrentCommunication', _.cloneDeep(params.object))
                    break;
                case 'paysheet':
                    commit('setCurrentPaysheet', _.cloneDeep(params.object))
                    break;
                case 'staff':
                    commit('setCurrentStaff', _.cloneDeep(params.object))
                    break;
                default:
                    commit('showSnackbar', {
                        message: 'Categoria allegati non definita'
                    })
                    can_go_on = false
                    break;
            }
            if (can_go_on) {
                commit('setCurrentAttachmentModel', _.cloneDeep(params.object))
                commit('setAttachmentCategory', params.filecategory)
            }
            else {
                commit('setCurrentAttachmentModel', {})
                commit('setAttachmentCategory', null)
            }
            dispatch('hideAction', 'hideAttachment')
            dispatch('hideAction', 'hideAddAttachment')
            if (params.show) {
                commit('showAttachment')
            }
        }
    },
    fetchAttachment({ commit }) {
        commit('START_ATTACHMENT_LOADING');
        axios.get('/api/attachment', { params: state.attachment_options })
            .then(res => {
                commit('setAttachment', res.data);
            })
            .catch(err => {
                commit('handleError', err);
            })
            .then(() => {
                commit('STOP_ATTACHMENT_LOADING');
            })
    },
    loadAttachmentPreview({commit},id){
        commit('START_ATTACHMENTPREVIEW_LOADING');
        axios.get('/api/attachment/' + id)
            .then(res => {
                commit('setAttachmentPreview', res.data);
            })
            .catch(err => {
                commit('handleError', err);
            })
            .then(() => {
                commit('STOP_ATTACHMENTPREVIEW_LOADING');
            })
    }
}

const mutations = {
    START_ATTACHMENT_LOADING(state) {
        state._attachment_loading++
    },
    STOP_ATTACHMENT_LOADING(state) {
        state._attachment_loading--
    },
    START_ATTACHMENTPREVIEW_LOADING(state) {
        state._attachmentpreview_loading++
    },
    STOP_ATTACHMENTPREVIEW_LOADING(state) {
        state._attachmentpreview_loading--
    },
    setAttachment(state,value){
        Vue.set(state,'attachment',value)
    },
    setAttachmentPreview(state,obj){
        state.currentAttachment.thumbnail_link = obj.thumbnail_link
        state.currentAttachment=JSON.parse(JSON.stringify(state.currentAttachment))
        if (state.currentAttachmentModel.attachments){
            let $finded = state.currentAttachmentModel.attachments.findIndex(attachment => attachment.id=== parseInt(obj.id))
            if ($finded!=-1){
                state.currentAttachmentModel.attachments[$finded]=obj
                state.currentAttachmentModel = JSON.parse(JSON.stringify(state.currentAttachmentModel))
            }
        }
    },
    setAttachmentOptions(state, value) {
        Vue.set(state, 'attachment_options', value)
    },
    showAttachment(state) {
        state.showAttachment = true
    },
    hideAttachment(state) {
        state.showAttachment = false
    },
    showCreateEditAttachment(state) {
        state.showCreateEditAttachment = true
    },
    hideCreateEditAttachment(state) {
        state.showCreateEditAttachment = false
    },
    showAddAttachment(state) {
        state.showAddAttachment = true
    },
    hideAddAttachment(state) {
        state.showAddAttachment = false
    },
    setSource(state, value) {
        Vue.set(state, 'source', value)
    },
    setProgress(state, value) {
        Vue.set(state, 'progress', value)
    },
    setTemporarySource(state, value) {
        state.temporarySource[value.index] = value.source
        state.temporarySource = JSON.parse(JSON.stringify(state.temporarySource))
    },
    setTemporaryProgress(state, value) {
        state.temporaryProgress[value.index] = value.progress
        state.temporaryProgress = JSON.parse(JSON.stringify(state.temporaryProgress))
    },
    resetTemporaryAttachment() {
        Vue.set(state, 'temporaryProgress', []),
        Vue.set(state, 'temporarySource', [])
    },
    setAttachmentCategory(state, value) {
        Vue.set(state, 'attachmentCategory', value)
    },
    setCurrentAttachmentModel(state, value) {
        Vue.set(state, 'currentAttachmentModel', value)
    },
    START_UPLOADATTACHMENT_LOADING(state) {
        state._uploadattachment_loading++
    },
    STOP_UPLOADATTACHMENT_LOADING(state) {
        state._uploadattachment_loading--
    },
    setTemporaryAttachment(state, value) {
        Vue.set(state, 'temporaryAttachment', value)
    },
    setCurrentAttachment(state, value) {
        Vue.set(state, 'currentAttachment', value)
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}