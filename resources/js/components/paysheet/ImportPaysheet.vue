<template>
    <material-popup :dialogShowModel="showImportPaysheet" @escintent="showImportPaysheet=false">
        <ValidationObserver ref="paysheetobserver" tag="div" v-slot="{ failed }">
            <material-card  :fullscreenadapter="$vuetify.breakpoint.xsOnly" icon="fas fa-file-import" :title="$t('paysheet.import_label')" dismissable @dismissed="showImportPaysheet=false">
                <div slot="after-heading">
                    {{$t('paysheet.import_helper')}}
                </div>
                <upload-file @fileuploaded="importPaysheetRead" :types="['pdf']" :uploading="importpaysheet_loading" v-if="!readedPaysheetImport"></upload-file>
                <v-row v-else-if="currentDisplayPaysheet">
                    <v-col cols="12" xs="12" sm="6" class="pb-2">
                        <image-zoom v-if="currentDisplayPaysheet.thumbnail_link" :src="currentDisplayPaysheet.thumbnail_link" @click="$functions.openLink(currentDisplayPaysheet.link)"></image-zoom>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6">
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('paysheet.year')}}:</strong>{{currentDisplayPaysheet.year}}</span></div>
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('paysheet.month_nr')}}:</strong>{{currentMonth}}</span></div>
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('paysheet.code')}}:</strong>{{currentRelatedStaff}}</span></div>
                        <v-row dense>
                            <v-col cols="12" xs="12" align="right">
                                <material-button small class="mr-2" color="warning" @click="showUpdatePaysheet()" outlined :text="$t('global.edit')"><v-icon small class="pl-1">far fa-edit</v-icon></material-button>
                                <material-button small @click="deletePaysheet()" outlined :text="$vuetify.breakpoint.xsOnly ? null : $t('global.delete')" :disabled="importpaysheet_loading"><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                            </v-col>
                        </v-row>
                        <instant-edit v-model="single_paysheet_edit" icon="fas fa-paperclip" title="Modifica">
                            <v-row justify="center" dense>
                                <v-col cols="12" xs="12">
                                    <ValidationProvider ref="year" vid='year' :name="$t('paysheet.year')" rules="required" v-slot="{ errors,field }">
                                        <v-text-field :label="$t('global.year')" v-model="currentPaysheet.year" :error-messages="errors" type="number" v-mask="'XXXX'"></v-text-field>
                                    </ValidationProvider>
                                </v-col>
                                <v-col cols="12" xs="12">
                                    <ValidationProvider ref="month_nr" vid='month_nr' :name="$t('paysheet.month_nr')" rules="required" v-slot="{ errors,field }">
                                        <v-select :label="$t('global.month')" :items="$appOptions.paysheetMonths()" v-model="currentPaysheet.month_nr" :error-messages="errors" item-text="label" item-value="value"></v-select>
                                    </ValidationProvider>
                                </v-col>
                                <v-col cols="12" xs="12">
                                    <ValidationProvider ref="code" vid='code' :name="$t('paysheet.code')" rules="required" v-slot="{ errors,field }">
                                        <v-autocomplete v-model="currentPaysheet.code" :loading="relatedstaff_loading" :label="$t('paysheet.code')" :items="relatedstaff" item-text="fullname" item-value="code" menu-props="closeOnClick, overflowY">
                                        </v-autocomplete>
                                    </ValidationProvider>
                                </v-col>
                            </v-row>
                            <template slot="instantactions">
                                <v-col cols="6" xs="6" sm="4" align="right">
                                    <material-button large @click="single_paysheet_edit=false" outlined :text="$t('global.undo')"></material-button>
                                </v-col>
                                <v-col cols="6" xs="6" sm="4" align="right">
                                    <material-button large @click="updatePaysheet()" color="success" :text="$t('global.ok')" :disabled="importpaysheet_loading" :loading="importpaysheet_loading"></material-button>
                                </v-col>
                            </template>
                        </instant-edit>
                    </v-col>
                </v-row>
                <template slot="actions" v-if="readedPaysheetImport">
                    <v-col cols="12" align="center" v-if="currentDisplayPaysheet">
                        <v-pagination v-model="current_paysheet_index" :length="readedPaysheetImport.length"></v-pagination>
                    </v-col>
                    <v-col cols="6" xs="6" sm="3">
                        <material-button large @click="showImportPaysheet=false" outlined :text="$t('global.undo')"></material-button>
                    </v-col>
                    <v-col cols="6" xs="6" sm="3">
                        <material-button large @click="importPaysheet()" color="success" :text="$t('global.save')"></material-button>
                    </v-col>
                </template>
            </material-card>
        </ValidationObserver>
    </material-popup>
</template>

<script>
export default {
    data: () => ({
        formData: null,
        single_paysheet_edit: false,
        current_paysheet_index: 1,
        currentPaysheet: {}
    }),
    computed: {
        importpaysheet_loading(){
            return this.$store.getters.importpaysheet_loading;
        },
        readedPaysheetImport: {
            get: function(){
                return this.$store.getters.getReadedPaysheetImport
            },
            set: function(value){
                this.$store.commit('setReadedPaysheetImport',value)
            }
        },
        currentDisplayPaysheet(){
            if (this.readedPaysheetImport){
                return this.readedPaysheetImport[this.current_paysheet_index - 1]
            }
            return null
        },
        showImportPaysheet: {
            get: function () {
                return this.$store.getters.showImportPaysheet;
            },
            set: function (value) {
                if (value == false) {
                    this.$store.dispatch('hideAction','hideImportPaysheet');
                }
            },
        },
        relatedstaff() {
            return this.$appOptions.relatedStaff()
        },
        relatedstaff_loading() {
            return this.$store.getters.relatedstaff_loading;
        },
        currentRelatedStaff(){
            if (this.currentDisplayPaysheet){
                if (this.currentDisplayPaysheet.code){
                    return this.relatedstaff.find(obj => obj.code==this.currentDisplayPaysheet.code)?.fullname?? null
                }
            }
            return null
        },
        currentMonth(){
            if (this.currentDisplayPaysheet){
                if (this.currentDisplayPaysheet.month_nr){
                    return this.$appOptions.paysheetMonths(this.currentDisplayPaysheet.month_nr)
                }
            }
            return null
        }
    },
    methods: {
        importPaysheetRead(files){
            const self=this
            this.formData = new FormData();
            this.formData.append('attachment', files[0]);
            this.$store.dispatch('importPaysheetRead',this.formData).then((res) =>{
                self.current_paysheet_index=1
            })
        },
        importPaysheet(){
            this.$store.dispatch('importPaysheetStore')
        },
        showUpdatePaysheet(){
            this.currentPaysheet = _.cloneDeep(this.currentDisplayPaysheet)
            this.single_paysheet_edit=true
        },
        updatePaysheet(){
            const self=this
            this.$refs.paysheetobserver.validate().then((isValid) => {
                if (isValid == true) {
                    self.readedPaysheetImport[self.current_paysheet_index - 1] = _.cloneDeep(self.currentPaysheet)
                    self.readedPaysheetImport = JSON.parse(JSON.stringify(self.readedPaysheetImport))
                    self.single_paysheet_edit=false
                }
            })
        },
        deletePaysheet(){
            const self=this
            let message = self.$t('paysheet.delete_paysheet_import_message')
            self.$store.commit('showDialog', {
                type: "confirm",
                title: self.$t('paysheet.delete_paysheet_import_title'),
                message:  message,
                okCb: () => {
                    self.readedPaysheetImport.splice(self.current_paysheet_index-1,1)
                    self.current_paysheet_index=1
                    if (self.readedPaysheetImport.length==0){
                        self.$store.commit('showSnackbar', {
                            message: self.$t('paysheet.import_aborted'),
                            color: 'warning',
                            duration: 3000
                        })
                        self.showImportPaysheet=false
                    }
                },
            });
        },
    },
}
</script>
