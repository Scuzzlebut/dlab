import Vue from 'vue'

const state = {
    _popup_opened: [],
}

const getters = {
    hasPopupOpen: state => state._popup_opened.length!==0,
    getPopupOpened: state => state._popup_opened
}

const actions = {
    showAction({ commit },action) {
        commit('addActionToOpenedPopup', action)
        commit(action)
    },
    hideAction({ commit }, action) {
        commit(action)
        commit('removeActionFromOpenedPopup', action)
    }
}
const mutations = {
    addActionToOpenedPopup(state,value){
        if (!state._popup_opened.includes(value)){
            state._popup_opened.push(value)
        }
    },
    removeActionFromOpenedPopup(state, value) {
        let shovalue = value.replace('hide','show')
        if (state._popup_opened.length>0){
            let $finded = state._popup_opened.indexOf(shovalue)
            if ($finded != -1) {
                state._popup_opened.splice($finded, 1)
            }
        }
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}