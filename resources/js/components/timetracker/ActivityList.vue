<template>
    <main-container>
        <material-card icon="fas fa-clock" :title="$t('timetracker.list_title')" :main_action="main_action" @mainAction="createActivity" noduplicate>
            <template slot="selection-actions">
            </template>
            <template slot="filters">
            </template>
            <material-table @click-action="editActivity" :headers="activity_headers" :items="activities.data" :options.sync="activity_options" :server-items-length="activities.total" :loading="activity_loading">
                <template v-slot:item.day="{ item }">{{ $formatters.frontEndDateFormat(item.day) }}</template>
                <template v-slot:item.delete="{ item }">
                    <material-button outlined @click="deleteActivity(item)" color="maingrey" small ><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                </template>
            </material-table>
            <create-edit-activity v-if="showCreateEditActivity"></create-edit-activity>
        </material-card>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        original_activities_options: null,
        deleting_id: null,
    }),
    computed: {
        main_action(){
            if (this.$hasRole('Admin')){
                return {
                    icon: 'fas fa-plus',
                    text: this.$t('timetracker.mainaction')
                }
            }
        },
        activity_options: {
            get: function () {
                return this.$store.getters.getActivityOptions;
            },
            set: function (value) {
                this.$store.commit('setActivityOptions', value);
            },
        },
        activity_headers() {
            let headers = []
            headers.push({
                text: this.$t('timetracker.user'),
                value: 'staff.fullname',
                align: 'left',
                sortable: true,
            })
            headers.push({
                text: this.$t('timetracker.project'),
                value: 'project.title',
                align: 'left',
                sortable: true,
            })
            headers.push({
                text: this.$t('timetracker.type'),
                value: 'type.title',
                align: 'left',
                sortable: true,
            })
            headers.push({
                text: this.$t('timetracker.day'),
                value: 'day',
                align: 'left',
                sortable: true,
            })
            headers.push({
                text: this.$t('timetracker.hours'),
                value: 'hours',
                align: 'left',
                sortable: true,
            })
            headers.push({
                text: this.$t('timetracker.note'),
                value: 'note',
                align: 'left',
            })
            headers.push({
                text: '',
                value: 'delete',
                align: 'center',
            })
            return {data: headers, id: 'activity_headers'}
        },
        activity_loading() {
            return this.$store.getters.activity_loading;
        },
        activities() {
            return this.$store.getters.getActivities;
        },
        showCreateEditActivity(){
            return this.$store.getters.showCreateEditActivity
        }
    },
    methods: {
        deleteActivity(item){
            this.deleting_id = item.id
            const self=this
            this.$store.dispatch('deleteActivity', item.id).then((res)=>{
                self.deleting_id=null
            }).catch((err)=>{
                self.deleting_id=null
            });
        },
        createActivity(){
            let element={
                date_start: moment().format('YYYY-MM-DD'),
                role_id: 1,
                type_id: 1,
                gender: 'M',
                morning_starttime: '09:00',
                morning_endtime: '13:00',
                afternoon_starttime: '14:00',
                afternoon_endtime: '18:00'
            }
            this.$store.dispatch('showAction','showCreateEditActivity')
        },
        editActivity(item){
            let cloned =_.cloneDeep(item)
            if (!cloned.morning_starttime){
                cloned.morning_starttime= '09:00',
                cloned.morning_endtime= '13:00',
                cloned.afternoon_starttime= '14:00',
                cloned.afternoon_endtime= '18:00'
            }
            this.$store.dispatch('showAction','showCreateEditActivity')
        },
        loadActivities() {
            if (!this.$functions.ObjectAreEqual(this.original_activities_options, this.activity_options)) {
                this.original_activities_options = _.cloneDeep(this.activity_options)
                this.$store.dispatch('fetchActivities');
            }
        },
    },
    watch: {
        activity_options: {
            handler() {
                this.loadActivities()
            },
            deep: true,
        },
    }
}
</script>
