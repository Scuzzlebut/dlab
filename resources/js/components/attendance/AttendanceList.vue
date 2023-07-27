<template>
    <main-container>
        <material-card icon="fas fa-umbrella-beach" :title="$t('attendance.list_title')" :exportElement="$can('attendance-export') ? { element: 'Attendance', types: ['xls'] } : {}" :main_action="main_action" @mainAction="createAttendance" noduplicate :selected="selected_attendance" @unselectAll="selected_attendance = []">
            <template slot="selection-actions">
                <v-btn class="ma-1 mr-0" v-if="allSelectedCanBeDeleted" outlined @click="deleteAttendance()" small :disabled="attendance_loading" :loading="attendance_deleting">
                    {{ $t("global.delete") }}
                    <v-icon small class="pl-1">fas fa-trash-alt</v-icon>
                </v-btn>
            </template>
            <template slot="filters">
                <v-col cols="6" xs="6" sm="2">
                    <v-select hide-details :label="$t('global.year')" :items="$appOptions.years()" v-model="attendance_options.year" clearable @change="attendance_options.page = 1"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="2">
                    <v-select hide-details :label="$t('global.month')" :items="$appOptions.months()" v-model="attendance_options.month" clearable item-text="label" item-value="value" :disabled="!attendance_options.year" @change="attendance_options.page = 1"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="3" v-if="$can('attendance-others')">
                    <v-select hide-details :label="$t('attendance.staff_type')" :items="$appOptions.staffTypes()" v-model="attendance_options.staff_types" multiple clearable @change="attendance_options.page = 1" item-text="type_name" item-value="id"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <v-select hide-details :label="$t('attendance.type_id')" :items="$appOptions.attendanceTypes()" v-model="attendance_options.type" clearable @change="attendance_options.page = 1" item-text="type_name" item-value="id"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="2">
                    <v-select hide-details :label="$t('attendance.status')" :items="$appOptions.attendanceStatus()" v-model="attendance_options.accepted" @change="attendance_options.page = 1" item-text="label" item-value="value"></v-select>
                </v-col>
                <v-col cols="12" xs="12" sm="6" v-if="$can('attendance-others')">
                    <related-staff v-model="attendance_options.staff_ids" multiple @change="attendance_options.page = 1"></related-staff>
                </v-col>
                <v-col cols="12" xs="12" :sm="$can('attendance-others') ? 6 : 3">
                    <search-box v-model="attendance_options.search" nocolor @change="attendance_options.page = 1"></search-box>
                </v-col>
            </template>
            <material-table @click-action="editAttendance" :selected.sync="selected_attendance" show-select :headers="attendance_headers" :items="attendance.data" :options.sync="attendance_options" :server-items-length="attendance.total" :loading="attendance_loading">
                <template v-slot:item.date_start="{ item }">{{ $formatters.formatDateTime(item.date_start) }}</template>
                <template v-slot:item.hours="{ item }">{{ item.hours.toFixed(2) }}</template>
                <template v-slot:item.date_end="{ item }">{{ $formatters.formatDateTime(item.date_end) }}</template>
                <template v-slot:item.type_id="{ item }"><v-chip small v-html="$appOptions.attendanceTypes(item.type_id)"></v-chip></template>
                <template v-slot:item.accepted="{ item }">
                    <v-icon color="success" v-if="item.accepted">fas fa-check</v-icon>
                    <v-icon color="warning" v-else>fas fa-clock</v-icon>
                </template>
            </material-table>
            <create-edit-attendance v-if="showCreateEditAttendance"></create-edit-attendance>
        </material-card>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        originalattendance_options: null,
        selected_attendance: [],
        attendance_deleting: false,
    }),
    computed: {
        main_action() {
            return {
                icon: "fas fa-plus",
                text: this.$t("attendance.mainaction"),
            };
        },
        attendance_options: {
            get: function () {
                return this.$store.getters.getAttendanceOptions;
            },
            set: function (value) {
                this.$store.commit("setAttendanceOptions", value);
            },
        },
        attendance_headers() {
            let headers = [];
            headers.push({
                text: this.$t("attendance.staff_id"),
                value: "staff.fullname",
                align: "left",
                sortable: true,
                selected: true,
            });
            headers.push({
                text: this.$t("attendance.date_start"),
                value: "date_start",
                align: "left",
                sortable: true,
                selected: true,
            });
            headers.push({
                text: this.$t("attendance.date_end"),
                value: "date_end",
                align: "left",
                sortable: true,
                selected: true,
            });
            headers.push({
                text: this.$t("attendance.sick_note"),
                value: "sick_note",
                align: "left",
                sortable: true,
                selected: false,
            });
            headers.push({
                text: this.$t("attendance.note"),
                value: "note",
                align: "left",
                sortable: true,
                selected: true,
            });
            headers.push({
                text: this.$t("attendance.hours"),
                value: "hours",
                align: "left",
                sortable: true,
                selected: true,
                width: "1%",
            });
            headers.push({
                text: this.$t("attendance.type_id"),
                value: "type_id",
                align: "left",
                sortable: true,
                selected: true,
                width: "1%",
            });
            headers.push({
                text: this.$t("attendance.status"),
                value: "accepted",
                align: "left",
                sortable: true,
                selected: true,
                width: "1%",
            });
            headers.push({
                text: this.$t("attendance.accepted_by"),
                value: "approver.fullname",
                align: "left",
                sortable: true,
                selected: false,
            });
            return { data: headers, id: "attendance_headers" };
        },
        attendance_loading() {
            return this.$store.getters.attendance_loading;
        },
        attendance() {
            return this.$store.getters.getAttendance;
        },
        allSelectedCanBeDeleted() {
            let status = true;
            for (let element of this.selected_attendance) {
                if (element.accepted) {
                    status = false;
                    break;
                }
            }
            return status;
        },
        me() {
            return this.$store.getters.getMe;
        },
        showCreateEditAttendance() {
            return this.$store.getters.showCreateEditAttendance;
        },
    },
    methods: {
        createAttendance() {
            let date = moment().format("YYYY-MM-DD");
            let minutes = moment().format("mm");
            minutes = Math.round(minutes / 15) * 15;
            if (minutes >= 60) {
                minutes = 0;
            }
            let hours = moment().format("HH");
            let newtime = moment(date + " " + hours + ":" + minutes, "YYYY-MM-DD HH:mm");
            let element = {
                date_start: newtime.format("YYYY-MM-DD HH:mm:ss"),
                staff_id: this.me?.id ?? null,
                staff: this.me ?? null,
            };
            if (element.staff) {
                element.date_end = moment(date + " " + element.staff.afternoon_endtime, "YYYY-MM-DD HH:mm");
            }
            this.$store.dispatch("setCurrentModelForAttachment", {
                object: element,
                filecategory: "attendance",
            });
            this.$store.dispatch("showAction", "showCreateEditAttendance");
        },
        editAttendance(item) {
            this.$store.dispatch("setCurrentModelForAttachment", {
                object: _.cloneDeep(item),
                filecategory: "attendance",
            });
            this.$store.dispatch("showAction", "showCreateEditAttendance");
        },
        deleteAttendance() {
            const self = this;
            let message = self.$t("global.delete_confirm_message_pre");
            if (self.selected_attendance.length <= 1) {
                message += self.$t("attendance.question_selected");
            } else {
                message += self.selected_attendance.length + self.$t("attendance.question_multiple_selected");
            }
            self.$store.commit("showDialog", {
                type: "confirm",
                title: self.$t("global.delete_confirm_title"),
                message: message,
                okCb: () => {
                    self.selected_attendance.forEach((element) => {
                        self.$store.dispatch("deleteAttendance", element.id).then((res) => {
                            self.$functions.actionOnSelection(res, self.selected_attendance);
                        });
                    });
                },
            });
        },
        loadAttendance() {
            if (!this.$functions.ObjectAreEqual(this.originalattendance_options, this.attendance_options)) {
                this.originalattendance_options = _.cloneDeep(this.attendance_options);
                this.$store.dispatch("fetchAttendance");
            }
        },
    },
    watch: {
        attendance_options: {
            handler() {
                this.loadAttendance();
            },
            deep: true,
        },
    },
    mounted() {
        if (this.attendance_options.year == null) {
            this.attendance_options.year = parseInt(moment().format("Y"));
        }
        // solo gli admin possono vedono le assenze da confermare, i dipendenti vedono qualsiasi richiesta
        if (this.$can("attendance-others")) {
            this.attendance_options.accepted = false;
        }
    },
};
</script>
