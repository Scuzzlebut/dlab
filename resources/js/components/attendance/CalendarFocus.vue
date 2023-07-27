<template>
<v-menu v-model="pickDate" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y min-width="290px">
    <template v-slot:activator="{ on }">
        <v-btn icon small v-on="on" :disabled="attendancecalendar_loading">
            <v-icon>far fa-calendar-alt</v-icon>
        </v-btn>
    </template>
    <v-date-picker first-day-of-week="1" :locale="$i18n.locale" v-model="innerValue" @input="pickDate = false" color="secondary"></v-date-picker>
</v-menu>
</template>

<script>
export default {
    data: () => ({
        innerValue: null,
        pickDate: false,
    }),
    props: {
        value: {},
    },
    computed: {
        attendancecalendar_loading(){
            return this.$store.getters.attendancecalendar_loading;
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
    created() {
        this.innerValue = _.cloneDeep(this.value);
    },
}
</script>
