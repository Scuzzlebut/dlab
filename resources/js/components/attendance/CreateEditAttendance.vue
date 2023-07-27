<template>
    <create-edit-layout @reload="emitReloadEvent" :emitreload="emitReload" v-if="showCreateEditAttendance && loaded" :disableedit="currentAttendance.accepted" element="Attendance" icon="fas fa-umbrella-beach" @duplicatecomplete="$emit('duplicatecomplete')">
        <div slot="instructions" v-if="$can('attendance-others')">
            <span v-if="currentAttendance.id">Dettaglio evento di <strong v-if="currentAttendance.staff">{{currentAttendance.staff.fullname}}</strong></span>
            <span v-else>Seleziona il dipendente per cui registrare l'evento e completa i dati</span>
        </div>
        <v-col cols="12" xs="12">
            <v-row dense :justify="currentAttendance.id ? 'end' : 'center'">
                <v-col cols="12" xs="12" sm="6" v-if="$can('attendance-others') && !currentAttendance.id">
                   <related-staff v-model="currentAttendance.staff_id" @change="setStaffData()"></related-staff>
                </v-col>
                <v-col cols="12" xs="12" sm="6" v-if="$can('attendance-others') && !currentAttendance.id" align="right">
                    <attachments-badge element="Attendance" class="ml-2"></attachments-badge>
                </v-col>
                <v-col cols="12" xs="12" v-if="$can('attendance-others') && currentAttendance.id" justify="center" align="right">
                    <span class="subtitle-1 mr-4" :class="{'text-success': currentAttendance.accepted}" v-html="attendanceStatus"></span>
                    <material-button v-if="!currentAttendance.accepted" small @click="acceptAttendance()" :disabled="attendance_loading || changes_not_saved" :loading="attendance_accepting" color="warning" :text="$t('attendance.accept')"></material-button>
                    <material-button v-else small @click="resetAttendance()" :disabled="attendance_loading" :loading="attendance_accepting" color="error" :text="$t('attendance.reset')"></material-button>
                    <attachments-badge element="Attendance" class="ml-2"></attachments-badge>
                </v-col>
            </v-row>
        </v-col>
        <v-col cols="12" xs="12" sm="4">
            <ValidationProvider ref='type_id' vid='type_id' :name="$t('attendance.type_id')" rules="required" v-slot="{ errors,field }">
                <v-select :label="$t('attendance.type_id')" v-bind:items="$appOptions.attendanceTypes()" v-model="currentAttendance.type_id" item-text="type_name" item-value="id" small-chips :error-messages="errors">
                </v-select>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12" sm="2">
            <date-field vid='date_start' :name="$t('attendance.date_start')" rules="date_format" v-model="currentAttendance.date_start"></date-field>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <time-field v-model="currentAttendance.date_start" rules="" :name="$t('attendance.time_start')"></time-field>
        </v-col>
        <v-col cols="12" xs="12" sm="2">
            <date-field vid='date_end' :name="$t('attendance.date_end')" rules="date_format" v-model="currentAttendance.date_end"></date-field>
        </v-col>
        <v-col cols="6" xs="6" sm="2">
            <time-field v-model="currentAttendance.date_end" rules="" :name="$t('attendance.time_end')"></time-field>
        </v-col>
        <v-col cols="12" xs="12" v-if="currentAttendance.type_id==4">
            <ValidationProvider ref="sick_note" vid='sick_note' :name="$t('attendance.sick_note')" rules="" v-slot="{ errors,field }">
                <v-textarea rows="1" auto-grow  :label="$t('attendance.sick_note')" v-model="currentAttendance.sick_note" :error-messages="errors"></v-textarea>
            </ValidationProvider>
        </v-col>
        <v-col cols="12" xs="12">
            <ValidationProvider ref="note" vid='note' :name="$t('attendance.note')" rules="" v-slot="{ errors,field }">
                <v-textarea rows="1" auto-grow  :label="$t('attendance.note')" v-model="currentAttendance.note" :error-messages="errors"></v-textarea>
            </ValidationProvider>
        </v-col>
    </create-edit-layout>
</template>

<script>
export default {
    data: () => ({
        attendance_accepting: false,
        loaded: false,
        emitReload: false
    }),
    computed: {
        currentAttendance: {
            get: function () {
                return this.$store.getters.getCurrentAttendance;
            },
            set: function (value) {
                this.$store.commit('setCurrentAttendance', value)
            },
        },
        showCreateEditAttendance() {
            return this.$store.getters.showCreateEditAttendance;
        },
        attendance_loading(){
            return this.$store.getters.attendance_loading
        },
        attendanceStatus(){
            let status = "<strong>" + this.$t('attendance.status') + ':</strong> '
            if (this.currentAttendance.accepted){
                status += this.$t('attendance.accepted')
            }
            else {
                status += this.$t('attendance.pending')
            }
            return status
        },
        changes_not_saved() {
            return this.$store.getters.changes_not_saved;
        },
    },
    methods: {
        emitReloadEvent(){
            this.$emit('reload')
        },
        setStaffData(){
            const self=this
            if (this.currentAttendance.staff_id!=null){
                this.currentAttendance.staff = _.cloneDeep(this.$appOptions.relatedStaff(self.currentAttendance.staff_id))
            }
        },
        acceptAttendance(){
            this.attendance_accepting = true
            this.$store.dispatch('acceptAttendance').then((res)=>{
                this.attendance_accepting=false
            }).catch((err) => {
                this.attendance_accepting=false
            })
        },
        resetAttendance(){
            this.attendance_accepting = true
            this.$store.dispatch('resetAttendance').then((res)=>{
                this.attendance_accepting=false
            }).catch((err) => {
                this.attendance_accepting=false
            })
        }
    },
    created(){
        if (this.currentAttendance.id){
            this.$store.dispatch('showAttendanceDetails').then((res)=>{
                this.loaded=true
                if (this.$store.getters.getAttendanceDragData!=null){
                    this.$nextTick(() => {
                        this.emitReload=true
                        this.currentAttendance.date_start = this.$store.getters.getAttendanceDragData.start
                        this.currentAttendance.date_end = this.$store.getters.getAttendanceDragData.end
                        this.$store.commit('setAttendanceDragData',null)
                    })
                }
            })
        }
        else {
            this.loaded=true
            if (this.$route.name=='calendar'){
                this.emitReload=true
            }
        }
    },
}
</script>
