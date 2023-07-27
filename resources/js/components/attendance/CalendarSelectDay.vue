<template>
<v-calendar :weekdays="$appOptions.calendar().weekdays" :locale="$i18n.locale" v-model="innerValue" @click:date="focusDay" type='month' :short-weekdays="shortWeekdays" :interval-format="IntervalFormatter" color="secondary">
    <template slot='day-label' slot-scope="{ date, day,month }">
        <v-btn @click="focusDay({date})" depressed fab rounded small :class="getIntervalStyle(date).class">{{day}}<span class="caption" v-if="day==1" v-html="$t('shortmonths.' + month)"></span></v-btn>
    </template>
</v-calendar>
</template>

<script>
export default {
    data: () => ({
        innerValue: null,
    }),
    props: {
        value: {},
    },
    computed: {
        shortWeekdays() {
            return this.$vuetify.breakpoint.smAndDown
        },
    },
    methods: {
        focusDay({date}) {
            this.innerValue = date
        },
        getIntervalStyle(interval) {
            let isfestivity = this.$appOptions.festivities().find(obj => obj.date == interval)
            if (interval == moment().format('YYYY-MM-DD')) {
                return {
                    'class': "calendar-day-today"
                }
            }
            if (isfestivity != null) {
                return {
                    'class': "calendar-day-festivity",
                    'tooltip': isfestivity.label
                }
            } else {
                return {
                    'class': "calendar-day-normal"
                }
            }
        },
        IntervalFormatter(timestamp, short) {
            return timestamp.time
        },
    },
    watch: {
        innerValue: {
            handler(newVal) {
                this.$emit("input", newVal);
                this.$nextTick(() => {
                    this.$emit("change");
                })
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
}
</script>
