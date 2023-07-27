<template>
    <main-container :class="{ 'pb-0': $vuetify.breakpoint.xsOnly }">
        <material-card icon="fas fa-calendar-alt" :title="$t('attendance.calendar_title')" class="pb-0 mb-0">
            <template slot="filters">
                <v-col cols="6" xs="6" sm="3">
                    <v-select hide-details :label="$t('attendance.type_id')" :items="$appOptions.attendanceTypes()" v-model="attendancecalendar_options.type" clearable item-text="type_name" item-value="id"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="3">
                    <v-select hide-details :label="$t('attendance.status')" :items="$appOptions.attendanceStatus()" v-model="attendancecalendar_options.accepted" item-text="label" item-value="value"></v-select>
                </v-col>
                <v-col cols="12" xs="12" sm="6" v-if="$can('attendance-others')">
                    <related-staff v-model="attendancecalendar_options.staff_ids" multiple></related-staff>
                </v-col>
            </template>
            <v-toolbar color="transparent" flat>
                <calendar-focus v-model="calendar.focus"></calendar-focus>
                <v-toolbar-title v-if="$refs.calendarcontainer" class="text-uppercase">
                    {{ $refs.calendarcontainer.$children[0].title }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <template v-if="$vuetify.breakpoint.xsOnly">
                    <v-menu bottom right>
                        <template v-slot:activator="{ on }">
                            <v-btn outlined v-on="on" small>
                                {{ typeToLabel }}
                                <v-icon right>fas fa-caret-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list>
                            <template v-for="item in $appOptions.calendarTypes()">
                                <v-list-item :key="item.value" @click="calendar.type = item.value">
                                    <v-list-item-title>{{ item.text }}</v-list-item-title>
                                </v-list-item>
                            </template>
                        </v-list>
                    </v-menu>
                </template>
                <template v-else>
                    <template v-for="item in $appOptions.calendarTypes()">
                        <v-btn text :key="item.value" :input-value="calendar.type == item.value" @click="calendar.type = item.value">
                            {{ item.text }}
                        </v-btn>
                    </template>
                </template>
                <v-spacer></v-spacer>
                <v-btn icon small @click="prev" :disabled="attendancecalendar_loading">
                    <v-icon>fas fa-chevron-left</v-icon>
                </v-btn>
                <v-btn icon small @click="next" :disabled="attendancecalendar_loading">
                    <v-icon>fas fa-chevron-right</v-icon>
                </v-btn>
            </v-toolbar>
            <v-row dense justify="center">
                <v-col cols="12" xs="12" sm="6" md="6" v-if="!$vuetify.breakpoint.xsOnly && calendar.type == 'day'" class="pt-0">
                    <v-sheet :height="calendarHeight.height">
                        <calendar-select-day v-model="calendar.focus" ref="calendar2"></calendar-select-day>
                    </v-sheet>
                </v-col>
                <v-col cols="12" xs="12" :sm="calendar.type == 'day' ? '6' : '12'" :md="calendar.type == 'day' ? '6' : '12'" class="py-0 pb-2">
                    <v-sheet :height="calendarHeight.height">
                        <calendar-element ref="calendarcontainer" v-model="calendar" :calendarevents="attendancecalendar" :loading="attendancecalendar_loading"></calendar-element>
                    </v-sheet>
                </v-col>
            </v-row>
            <v-row dense>
                <v-col cols="12" class="my-3">
                    <ul class="d-flex v-legend px-0 mt-auto">
                        <li class="mr-4" v-for="item in $appOptions.attendanceTypes()">
                            <div class="d-inline-flex align-center">
                                <span class="mr-1" :style="{ background: item.color }"></span>
                                <span>{{ item.type_name }}</span>
                            </div>
                        </li>
                    </ul>
                </v-col>
            </v-row>
        </material-card>
        <create-edit-attendance v-if="showCreateEditAttendance" @reload="reloadCalendar()"></create-edit-attendance>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        calendar: {
            focus: null,
            type: null,
            start: null,
            end: null,
        },
        originalattendancecalendar_options: null,
    }),
    computed: {
        showCreateEditAttendance() {
            return this.$store.getters.showCreateEditAttendance;
        },
        attendancecalendar_options: {
            get: function () {
                return this.$store.getters.getAttendanceCalendarOptions;
            },
            set: function (value) {
                this.$store.commit("setAttendanceCalendarOptions", value);
            },
        },
        attendancecalendar() {
            return this.$store.getters.getAttendanceCalendar;
        },
        attendancecalendar_loading() {
            return this.$store.getters.attendancecalendar_loading;
        },
        today() {
            return moment().format("Y-M-D");
        },
        calendarHeight() {
            let options = {
                height: 400,
                screenheight: 400,
            };
            if (this.$vuetify.breakpoint) {
                let height = this.$vuetify.breakpoint.height;
                options.height = height - 56 - 48 - 100 - 26;
                options.screenheight = options.height;
                if (this.$vuetify.breakpoint.mdAndUp == true) {
                    options.height = options.height - 8 - 16;
                }
                let occupied_space = this.$appOptions.calendar().displayedHours * this.$appOptions.calendar().intervalHeight;
                if (occupied_space + 70 < options.height) {
                    options.height = occupied_space + 70;
                }
            }
            return options;
        },
        typeToLabel() {
            const self = this;
            let testo = this.$appOptions.calendarTypes().find(function (v) {
                return v["value"] === self.calendar.type;
            });
            return testo.text;
        },
    },
    methods: {
        reloadCalendar() {
            this.originalattendancecalendar_options = _.cloneDeep(this.attendancecalendar_options);
            this.$store.dispatch("fetchAttendanceCalendar", this.attendancecalendar_options);
        },
        loadCalendar() {
            const self = this;
            if (self.$functions.ObjectAreEqual(self.originalattendancecalendar_options, self.attendancecalendar_options) == false) {
                self.originalattendancecalendar_options = _.cloneDeep(self.attendancecalendar_options);
                self.$store.dispatch("fetchAttendanceCalendar", self.attendancecalendar_options);
            }
        },
        view(date) {
            this.calendar.focus = date;
            this.calendar.date_start = date;
            this.calendar.date_end = date;
            this.calendar.type = "day";
        },
        setToday() {
            this.calendar.focus = this.today;
        },
        prev() {
            if (!this.attendancecalendar_loading) {
                if (this.$refs.calendar2) {
                    this.$refs.calendar2.$children[0].prev();
                } else {
                    this.$refs.calendarcontainer.$children[0].prev();
                }
            }
        },
        next() {
            if (!this.attendancecalendar_loading) {
                if (this.$refs.calendar2) {
                    this.$refs.calendar2.$children[0].next();
                } else {
                    this.$refs.calendarcontainer.$children[0].next();
                }
            }
        },
    },
    watch: {
        attendancecalendar_options: {
            handler(val) {
                this.loadCalendar();
            },
            deep: true,
        },
        "$vuetify.breakpoint.xsOnly": function (val) {
            if (val) {
                this.calendar.type = "4day";
            } else {
                this.calendar.type = "week";
            }
        },
    },
    created() {
        if (this.$vuetify.breakpoint.xsOnly) {
            this.calendar.type = "4day";
        } else {
            this.calendar.type = "week";
        }
    },
    mounted() {
        this.setToday();
        this.loadCalendar();
    },
};
</script>
