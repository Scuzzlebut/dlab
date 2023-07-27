import Vue from 'vue'
import Vuex from 'vuex'

import attachment from './vuex_modules/attachment'
import attendance from './vuex_modules/attendance'
import communication from './vuex_modules/communication'
import dialog from './vuex_modules/dialog'
import files from './vuex_modules/files'
import global from './vuex_modules/global'
import paysheet from './vuex_modules/paysheet'
import popuphandler from './vuex_modules/popuphandler'
import staff from './vuex_modules/staff'
import user from './vuex_modules/user'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        attachment,
        attendance,
        communication,
        dialog,
        files,
        global,
        paysheet,
        popuphandler,
        staff,
        user
    }
});