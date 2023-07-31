<template>
    <main-container>
        <material-card icon="fas fa-clock" :title="$t('timetracker.list_title')" :main_action="main_action" @mainAction="createActivity" noduplicate>
            <template slot="selection-actions">
            </template>
            <template slot="filters">
                <v-col cols="6" xs="6" sm="3">
                    <v-select hide-details :label="$t('timetracker.type')" :items="$appOptions.activityTypes()" v-model="activity_options.type_id" clearable @change="activity_options.page = 1" item-text="title" item-value="id"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <v-select hide-details :label="$t('timetracker.project')" :items="$appOptions.projects()" v-model="activity_options.project_id" clearable @change="activity_options.page = 1" item-text="title" item-value="id"></v-select>
                </v-col>
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
import _ from "lodash";

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
            let newtime = moment("01/01/2000 00:00", "YYYY-MM-DD HH:mm");
            let element = {
                day: moment().format('YYYY-MM-DD'),
                timepicker: newtime.format("YYYY-MM-DD HH:mm:ss")
            }
            this.$store.commit('setCurrentActivity', element)
            this.$store.dispatch('showAction','showCreateEditActivity')
        },
        editActivity(item){
            let time = (item.hours + "").split(".")
            let hours = time[0]
            let minutes = time[1] || 0
            switch (minutes){
                case '25':
                    minutes = 15;
                    break;
                case '5':
                    minutes = 30;
                    break;
                case '75':
                    minutes = 45;
                    break;
                default:
                    minutes = "00";
                    break;
            }
            let newtime = moment("01/01/2000 "+hours + ":" + minutes, "YYYY-MM-DD HH:mm");
            item.timepicker = newtime.format("YYYY-MM-DD HH:mm:ss")
            this.$store.commit('setCurrentActivity', _.cloneDeep(item))
            this.$store.dispatch("showAction", "showCreateEditActivity");
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
