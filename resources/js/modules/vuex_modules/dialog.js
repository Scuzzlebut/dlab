import Vue from 'vue'

const state = {
    apierrors: {},
    // snackbar
    showSnackbar: false,
    snackbarMessage: '',
    snackbarColor: '',
    snackbarDuration: 3000,
    // dialog
    dialogShow: false,
    dialogPersistent: false,
    dialogType: '',
    dialogTitle: '',
    dialogMessage: '',
    dialogConfirmText: '',
    dialogOkCb: () => { },
    dialogCancelCb: () => { }
}

const getters = {
    getApiErrors: state => state.apierrors,
    // snackbar
    showSnackbar: state => state.showSnackbar,
    snackbarMessage: state => state.snackbarMessage,
    snackbarColor: state => state.snackbarColor,
    snackbarDuration: state =>  state.snackbarDuration,
    // dialog
    showDialog: state => state.dialogShow,
    dialogType: state => state.dialogType,
    dialogTitle: state => state.dialogTitle,
    dialogMessage: state => state.dialogMessage,
    dialogConfirmText: state => state.dialogConfirmText,
}
const actions = {
    handleError({ commit }, errors) {  
        if (errors.response.data){
            if (errors.response.data.errors){
                return Vue.set(state, 'apierrors', errors.response.data.errors);
            }
            if (errors.response.data.message) {
                return commit('showSnackbar', {
                    message: errors.response.data.message,
                    color: 'error',
                    duration: 5000
                })
            }
        }

        if (errors.message) {
            commit('showSnackbar', {
                message: errors.message,
                color: 'error',
                duration: 5000
            })
        }
    },
    handleResponseMessage({ commit, dispatch }, response) {
        let color = 'success'
        let message = null
        let title = 'Attenzione!'
        let type = null
        let duration = 3000

        if (response.code) {
            switch (response.code) {
                case 1:
                    color = 'warning'
                    type = 'snackbar'
                    duration = 5000
                    break;
                case 2:
                    color = 'error'
                    type = 'dialog'
                    break;
                default:
                    break;
            }
        }
        if (response.message) {
            message = response.message
            if (!type) {
                type = 'snackbar'
            }
        }
        if (response.errors) {
            Vue.set(state, 'apierrors', response.errors);
        }
        if (response.title) {
            title = response.title
        }

        switch (type) {
            case 'dialog':
                commit('showDialog', {
                    type: color == 'error' ? 'alert' : 'advice',
                    title: title,
                    message: message,
                    okCb: () => {
                    },
                })
                break;
            case 'snackbar':
                commit('showSnackbar', {
                    message: message,
                    duration: duration,
                    color: color
                })
                break;
            default:
                break;
        }
    },
}
const mutations = {
    // snackbar
    showSnackbar(state, data) {
        state.snackbarDuration = data.duration || 5000;
        state.snackbarMessage = data.message || 'No message.';
        state.snackbarColor = data.color || 'error';
        state.showSnackbar = true;
    },
    hideSnackbar(state) {
        state.showSnackbar = false;
    },
    // dialog
    showDialog(state, data) {
        state.dialogType = data.type || 'confirm';
        state.dialogTitle = data.title;
        state.dialogMessage = data.message;
        state.dialogConfirmText = data.dialogConfirmText;
        state.dialogOkCb = data.okCb || function () { };
        state.dialogCancelCb = data.cancelCb || function () { };
        if (data.dialogPersistent) {
            state.dialogPersistent = true;
        }
        else {
            state.dialogPersistent = false;
        }
        state.dialogShow = true;
    },
    hideDialog(state) {
        state.dialogShow = false;
    },
    dialogOk(state) {
        if (!state.dialogPersistent) {
            state.dialogShow = false;
        }
        state.dialogOkCb();
    },
    dialogConfirm(state, text) {
        state.dialogConfirmText = text;
        if (!state.dialogPersistent) {
            state.dialogShow = false;
        }
        state.dialogOkCb();
    },
    dialogCancel(state) {
        state.dialogCancelCb();
        state.dialogShow = false;
    }
}
export default {
    state,
    getters,
    actions,
    mutations,
}