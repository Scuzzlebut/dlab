<template>
    <v-dialog v-model="timemenu" persistent width="290px" v-if="$vuetify.breakpoint.smAndDown">
        <template v-slot:activator="{ on, attrs }">
            <ValidationProvider :vid="vid" :name="name" :rules="rules" v-slot="{ errors,field }">
                <v-text-field :label="name" :value="displayedTime" :error-messages="errors" @change="newtime => updateTime(newtime)" return-masked-value v-mask="'##:##'" :hide-details="dense && !errors" :dense="dense">
                    <v-icon slot="prepend" v-on="on">
                        far fa-clock
                    </v-icon>
                </v-text-field>
            </ValidationProvider>
        </template>
        <v-time-picker full-width :locale="$i18n.locale" :allowed-minutes="allowedMinutes" ampm-in-title format="24hr" :value="onlytime ? innerValue : $formatters.backEndTimeWithoutDate(innerValue)" @input="newtime => temptime=newtime" @change="newtime => updateTime(newtime)">
            <v-row align="center" justify="end" dense>
                <v-col cols="6" xs="6" sm="3">
                    <material-button @click="temptime ? updateTime(temptime) : timemenu=false" color="warning" :text="$t('global.ok')"></material-button>
                </v-col>
            </v-row>
        </v-time-picker>
    </v-dialog>
    <v-menu v-else ref="menu" v-model="timemenu" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y max-width="290px" min-width="290px">
        <template v-slot:activator="{ on, attrs }">
            <ValidationProvider :vid="vid" :name="name" :rules="rules" v-slot="{ errors,field }">
                <v-text-field :label="name" :value="displayedTime" :error-messages="errors" @change="newtime => updateTime(newtime)" return-masked-value v-mask="'##:##'" :hide-details="dense && !errors" :dense="dense">
                    <v-icon slot="prepend" v-on="on">
                        far fa-clock
                    </v-icon>
                </v-text-field>
            </ValidationProvider>
        </template>
        <v-time-picker :locale="$i18n.locale" :allowed-minutes="allowedMinutes" ampm-in-title format="24hr" :value="onlytime ? innerValue : $formatters.backEndTimeWithoutDate(innerValue)" @input="newtime => temptime=newtime" @change="newtime => updateTime(newtime)">
            <v-row align="center" justify="end" dense>
                <v-col cols="6" xs="6" sm="3">
                    <material-button @click="temptime ? updateTime(temptime) : timemenu=false" color="warning" :text="$t('global.ok')"></material-button>
                </v-col>
            </v-row>
        </v-time-picker>
    </v-menu>
</template>

<script>
export default {
    data: () => ({
        timemenu:false,
        innerValue: null,
        allowedMinutes: [
            0, 15, 30, 45
        ],
        nochangeemitted: false,
        temptime:null
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
        onlytime: {
            type: Boolean,
            default: false
        },
        dense: {
            type: Boolean,
            default: false
        },
    },
    methods: {
        updateTime(inputtime){
            if (this.onlytime){
                let minutes = moment(inputtime, 'HH:mm').format('mm')
                minutes= (Math.round(minutes/15) ) * 15
                let hours = moment(inputtime, 'HH:mm').format('HH')
                let newtime = moment(hours +':' + minutes, "HH:mm")
                if (newtime.isValid()){
                    this.innerValue=newtime.format('HH:mm')
                }
                else {
                    this.innerValue = null
                }
            }
            else {
                let date = moment(this.innerValue).format('YYYY-MM-DD')
                let minutes = moment(inputtime, 'HH:mm').format('mm')
                minutes= (Math.round(minutes/15) ) * 15
                let hours = moment(inputtime, 'HH:mm').format('HH')
                let newtime = moment(date + ' ' + hours +':' + minutes, "YYYY-MM-DD HH:mm")
                if (newtime.isValid()){
                    this.innerValue=newtime.format('YYYY-MM-DD HH:mm:ss')
                }
                else {
                    if (moment(date).isValid()){
                        this.innerValue = date
                    }
                    else {
                        this.innerValue=null
                    }   
                }
            }
            this.timemenu=false
        },
    },
    computed: {
        displayedTime(){
            if (this.innerValue){
                if (this.onlytime){
                    return this.innerValue
                }
                return this.$formatters.backEndTimeWithoutDate(this.innerValue)
            }
            return null
        }
    },
    watch: {
        innerValue(newVal) {
            this.$emit("input", newVal);
            this.$nextTick(() => {
                if (!this.nochangeemitted && newVal != this.value){
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
    },
}
</script>
