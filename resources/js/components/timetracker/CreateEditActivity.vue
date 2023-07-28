<template>
    <create-edit-layout v-if="showCreateEditActivity && loaded" :disableedit="currentActivity.accepted" element="Activity" icon="fas fa-clock" @duplicatecomplete="$emit('duplicatecomplete')">
<!--        <v-col cols="12" xs="12" align="right" class="py-0">
            <material-button v-if="!isProfileEdit && canChangeManagers" @click="showInstantEdit('managers_edit')" small outlined :color="$functions.isEmpty(currentStaff.managers) ? '' : 'secondary'">
                {{ $t("staff.managers") }}
                <v-icon small class="pl-1">fas fa-user-tie</v-icon>
            </material-button>
            <material-button v-if="!isProfileEdit" class="ml-2" @click="showInstantEdit('internal_note_edit')" small outlined :color="$functions.isEmpty(currentStaff.note) ? '' : 'secondary'">
                {{ $t("staff.note") }}
                <v-icon small class="pl-1">far fa-sticky-note</v-icon>
            </material-button>
            <material-button v-if="!isProfileEdit" class="ml-2" @click="showInstantEdit('timetable_edit')" small outlined>
                {{ $t("staff.timetable") }}
                <v-icon small class="pl-1">far fa-clock</v-icon>
            </material-button>
            <attachments-badge class="ml-2" element="Staff"></attachments-badge>
        </v-col>-->
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
            <ValidationProvider ref="note" vid="note" :name="$t('timetracker.note')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('timetracker.note')" v-model="currentActivity.note" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <!--
        <v-col cols="12" xs="12" :sm="isProfileEdit || !$can('roles-edit') ? 6 : 4">
            <ValidationProvider ref="surname" vid="surname" :name="$t('staff.surname')" rules="required" v-slot="{ errors, field }">
                <v-text-field class="required" :label="$t('staff.surname')" v-model="currentStaff.surname" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" :sm="isProfileEdit || !$can('roles-edit') ? 6 : 4">
            <ValidationProvider ref="name" vid="name" :name="$t('staff.name')" rules="required" v-slot="{ errors, field }">
                <v-text-field class="required" :label="$t('staff.name')" v-model="currentStaff.name" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="2" v-if="!isProfileEdit && $can('roles-edit')">
            <ValidationProvider ref="role_id" vid="role_id" :name="$t('staff.role_id')" rules="required" v-slot="{ errors, field }">
                <v-select :label="$t('staff.role_id')" v-bind:items="$appOptions.staffRoles()" v-model="currentStaff.role_id" item-text="role_name" item-value="id" small-chips :error-messages="errors"></v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="taxcode" :name="$t('staff.taxcode')" rules="min:11|max:16" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.taxcode')" v-model="currentStaff.taxcode" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <ValidationProvider ref="gender" vid="gender" :name="$t('staff.gender')" rules="" v-slot="{ errors, field }">
                <v-select :label="$t('staff.gender')" v-bind:items="$appOptions.genders()" v-model="currentStaff.gender" item-text="label" item-value="value" small-chips :error-messages="errors"></v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <date-field vid="birthday" :name="$t('staff.birthday')" rules="date_format" v-model="currentStaff.birthday"></date-field>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="birthplace" :name="$t('staff.birthplace')" rules="max:100" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.birthplace')" v-model="currentStaff.birthplace" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="private_email" :name="$t('staff.private_email')" rules="email" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.private_email')" type="email" v-model="currentStaff.private_email" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="phone_number" :name="$t('staff.phone_number')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.phone_number')" type="tel" v-model="currentStaff.phone_number" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="iban" :name="$t('staff.iban')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.iban')" v-model="currentStaff.iban" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="5">
            <ValidationProvider vid="address" :name="$t('staff.address')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.address')" v-model="currentStaff.address" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider vid="city" :name="$t('staff.city')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.city')" v-model="currentStaff.city" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <ValidationProvider vid="state" :name="$t('staff.state')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.state')" v-model="currentStaff.state" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="6" xs="6" sm="1">
            <ValidationProvider vid="postcode" :name="$t('staff.postcode')" rules="" v-slot="{ errors, field }">
                <v-text-field :label="$t('staff.postcode')" type="number" v-model="currentStaff.postcode" :error-messages="errors"></v-text-field>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="3" class="pl-4 pb-2">
            <v-switch v-model="currentStaff.notifications_on" hide-details :label="$t('staff.notifications_on')"></v-switch>
        </v-col>
        <v-col cols="12" xs="12" sm="4" v-if="!isProfileEdit">
            <ValidationProvider ref="type_id" vid="type_id" :name="$t('staff.type_id')" rules="required" v-slot="{ errors, field }">
                <v-select :label="$t('staff.type_id')" v-bind:items="$appOptions.staffTypes()" v-model="currentStaff.type_id" item-text="type_name" item-value="id" small-chips :error-messages="errors"></v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="5" align="right" class="my-auto" v-if="currentStaff.date_end == null && !isProfileEdit">
            <material-button v-if="!isActiveUser" small @click="createStaffLogin()" :disabled="stafflogin_loading" :loading="stafflogin_loading" color="warning" :text="$t('staff.create_login')"><v-icon small class="pl-2">fas fa-key</v-icon></material-button>
            <material-button v-else small @click="disableLogin()" :disabled="stafflogin_loading" :loading="stafflogin_loading" outlined :text="$t('staff.remove_login')"><v-icon small class="pl-2">fas fa-key</v-icon></material-button>
            <material-button class="ml-2" small @click="disableStaff()" :disabled="staffdisable_loading" :loading="staffdisable_loading" outlined color="error" :text="$t('staff.disable')"><v-icon small class="pl-2">fas fa-fire-extinguisher</v-icon></material-button>
        </v-col>
        <instant-edit v-if="internal_note_edit" v-model="internal_note_edit" icon="far fa-sticky-note" :title="$t('staff.note')" @closeandupdate="currentStaff = temporaryCurrentStaff">
            <v-row justify="center" dense>
                <v-col cols="12" xs="12" sm="12">
                    <ValidationProvider ref="note" vid="note" :name="$t('staff.note')" rules="" v-slot="{ errors, field }">
                        <v-textarea rows="1" auto-grow :label="$t('staff.note')" v-model="temporaryCurrentStaff.note" :error-messages="errors"></v-textarea>
                    </ValidationProvider>
                </v-col>
            </v-row>
        </instant-edit>
        <instant-edit v-if="timetable_edit" v-model="timetable_edit" icon="far fa-clock" :title="$t('staff.timetable')" @closeandupdate="currentStaff = temporaryCurrentStaff">
            <v-row justify="center" dense>
                <v-col cols="12" xs="12" sm="6">
                    <date-field vid="date_start" :name="$t('staff.date_start')" rules="date_format" v-model="temporaryCurrentStaff.date_start"></date-field>
                </v-col>
                <v-col cols="12" xs="12" sm="6">
                    <date-field vid="date_end" :name="$t('staff.date_end')" rules="date_format" v-model="temporaryCurrentStaff.date_end"></date-field>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <time-field onlytime v-model="temporaryCurrentStaff.morning_starttime" rules="" :name="$t('staff.morning_starttime')"></time-field>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <time-field onlytime v-model="temporaryCurrentStaff.morning_endtime" rules="" :name="$t('staff.morning_endtime')"></time-field>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <time-field onlytime v-model="temporaryCurrentStaff.afternoon_starttime" rules="" :name="$t('staff.afternoon_starttime')"></time-field>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <time-field onlytime v-model="temporaryCurrentStaff.afternoon_endtime" rules="" :name="$t('staff.afternoon_endtime')"></time-field>
                </v-col>
            </v-row>
        </instant-edit>
        <instant-edit v-model="show_create_staff_login" icon="fas fa-key" :title="$t('staff.create_login')">
            <div slot="afterheading">
                Imposta la password da assegnare a
                <strong>{{ currentStaff.fullname }}</strong>
                <br />
                Creando l'account il dipendente potrà entrare nel gestionale e compiere delle azioni in base al ruolo assegnato. In qualsiasi momento potrai revocare le credenziali create.
            </div>
            <v-form autocomplete="off">
                <v-row dense>
                    <v-col cols="12" xs="12">
                        <ValidationProvider vid="email" :name="$t('user.email')" rules="required|email" v-slot="{ errors, field }">
                            <v-text-field type="text" prepend-icon="fas fa-user" :label="$t('user.email')" v-model="stafflogin.email" name="email" :error-messages="errors"></v-text-field>
                        </ValidationProvider>
                    </v-col>
                    <v-col cols="12" xs="12">
                        <ValidationProvider vid="password" :name="$t('auth.newpassword')" rules="required|min:6" v-slot="{ errors, field }">
                            <v-text-field :label="$t('auth.newpassword')" @keydown.space.prevent v-model="stafflogin.password" :error-messages="errors" type="password" @input="updateStrength" autocomplete="new-password">
                                <v-progress-circular slot="append-outer" size="50" :rotate="90" :value="score" :color="scoreColor" v-if="stafflogin.password">{{ displayedScore }}</v-progress-circular>
                            </v-text-field>
                        </ValidationProvider>
                        <span class="text-caption" :style="{ color: scoreColor }" v-if="stafflogin.password">{{ scoreMessage }}</span>
                    </v-col>
                    <v-col cols="12" xs="12">
                        <ValidationProvider vid="password" :name="$t('auth.password_confirmation')" rules="required|min:6" v-slot="{ errors, field }">
                            <v-text-field @keyup.enter="confirmCreateStaffLogin()" @keydown.space.prevent :label="$t('auth.password_confirmation')" v-model="stafflogin.password_confirmation" :error-messages="errors" type="password" autocomplete="new-password"></v-text-field>
                        </ValidationProvider>
                    </v-col>
                </v-row>
            </v-form>
            <template slot="instantactions">
                <v-col cols="6" xs="6" sm="4" align="right">
                    <material-button @click="undoCreateStaffLogin()" outlined :text="$t('global.undo')"></material-button>
                </v-col>
                <v-col cols="6" xs="6" sm="4" align="right">
                    <material-button @click="confirmCreateStaffLogin()" color="success" :text="$t('global.ok')" :loading="stafflogin_loading"></material-button>
                </v-col>
            </template>
        </instant-edit>
        <instant-edit v-if="managers_edit" v-model="managers_edit" icon="fas fa-user-tie" :title="$t('staff.managers')">
            <v-row justify="center" dense>
                <v-col cols="12" xs="12" sm="12">
                    <v-autocomplete v-model="temporaryCurrentStaff.managers" multiple :loading="relatedstaff_loading" :label="$t('staff.managers')" :items="managersStaff" item-text="fullname" item-value="id" menu-props="closeOnClick, overflowY"></v-autocomplete>
                </v-col>
            </v-row>
            <template slot="instantactions">
                <v-col cols="6" xs="6" sm="4" align="right">
                    <material-button @click="managers_edit = false" outlined :text="$t('global.undo')"></material-button>
                </v-col>
                <v-col cols="6" xs="6" sm="4" align="right">
                    <material-button @click="setManagers()" color="success" :text="$t('global.ok')" :loading="managers_loading"></material-button>
                </v-col>
            </template>
        </instant-edit>-->
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
