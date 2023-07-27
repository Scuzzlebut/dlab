<template>
    <main-container>
        <material-card icon="fas fa-sack-dollar" :title="$t('paysheet.list_title')" :main_action="main_action" @mainAction="createPaysheet" noduplicate :selected="selected_paysheet" @unselectAll="selected_paysheet=[]">
            <template slot="selection-actions">
                <v-btn class="ma-1 mr-0" v-if="allSelectedCanBeDeleted" outlined @click="deletePaysheet()" small :disabled="paysheet_loading" :loading="paysheet_deleting">{{$t('global.delete')}}<v-icon small class="pl-1">fas fa-trash-alt</v-icon></v-btn>
            </template>
            <template slot="filters">
                <v-col cols="6" xs="6" sm="2">
                    <v-select hide-details :label="$t('global.year')" :items="$appOptions.years()" v-model="paysheet_options.year" clearable @change="paysheet_options.page=1"></v-select>
                </v-col>
                <v-col cols="6" xs="6" sm="2">
                    <v-select hide-details :label="$t('global.month')" :items="$appOptions.months()" v-model="paysheet_options.month" clearable item-text="label" item-value="value" :disabled="!paysheet_options.year" @change="paysheet_options.page=1"></v-select>
                </v-col>
                <v-col cols="12" xs="12" sm="4" v-if="$can('paysheet-others')">
                    <related-staff v-model="paysheet_options.staff_ids"  multiple @change="paysheet_options.page=1"></related-staff>
                </v-col>
                <v-col cols="12" xs="12" sm="4">
                    <search-box v-model="paysheet_options.search" nocolor @change="paysheet_options.page=1"></search-box>
                </v-col>
            </template>
            <material-table @click-action="editPaysheet" :selected.sync="selected_paysheet" :show-select="$can('paysheet-others')" :headers="paysheet_headers" :items="paysheet.data" :options.sync="paysheet_options" :server-items-length="paysheet.total" :loading="paysheet_loading">
                <template v-slot:item.created_at="{ item }">{{$formatters.formatDateTime(item.created_at)}}</template>
                <template v-slot:item.reference_month="{item}">{{ $appOptions.paysheetMonths(item.reference_month) }}</template>
                <template v-slot:item.downloaded_at="{item}">
                    <v-icon color="success" v-if="item.downloaded_at">fas fa-check</v-icon>
                    <template v-else>
                        <v-icon color="error" v-if="$can('paysheet-others') && (item.staff_id != user.id)">fas fa-times</v-icon>
                        <material-button v-else color="warning" small @click="downloadPaysheet(item)" :disabled="downloading_id!=null" :loading="downloading_id==item.id" :text="$t('global.download')"><v-icon small class="pl-1">fas fa-download</v-icon></material-button>
                    </template>
                </template>
                <template v-slot:item.actions="{item}">
                    <v-btn v-if="item.downloaded_at || ($can('paysheet-others') && (item.staff_id != user.id))" icon small @click.stop="viewPaysheet(item)" color="primary"><v-icon>fas fa-file-pdf</v-icon></v-btn>
                </template>
            </material-table>
            <create-edit-paysheet v-if="showCreateEditPaysheet"></create-edit-paysheet>
            <import-paysheet v-if="showImportPaysheet"></import-paysheet>
        </material-card>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        originalpaysheet_options: null,
        selected_paysheet: [],
        paysheet_deleting: false,
        downloading_id: null
    }),
    computed: {
        main_action(){
            if (this.$can('paysheet-others')){
                return {
                    icon: 'fas fa-plus',
                    text: this.$t('paysheet.mainaction')
                }
            }
        },
        paysheet_options: {
            get: function () {
                return this.$store.getters.getPaysheetOptions;
            },
            set: function (value) {
                this.$store.commit('setPaysheetOptions', value);
            },
        },
        paysheet_headers() {
            let headers = []
            if (this.$can('paysheet-others')){
                headers.push({
                    text: this.$t('paysheet.staff_id'),
                    value: 'staff.fullname',
                    align: 'left',
                    sortable: true,
                    selected: true
                })
            }
            headers.push({
                text: this.$t('paysheet.reference_year'),
                value: 'reference_year',
                align: 'left',
                sortable: true,
                selected: true,
                width: "1%"
            })
            headers.push({
                text: this.$t('paysheet.reference_month'),
                value: 'reference_month',
                align: 'left',
                sortable: true,
                selected: true,
                width: "1%"
            })
            headers.push({
                text: this.$t('paysheet.created_at'),
                value: 'created_at',
                align: 'left',
                sortable: true,
                selected: true,
                width: '1%'
            })
            headers.push({
                text: this.$t('paysheet.downloaded_at'),
                value: 'downloaded_at',
                align: 'center',
                sortable: true,
                selected: true,
                width: '1%'
            })
            headers.push({
                text: '',
                selectiontext: this.$t('global.pdf'),
                value: 'actions',
                align: 'center',
                sortable: false,
                width: '1%'
            })
            return {data: headers, id: 'paysheet_headers'}
        },
        paysheet_loading() {
            return this.$store.getters.paysheet_loading;
        },
        paysheet() {
            return this.$store.getters.getPaysheet;
        },
        allSelectedCanBeDeleted(){
            let status=true
            for (let element of this.selected_paysheet) {
                if (element.accepted){
                    status= false
                    break;
                }
            }
            return status
        },
        showCreateEditPaysheet(){
            return this.$store.getters.showCreateEditPaysheet
        },
        showImportPaysheet(){
            return this.$store.getters.showImportPaysheet
        },
        user(){
            return this.$store.getters.getUser;
        },
    },
    methods: {
        createPaysheet(){
            this.$store.dispatch('showAction','showImportPaysheet')
        },
        editPaysheet(item){
            this.$store.dispatch('setCurrentModelForAttachment',{
                object: _.cloneDeep(item),
                filecategory: 'paysheet'
            })
            this.$store.dispatch('showAction','showCreateEditPaysheet')
        },
        deletePaysheet(){
            const self=this
            let message = self.$t('global.delete_confirm_message_pre') 
            if (self.selected_paysheet.length<=1){
                message+= self.$t('paysheet.question_selected') 
            }
            else {
                message+= self.selected_paysheet.length + self.$t('paysheet.question_multiple_selected') 
            }
            self.$store.commit('showDialog', {
                type: "confirm",
                title: self.$t('global.delete_confirm_title'),
                message:  message,
                okCb: () => {
                    self.selected_paysheet.forEach(element => {
                        self.$store.dispatch('deletePaysheet',element.id).then((res) => {
                            self.$functions.actionOnSelection(res,self.selected_paysheet)
                        })
                    });
                },
            });
        },
        loadPaysheet() {
            if (!this.$functions.ObjectAreEqual(this.originalpaysheet_options, this.paysheet_options)) {
                this.originalpaysheet_options = _.cloneDeep(this.paysheet_options)
                this.$store.dispatch('fetchPaysheet');
            }
        },
        downloadPaysheet(item){
            if (item.attachments){
                this.downloading_id = item.id
                this.$functions.openLink(item.attachments[0].download_link)
                if (!item.downloaded_at){
                    this.$store.dispatch('setPaysheetAsDownloaded',item.id).then(()=>{
                        this.downloading_id=null
                    }).catch(()=>{
                        this.downloading_id=null
                    })
                }
            }   
        },
        viewPaysheet(item){
            if (item.attachments){
                this.$functions.openLink(item.attachments[0].link)
            }   
        }
    },
    watch: {
        paysheet_options: {
            handler() {
                this.loadPaysheet()
            },
            deep: true,
        },
    },
    mounted() {
        if (this.paysheet_options.year == null) {
            this.paysheet_options.year = parseInt(moment().format('Y'))
        }
    },
}
</script>
