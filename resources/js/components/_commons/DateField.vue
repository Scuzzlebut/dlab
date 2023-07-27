<template>
<v-dialog v-model="datemenu" persistent width="290px" v-if="$vuetify.breakpoint.smAndDown">
    <template v-slot:activator="{ on, attrs }">
        <ValidationProvider :vid="vid" :name="name" :rules="rules" v-slot="{ errors,field }">
            <v-text-field :label="name" :value="displayedDate" :error-messages="personalerror!= null ? personalerror : errors" @change="newdate => updateManualDate(newdate)" :disabled="disabled" return-masked-value v-mask="'##/##/####'" @input="personalerror=null">
                <v-icon slot="prepend-inner" v-on="on">
                    far fa-calendar-alt
                </v-icon>
            </v-text-field>
        </ValidationProvider>
    </template>
    <v-date-picker first-day-of-week="1" :locale="$i18n.locale" :allowed-dates="val => allowedDates(val)" :value="$formatters.backEndDateWithoutTime(innerValue)" @input="value => updateDate(value)">
        <v-spacer></v-spacer>
        <v-btn text color="primary" @click="datemenu=false">{{$t('global.ok')}}</v-btn>
    </v-date-picker>
</v-dialog>
<v-menu v-else v-model="datemenu" :close-on-content-click="false" transition="scale-transition"  offset-overflow offset-y min-width="290px" :disabled="disabled">
    <template v-slot:activator="{ on, attrs }">
        <ValidationProvider :vid="vid" :name="name" :rules="rules" v-slot="{ errors,field }">
            <v-text-field :label="name" :value="displayedDate" :error-messages="personalerror!= null ? personalerror : errors" @change="newdate => updateManualDate(newdate)" :disabled="disabled" return-masked-value v-mask="'##/##/####'" @input="personalerror=null">
                <v-icon slot="prepend-inner" v-on="on">
                    far fa-calendar-alt
                </v-icon>
            </v-text-field>
        </ValidationProvider>
    </template>
    <v-date-picker first-day-of-week="1" :locale="$i18n.locale" :allowed-dates="val => allowedDates(val)" :value="$formatters.backEndDateWithoutTime(innerValue)" @input="value => updateDate(value)"></v-date-picker>
</v-menu>
</template>

<script>
export default {
    data: () => ({
        datemenu:false,
        innerValue: null,
        personalerror: null,
        nochangeemitted: false
    }),
    props: {
        value: {
            type: String,
            default: null
        },
        vid: {
            type: String,
            default: null
        },
        name: {
            type: String,
            default: null
        },
        rules: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        },
        allowedDatesFunction: {
            type: String,
            default: null
        },
        daterange: {
            type: Array,
            default: function () {
                []
            }
        }
    },
    methods: {
        allowedDates(val){
            if (this.allowedDatesFunction){
                switch (this.allowedDatesFunction) {
                    case 'isAfterToday':
                        return this.$functions.isAfterToday(val)
                        break;
                    case 'isBeforeToday':
                        return this.$functions.isBeforeToday(val)
                        break;
                    case 'isInDateRange':
                        return this.$functions.isInDateRange(val,this.daterange)
                        break;
                    default:
                        return val
                        break;
                }
            }
            return val
        },
        updateDate(inputdate){
            if (inputdate){
                let time = moment(this.innerValue).format('HH:mm')
                let newdate = moment(inputdate + ' ' + time , "YYYY-MM-DD HH:mm")
                if (newdate.isValid()){
                    this.personalerror=null
                    this.innerValue=newdate.format('YYYY-MM-DD HH:mm:ss')
                }
                else {
                    this.personalerror=[this.$t('global.date_not_valid')]
                    this.innerValue = inputdate
                }
                this.datemenu=false  
            }
            else {
                this.innerValue = null
                this.datemenu=false  
            }
        },
        updateManualDate(inputdate){
            if (inputdate){
                let time = moment(this.innerValue).format('HH:mm')
                let newdate = moment(inputdate + ' ' + time , "DD/MM/YYYY HH:mm")
                if (newdate.isValid()){
                    this.personalerror=null
                    this.innerValue=newdate.format('YYYY-MM-DD HH:mm:ss')
                }
                else {
                    this.personalerror=[this.$t('global.date_not_valid')]
                }
                this.datemenu=false  
            }
            else {
                this.innerValue = null
                this.datemenu=false  
            }
        },
    },
    computed: {
        displayedDate(){
            if (this.innerValue){
                return this.$formatters.frontEndDateFormat(this.innerValue)
            }
            this.personalerror=null
            return null
        },
    },
    watch: {
        innerValue(newVal) {
            this.$emit("input", newVal);
            this.$nextTick(() => {
                if (!this.nochangeemitted){
                    this.$emit("change");
                }
                this.nochangeemitted=false
            })
        },
        value(newVal) {
            this.innerValue = _.cloneDeep(newVal);
        },
    },
    created() {
        if (this.value) {
            this.nochangeemitted = true
            this.innerValue = _.cloneDeep(this.value);
        }
    }
}
</script>
