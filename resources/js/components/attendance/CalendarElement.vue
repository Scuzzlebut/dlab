<template>
    <v-calendar ref="calendar" show-month-on-first :event-more="innerValue.type == 'month' ? true : false" :weekdays="$appOptions.calendar().weekdays" :locale="$i18n.locale" v-model="innerValue.focus" :type="innerValue.type" @click:event="showEvent" @click:more="viewDay" @click:date="viewDay" @change="updateRange" :first-interval="$appOptions.calendar().day_start" :interval-count="innerValue.type == 'month' ? 5 : $appOptions.calendar().displayedHours" :interval-height="$appOptions.calendar().intervalHeight" :short-weekdays="shortWeekdays" :interval-format="IntervalFormatter" event-overlap-mode="column" :events="computedCalendarEvents" :event-color="getEventColor" :event-name="getEventName" color="secondary" @mousedown:event="startDrag" @mousedown:time="startTime" @mousemove:time="mouseMove" @mouseup:time="endDrag" @mouseleave.native="cancelDrag">
        <template slot="day-label-header" slot-scope="{ date, day, month }">
            <v-btn v-if="!$vuetify.breakpoint.xsOnly" @click="focusDay({ date })" depressed fab rounded small class="px-1" :class="getIntervalStyle(date).class">
                {{ day }}
                <span class="caption" v-if="day == 1" v-html="$t('shortmonths.' + month)"></span>
            </v-btn>
            <v-btn v-else class="px-1 caption" @click="focusDay({ date })" small depressed :class="getIntervalStyle(date).class">
                {{ day }}
                <span class="caption" v-html="$t('shortmonths.' + month)" v-if="innerValue.type == '4day'"></span>
                <span class="caption" v-html="$t('months.' + month)" v-else></span>
            </v-btn>
        </template>
        <template slot="day-label" slot-scope="{ date, day, month }">
            <v-btn @click="focusDay({ date })" depressed fab rounded small class="px-1" :class="getIntervalStyle(date).class">
                {{ day }}
                <span class="caption" v-if="day == 1" v-html="$t('shortmonths.' + month)"></span>
            </v-btn>
        </template>
        <template #day-body="{ date, week }">
            <div v-if="isToday(date)" class="v-current-time first" :style="{ top: nowY() }"></div>
        </template>
        <template #category="{ date, category }">
            <initial-avatar type="category" :text="category"></initial-avatar>
        </template>
    </v-calendar>
</template>

<script>
export default {
    data: () => ({
        innerValue: null,
        dragEvent: null,
        dragStart: null,
        createEvent: null,
        createStart: null,
        extendOriginal: null,
        isDragging: false,
        shakeEvent: null,
    }),
    props: {
        value: {},
        calendarevents: {
            type: [Array, Object],
            default: null,
        },
        type: {
            type: String,
            default: null,
        },
    },
    computed: {
        showCreateEditAttendance() {
            return this.$store.getters.showCreateEditAttendance;
        },
        computedCalendarEvents() {
            let data = _.cloneDeep(this.calendarevents);
            if (this.createEvent) {
                data.push(this.createEvent);
            }
            if (this.dragEvent) {
                let finded = data.findIndex((obj) => obj.attendance_id == this.dragEvent.attendance_id);
                if (finded != -1) {
                    data[finded] = this.dragEvent;
                }
            }
            return data;
        },
        attendancecalendar_options: {
            get: function () {
                return this.$store.getters.getAttendanceCalendarOptions;
            },
            set: function (value) {
                this.$store.commit("setAttendanceCalendarOptions", value);
            },
        },
        attendancecalendar_loading() {
            return this.$store.getters.attendancecalendar_loading;
        },
        cal() {
            return this.$refs.calendar;
        },
        shortWeekdays() {
            return this.$vuetify.breakpoint.smAndUp || this.innerValue.type != "day";
        },
    },
    methods: {
        nowY() {
            if (this.$refs.calendar) {
                if (this.$refs.calendar.timeToY(this.$refs.calendar.times.now)) {
                    return this.$refs.calendar.timeToY(this.$refs.calendar.times.now) + "px";
                }
                return "-10px";
            }
        },
        prev() {
            if (!this.activitiescalendar_loading) {
                if (this.cal) {
                    this.cal.prev();
                }
            }
        },
        next() {
            if (!this.activitiescalendar_loading) {
                if (this.cal) {
                    this.cal.next();
                }
            }
        },
        getIntervalStyle(interval) {
            let isfestivity = this.$appOptions.festivities().find((obj) => obj.date == interval);
            if (interval == moment().format("YYYY-MM-DD")) {
                return {
                    class: "calendar-day-today",
                };
            }
            if (isfestivity != null) {
                return {
                    class: "calendar-day-festivity",
                    tooltip: isfestivity.label,
                };
            } else {
                return {
                    class: "calendar-day-normal",
                };
            }
        },
        focusDay({ date }) {
            this.innerValue.type = "day";
            this.innerValue.focus = date;
        },
        viewDay({ date }) {
            this.innerValue.focus = date;
            this.innerValue.type = "day";
        },
        updateRange({ start, end }) {
            this.innerValue.date_start = start;
            this.innerValue.date_end = end;
            if (this.type != "edit") {
                this.attendancecalendar_options.date_start = start.date;
                this.attendancecalendar_options.date_end = end.date;
            }
            if (moment(this.innerValue.date_start.date).isBefore() && moment(this.innerValue.date_end.date).isAfter()) {
                this.scrollToTime();
            }
        },
        scrollToTime() {
            if (this.ready) {
                const time = this.getCurrentTime();
                const first = Math.max(0, time - (time % 30) - 30);
                if (this.cal) {
                    this.cal.scrollToTime(first);
                }
            }
        },
        getCurrentTime() {
            return this.cal ? this.cal.times.now.hour * 60 + this.cal.times.now.minute : 0;
        },
        IntervalFormatter(timestamp, short) {
            return timestamp.time;
        },
        getEventColor(event) {
            let color = "secondary";
            if (event.color) {
                color = event.color;
            }
            if (event.attendance_id) {
                if (moment(event.end, "Y-M-D HH:mm:ss").isBefore(moment(), "minute")) {
                    color = this.$formatters.pSBC(+0.3, color, false, true);
                }
            }
            return color;
        },
        getEventName(event) {
            let myevent = event;
            let content = null;
            const self = this;
            if (event.input) {
                myevent = event.input;
            }
            if (myevent.staff_fname != null) {
                content = "<span style='white-space: pre-line;'><strong>" + myevent.staff_fname + "</strong></span>";
            }
            content += "<br><span style='white-space: pre-line;' class='caption'>" + this.$formatters.backEndTimeWithoutDate(myevent.start) + " - " + this.$formatters.backEndTimeWithoutDate(myevent.end) + "</span>";
            if (myevent.type != null) {
                content += '<br><i aria-hidden="true" class="v-icon notranslate fas fa-tag theme--dark" style="font-size: 11px;padding-right:2px"></i>';
                content += "<span style='white-space: pre-line;'>" + myevent.type + "</span>";
            }
            return content;
        },
        isToday(date) {
            return date == moment().format("YYYY-MM-DD");
        },
        startDrag({ event, timed, nativeEvent }) {
            if (event && timed) {
                this.dragEvent = event;
                this.dragTime = null;
                this.extendOriginal = null;
                this.isDragging = true;
                if (event.accepted) {
                    this.shakeEvent = nativeEvent;
                }
            }
        },
        startTime(tms) {
            if (this.shakeEvent == null) {
                this.isDragging = true;
                const mouse = this.toTime(tms);
                if (this.dragEvent && this.dragTime === null) {
                    const start = moment(this.dragEvent.start).valueOf();
                    this.dragTime = mouse - start;
                } else {
                    this.createStart = this.roundTime(mouse);
                    this.createEvent = {
                        id: "newevent",
                        start: moment(this.createStart).format("YYYY-MM-DD HH:mm:ss"),
                        end: moment(this.createStart).format("YYYY-MM-DD HH:mm:ss"),
                        staff_fname: "Registra assenza",
                        color: "warning",
                        timed: true,
                    };
                }
            }
        },
        extendBottom(event) {
            this.createEvent = event;
            this.createStart = event.start;
            this.extendOriginal = event.end;
        },
        mouseMove(tms) {
            if (this.isDragging) {
                const mouse = this.toTime(tms);

                if (this.dragEvent && this.dragTime !== null) {
                    const start = moment(this.dragEvent.start).valueOf();
                    const end = moment(this.dragEvent.end).valueOf();
                    const duration = end - start;
                    const newStartTime = mouse - this.dragTime;
                    const newStart = this.roundTime(newStartTime);
                    const newEnd = newStart + duration;
                    this.dragEvent.start = moment(newStart).format("YYYY-MM-DD HH:mm:ss");
                    this.dragEvent.end = moment(newEnd).format("YYYY-MM-DD HH:mm:ss");
                } else if (this.createEvent && this.createStart !== null) {
                    const mouseRounded = this.roundTime(mouse, false);
                    const min = Math.min(mouseRounded, this.createStart);
                    const max = Math.max(mouseRounded, this.createStart);

                    this.createEvent.start = moment(min).format("YYYY-MM-DD HH:mm:ss");
                    this.createEvent.end = moment(max).format("YYYY-MM-DD HH:mm:ss");
                }
            }
            this.handleShakeEvent();
        },
        endDrag() {
            this.isDragging = false;
            if (this.createEvent) {
                let element = {
                    date_start: this.createEvent.start,
                    date_end: this.createEvent.end,
                };
                this.$store.dispatch("setCurrentModelForAttachment", {
                    object: element,
                    filecategory: "attendance",
                });
                this.$store.dispatch("showAction", "showCreateEditAttendance");
            }
            if (this.dragEvent) {
                let element = this.dragEvent;
                element.id = this.dragEvent.attendance_id;
                this.$store.dispatch("setCurrentModelForAttachment", {
                    object: element,
                    filecategory: "attendance",
                });
                this.$store.commit("setAttendanceDragData", this.dragEvent);
                this.$store.dispatch("showAction", "showCreateEditAttendance");
            }
            this.shakeEvent = null;
        },
        cancelDrag() {
            if (this.isDragging) {
                if (this.createEvent) {
                    if (this.extendOriginal) {
                        this.createEvent.end = moment(this.extendOriginal).format("YYYY-MM-DD HH:mm:ss");
                    }
                }
                this.resetDrag();
            }
        },
        handleShakeEvent() {
            if (this.shakeEvent != null) {
                if (this.shakeEvent.target != null) {
                    const self = this;
                    this.shakeEvent.target.classList.add("shake");
                    setTimeout(() => {
                        if (self.shakeEvent) {
                            self.shakeEvent.target.classList.remove("shake");
                            self.shakeEvent = null;
                        }
                    }, 1000);
                }
            }
        },
        roundTime(time, down = true) {
            const roundTo = 15; // minutes
            const roundDownTime = roundTo * 60 * 1000;

            return down ? time - (time % roundDownTime) : time + (roundDownTime - (time % roundDownTime));
        },
        toTime(tms) {
            return new Date(tms.year, tms.month - 1, tms.day, tms.hour, tms.minute).getTime();
        },
        resetDrag() {
            this.dragTime = null;
            this.dragEvent = null;
            this.createEvent = null;
            this.createStart = null;
            this.extendOriginal = null;
            this.shakeEvent = null;
        },
        showEvent(event) {
            let calendar = _.cloneDeep(event.event);
            calendar.id = calendar.attendance_id;
            this.$store.commit("setCurrentAttendance", calendar);
            this.$store.dispatch("showAction", "showCreateEditAttendance");
        },
    },
    watch: {
        calendarevents: {
            handler(newVal) {
                this.resetDrag();
            },
            deep: true,
        },
        innerValue: {
            handler(newVal) {
                this.$emit("input", newVal);
                this.$emit("change");
            },
            deep: true,
        },
        value: {
            handler(newVal) {
                this.innerValue = newVal;
            },
            deep: true,
        },
    },
    mounted() {
        const self = this;
        self.scrollToTime();
    },
    created() {
        this.innerValue = _.cloneDeep(this.value);
    },
};
</script>
