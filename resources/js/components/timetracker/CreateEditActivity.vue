<template>
    <create-edit-layout v-if="showCreateEditActivity && loaded" :disableedit="currentActivity.accepted" element="Activity" icon="fas fa-clock" @duplicatecomplete="$emit('duplicatecomplete')">
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider ref='project_id' vid='project_id' :name="$t('timetracker.project')" rules="required" v-slot="{ errors,field }">
                <v-select :label="$t('timetracker.project')" v-bind:items="$appOptions.projects()" v-model="currentActivity.project_id" item-text="title" item-value="id" small-chips :error-messages="errors">
                </v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider ref='type_id' vid='type_id' :name="$t('timetracker.type')" rules="required" v-slot="{ errors,field }">
                <v-select :label="$t('timetracker.type')" v-bind:items="$appOptions.activityTypes()" v-model="currentActivity.activity_type_id" item-text="title" item-value="id" small-chips :error-messages="errors">
                </v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="2">
            <date-field vid='day' :name="$t('timetracker.day')" rules="date_format" v-model="currentActivity.day"></date-field>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <time-field v-model="currentActivity.timepicker" rules="required" :name="$t('timetracker.hours')"></time-field>
        </v-col>
        <v-col cols="12" xs="12" sm="12">
            <ValidationProvider ref="note" vid="note" :name="$t('timetracker.note')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('timetracker.note')" v-model="currentActivity.note" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
    </create-edit-layout>
</template>

<script>
export default {
    data: () => ({
        stafflogin_loading: false,
        staffdisable_loading: false,
        temporaryCurrentActivity: null,
        internal_note_edit: false,
        managers_edit: false,
        timetable_edit: false,
        score: 0,
        loaded: false,
    }),
    computed: {
        currentActivity: {
            get: function () {
                return this.$store.getters.getCurrentActivity;
            },
            set: function (value) {
                this.$store.commit('setCurrentActivity', value)
            },
        },
        user() {
            return this.$store.getters.getUser;
        },
        isProfileEdit() {
            if (this.user.staff) {
                if (this.currentActivity.id === this.user.staff.id) {
                    return true;
                }
            }
            return this.$route.name === "activity";
        },
        showCreateEditActivity() {
            return this.$store.getters.showCreateEditActivity;
        }
    },
    methods: {
        showInstantEdit(model) {
            this.temporaryCurrentActivity = _.cloneDeep(this.currentActivity);
            this[model] = true;
        }
    },
    created() {
        if (this.currentActivity.id) {
            this.$store.dispatch("showActivityDetails").then((res) => {
                this.loaded = true;
            });
        } else {
            this.loaded = true;
        }
    },
};
</script>
