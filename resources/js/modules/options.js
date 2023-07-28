
import store from './store';
import i18n from './i18n';

export default {
    install(Vue, options) {
        Vue.prototype.$appOptions = {
            appLogoQuadrato: function () {
                if (import.meta.env.VITE_BASE_PATH){
                    return import.meta.env.VITE_BASE_PATH + '/img/digitalab_logo_quadrato.png'
                }
                return '/img/digitalab_logo_quadrato.png'
            },
            years: function () {
                let current = moment().format('YYYY')
                let years = []
                let user = store.getters.getUser
                let created = current
                if (user.staff) {
                    if (user.staff.date_start){
                        created = moment(user.staff.date_start).format('YYYY')
                    }
                }
                for (let year = created; year <= current; year++) {
                    years.push(parseInt(year))
                }
                return years
            },
            months: function (value=null) {
                let months = [
                    { label: i18n.t('global.months.1'), value: 1 },
                    { label: i18n.t('global.months.2'), value: 2 },
                    { label: i18n.t('global.months.3'), value: 3 },
                    { label: i18n.t('global.months.4'), value: 4 },
                    { label: i18n.t('global.months.5'), value: 5 },
                    { label: i18n.t('global.months.6'), value: 6 },
                    { label: i18n.t('global.months.7'), value: 7 },
                    { label: i18n.t('global.months.8'), value: 8 },
                    { label: i18n.t('global.months.9'), value: 9 },
                    { label: i18n.t('global.months.10'), value: 10 },
                    { label: i18n.t('global.months.11'), value: 11 },
                    { label: i18n.t('global.months.12'), value: 12 },
                ]
                if (value){
                    return months.find(obj => obj.value==value)?.label ?? null
                }
                return months
            },
            paysheetMonths: function (value = null) {
                let months = this.months()
                months.push({ label: i18n.t('global.months.13'), value: 13 })
                if (value) {
                    return months.find(obj => obj.value == value)?.label ?? null
                }
                return months
            },
            defaultRowsPerPage: function () {
                return 10
            },
            attendanceTypes: function (value = null) {
                let types = store.getters.getAttendanceTypes
                if (!types) {
                    if (!store.getters.attendancetypes_loading) {
                        store.dispatch('fetchAttendanceTypes')
                    }
                    if (value != null) {
                        return null
                    }
                    return []
                }
                let cloned = _.cloneDeep(types)
                if (value != null && types) {
                    return cloned.find(obj => obj.id == value)?.type_name ?? null
                }
                return cloned
            },
            relatedStaff: function (value = null) {
                let types = _.cloneDeep(store.getters.getRelatedStaff)
                if (types==null) {
                    if (!store.getters.relatedstaff_loading) {
                        store.dispatch('fetchRelatedStaff')
                    }
                    types=[]
                }
                types.push(store.getters.getMe)
                if (value != null && types) {
                    return types.find(obj => obj.id == value)
                }
                return types
            },
            permissions: function (value = null) {
                let types = [
                    { permission: 'attendance-others', roles: ['Admin', 'Responsabile']},
                    { permission: 'attendance-export', roles: ['Admin']},
                    { permission: 'staff-list', roles: ['Admin','Responsabile']},
                    { permission: 'roles-edit', roles: ['Admin'] },
                    { permission: 'communication-management', roles: ['Admin', 'Responsabile'] },
                    { permission: 'paysheet-others', roles: ['Admin'] },
                    { permission: 'attachmentmanagement-communication', roles: ['Admin', 'Responsabile'] },
                    { permission: 'attachmentmanagement-paysheet', roles: ['Admin', 'Responsabile'] },
                    { permission: 'attachmentmanagement-attendance', roles: ['Admin', 'Responsabile','Dipendente'] },
                    { permission: 'attachmentmanagement-staff', roles: ['Admin', 'Responsabile'] },
                ]
                if (value != null) {
                    return types.find(obj => obj.permission == value)?.roles ?? null
                }
                return types
            },
            genders: function (value = null) {
                let types = [
                    { value: null, label: i18n.t('global.gender_other') },
                    { value: 'M', label: i18n.t('global.gender_male') },
                    { value: 'F', label: i18n.t('global.gender_female') }
                ];
                if (value != null) {
                    return types.find(obj => obj.value == value).label
                }
                return types
            },
            attendanceStatus: function (value = null) {
                let types = [
                    { value: null, label: i18n.t('global.whatever') },
                    { value: true, label: i18n.t('attendance.accepted') },
                    { value: false, label: i18n.t('attendance.pending') }
                ];
                if (value != null) {
                    return types.find(obj => obj.value == value).label
                }
                return types
            },
            activityTypes: function (value = null) {
                let options = store.getters.getActivityTypes
                if (!options) {
                    if (!store.getters.activity_types_loading) {
                        store.dispatch('fetchActivityTypes')
                    }
                    if (value != null) {
                        return null
                    }
                    return []
                }
                let cloned = _.cloneDeep(options)
                if (value != null && options) {
                    return cloned.find(obj => obj.id == value)?.title ?? null
                }
                return cloned
            },
            projects: function (value = null) {
                let projects = store.getters.getProjects
                if (!projects) {
                    if (!store.getters.projects_loading) {
                        store.dispatch('fetchProjects')
                    }
                    if (value != null) {
                        return null
                    }
                    return []
                }
                let cloned = _.cloneDeep(projects)
                if (value != null && projects) {
                    return cloned.find(obj => obj.id == value)?.title ?? null
                }
                return cloned
            },
            staffRoles: function (value = null) {
                let types = store.getters.getStaffRoles
                if (!types) {
                    if (!store.getters.staffroles_loading) {
                        store.dispatch('fetchStaffRoles')
                    }
                    if (value != null) {
                        return null
                    }
                    return []
                }
                let cloned = _.cloneDeep(types)
                if (value != null && types) {
                    return cloned.find(obj => obj.id == value)?.role_name ?? null
                }
                return cloned
            },
            staffTypes: function (value = null) {
                let types = store.getters.getStaffTypes
                if (!types) {
                    if (!store.getters.stafftypes_loading) {
                        store.dispatch('fetchStaffTypes')
                    }
                    if (value != null) {
                        return null
                    }
                    return []
                }
                let cloned = _.cloneDeep(types)
                if (value != null && types) {
                    return cloned.find(obj => obj.id == value)?.type_name ?? null
                }
                return cloned
            },
            calendarTypes: function () {
                let options = [
                    { text: i18n.t('calendar.day'), value: 'day' },
                    { text: i18n.t('calendar.fourday'), value: '4day' },
                    { text: i18n.t('calendar.week'), value: 'week' },
                    { text: i18n.t('calendar.month'), value: 'month' }
                ]
                return options
            },
            calendar: function () {
                let options = {
                    intervalHeight: 40,
                    displayedHours: 11,
                    day_start: 8,
                    weekdays: [1, 2, 3, 4, 5, 6, 0],
                }
                return options
            },
            festivities: function () {
                return [
                    { date: '2019-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2019-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2019-04-21', label: i18n.t('festivity.pasqua') },
                    { date: '2019-04-22', label: i18n.t('festivity.pasquetta') },
                    { date: '2019-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2019-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2019-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2019-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2019-11-01', label: i18n.t('festivity.santi') },
                    { date: '2019-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2019-12-25', label: i18n.t('festivity.natale') },
                    { date: '2019-12-26', label: i18n.t('festivity.santostefano') },
                    { date: '2020-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2020-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2020-04-12', label: i18n.t('festivity.pasqua') },
                    { date: '2020-04-13', label: i18n.t('festivity.pasquetta') },
                    { date: '2020-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2020-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2020-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2020-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2020-11-01', label: i18n.t('festivity.santi') },
                    { date: '2020-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2020-12-25', label: i18n.t('festivity.natale') },
                    { date: '2020-12-26', label: i18n.t('festivity.santostefano') },
                    { date: '2021-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2021-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2021-04-04', label: i18n.t('festivity.pasqua') },
                    { date: '2021-04-05', label: i18n.t('festivity.pasquetta') },
                    { date: '2021-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2021-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2021-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2021-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2021-11-01', label: i18n.t('festivity.santi') },
                    { date: '2021-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2021-12-25', label: i18n.t('festivity.natale') },
                    { date: '2021-12-26', label: i18n.t('festivity.santostefano') },
                    { date: '2022-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2022-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2022-04-17', label: i18n.t('festivity.pasqua') },
                    { date: '2022-04-18', label: i18n.t('festivity.pasquetta') },
                    { date: '2022-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2022-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2022-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2022-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2022-11-01', label: i18n.t('festivity.santi') },
                    { date: '2022-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2022-12-25', label: i18n.t('festivity.natale') },
                    { date: '2022-12-26', label: i18n.t('festivity.santostefano') },
                    { date: '2023-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2023-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2023-04-09', label: i18n.t('festivity.pasqua') },
                    { date: '2023-04-10', label: i18n.t('festivity.pasquetta') },
                    { date: '2023-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2023-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2023-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2023-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2023-11-01', label: i18n.t('festivity.santi') },
                    { date: '2023-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2023-12-25', label: i18n.t('festivity.natale') },
                    { date: '2023-12-26', label: i18n.t('festivity.santostefano') },
                    { date: '2024-01-01', label: i18n.t('festivity.capodanno') },
                    { date: '2024-01-06', label: i18n.t('festivity.epifania') },
                    { date: '2024-03-31', label: i18n.t('festivity.pasqua') },
                    { date: '2024-04-01', label: i18n.t('festivity.pasquetta') },
                    { date: '2024-04-25', label: i18n.t('festivity.liberazione') },
                    { date: '2024-05-01', label: i18n.t('festivity.festalavoro') },
                    { date: '2024-06-02', label: i18n.t('festivity.festarepubblica') },
                    { date: '2024-08-15', label: i18n.t('festivity.ferragosto') },
                    { date: '2024-11-01', label: i18n.t('festivity.santi') },
                    { date: '2024-12-08', label: i18n.t('festivity.immacolata') },
                    { date: '2024-12-25', label: i18n.t('festivity.natale') },
                    { date: '2024-12-26', label: i18n.t('festivity.santostefano') },
                ]
            }
        }
    }
}
