import Vue from 'vue'
import VueRouter from "vue-router"
import store from './store';
import i18n from './i18n';

//AUTH
import AskReset from "../components/auth/AskReset.vue";
import ResetPassword from "../components/auth/ResetPassword.vue";
import ChangePassword from "../components/auth/ChangePassword.vue";
import Login from "../components/auth/Login.vue";
//ATTENDANCE
import AttendanceCalendar from "../components/attendance/AttendanceCalendar.vue";
import AttendanceList from "../components/attendance/AttendanceList.vue";
//COMMUNICATION
import CommunicationList from "../components/communication/CommunicationList.vue"
//ERROR
import PageNotFound from '../components/error/PageNotFound.vue'
//PAYSHEET
import PaysheetList from "../components/paysheet/PaysheetList.vue"
//STAFF
import StaffList from "../components/staff/StaffList.vue";
import User from "../components/staff/User.vue";

import Dashboard from "../components/Dashboard.vue"
import Layout from "../components/Layout.vue"

Vue.use(VueRouter);

const ifNotAuthenticated = (to, from, next) => {
    if (!store.getters.isAuthenticated) {
        store.commit('getAccessFromStorage')
    }
    if (!store.getters.isAuthenticated) {
        next();
        return
    }
    else {
        next('/dashboard')
    }
}

const ifAuthenticated = (to, from, next) => {
    if (!store.getters.isAuthenticated) {
        store.commit('getAccessFromStorage')
    }
    if (store.getters.isAuthenticated) {
        next();
        return
    }
    else {
        next('/')
    }
}

const ifHasPermission = (to, from, next) => {
    if (!store.getters.isAuthenticated) {
        store.commit('getAccessFromCookie')
    }
    if (store.getters.isAuthenticated) {
        if (store.getters.getMe!=null){
            if (Vue.prototype.$appOptions.permissions(to.meta.permission).indexOf(store.getters.getMe?.role_name ?? null) != -1) {
                next();
                return
            }
            else {
                next('/');
            }
        }
        else {
            store.dispatch('fetchUser').then((res)=>{
                if (Vue.prototype.$appOptions.permissions(to.meta.permission).indexOf(store.getters.getMe?.role_name ?? null) != -1) {
                    next();
                    return
                }
                else {
                    next('/');
                }
            })
        }

        
    }
    else {
        next('/login');
    }
}

const routes = [
    {
        path: "/",
        name: "login",
        component: Login,
        beforeEnter: ifNotAuthenticated,
    },
    {
        path: "/login",
        name: "secondlogin",
        component: Login,
        beforeEnter: ifNotAuthenticated,
    },
    {
        path: '/password/askreset/',
        name: 'askreset',
        component: AskReset,
        beforeEnter: ifNotAuthenticated,
    }, 
    {
        path: '/reset-password-go/',
        name: 'resetpassword',
        component: ResetPassword,
        beforeEnter: ifNotAuthenticated,
        props: (route) => ({
            token: route.params.token,
            email: route.params.email,
        }),
    },
    {
        path: "*",
        name: 'notfound',
        component: PageNotFound
    },
    {
        path: "/",
        name: "layout",
        component: Layout,
        beforeEnter: ifAuthenticated,
        children: [
            {
                path: '/changepassword',
                name: 'changepassword',
                component: ChangePassword,
                beforeEnter: ifAuthenticated
            },
            {
                path: "/dashboard",
                name: "dashboard",
                component: Dashboard
            },
            {
                path: "/attendance",
                name: "attendance",
                component: AttendanceList
            },
            {
                path: "/calendar",
                name: "calendar",
                component: AttendanceCalendar
            },
            {
                path: "/staff",
                name: "staff",
                component: StaffList,
                beforeEnter: ifHasPermission,
                meta: { permission: 'staff-list' },
            },
            {
                path: "/communication",
                name: "communication",
                component: CommunicationList
            },
            {
                path: "/paysheet",
                name: "paysheet",
                component: PaysheetList
            },
            {
                path: "/user",
                name: "user",
                component: User
            }
        ]
    },
]


export const router = new VueRouter({
    mode: 'history',
    base: import.meta.env.VITE_BASE_PATH,
    routes
}); 

window.popStateDetected = false
window.addEventListener('popstate', () => {
    window.popStateDetected = true
})

router.beforeEach((to, from, next) => {
    const IsItABackButton = window.popStateDetected
    window.popStateDetected = false
    if (!store.getters.isOffline) {
        if (!to.matched.length) {
            next('/notFound');
        } else {
            if (!store.getters.changes_not_saved) {
                if (store.getters.hasPopupOpen) {
                    let hidevalue = store.getters.getPopupOpened[0]
                    if (hidevalue) {
                        hidevalue = hidevalue.replace('show', 'hide')
                        store.dispatch('hideAction', hidevalue)
                        if (IsItABackButton) {
                            next(false)
                            return;
                        }
                    }
                }
                if (to.name) {
                    store.commit('setCurrentRoute', to.name)
                }
                next()
            }
            else {
                store.commit('showDialog', {
                    type: "confirm",
                    title: i18n.t('global.changes_not_saved_title'),
                    message: i18n.t('global.changes_not_saved_message'),
                    okCb: () => {
                        store.commit('setChanges_not_saved', false);
                        if (store.getters.hasPopupOpen) {
                            let hidevalue = store.getters.getPopupOpened[0]
                            if (hidevalue) {
                                hidevalue = hidevalue.replace('show', 'hide')
                                store.dispatch('hideAction', hidevalue)
                                next(false)
                                return;
                            }
                        }
                        if (to.name) {
                            store.commit('setCurrentRoute', to.name)
                        }
                        next();
                    },
                });
            }
        }
    }
});