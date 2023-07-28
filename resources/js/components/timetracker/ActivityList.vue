<template>
    <main-container>
        <material-card icon="fas fa-clock" :title="$t('timetracker.list_title')" :main_action="main_action" @mainAction="createStaff" noduplicate :selected="selected_staff" @unselectAll="selected_staff=[]">
            <template slot="selection-actions">

            </template>
            <template slot="filters">
                <v-col cols="12" xs="12" sm="3">
                    <ValidationProvider ref="all_employees" vid='all_employees' :name="$t('staff.all_employees')">
                        <v-checkbox hide-details :label="$t('staff.all_employees')" v-model="staff_options.all" ></v-checkbox>
                    </ValidationProvider>
                </v-col>
                <v-col cols="12" xs="12" sm="5">
                    <search-box v-model="staff_options.search" nocolor @change="staff_options.page=1"></search-box>
                </v-col>
            </template>
            <material-table @click-action="editStaff" :selected.sync="selected_staff" show-select :headers="staff_headers" :items="staff.data" :options.sync="staff_options" :server-items-length="staff.total" :loading="staff_loading">
                <template v-slot:item.birthday="{ item }">{{$formatters.frontEndDateFormat(item.birthday)}}</template>
                <template v-slot:item.role_id="{ item }">{{item.role_name}}</template>
                <template v-slot:item.type_id="{ item }">{{item.type_name}}</template>
            </material-table>
            <create-edit-staff v-if="showCreateEditStaff"></create-edit-staff>
        </material-card>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        originalstaff_options: null,
        selected_staff: [],
    }),
    computed: {
        main_action(){
            if (this.$hasRole('Admin')){
                return {
                    icon: 'fas fa-plus',
                    text: this.$t('staff.mainaction')
                }
            }
        },
        staff_options: {
            get: function () {
                return this.$store.getters.getStaffOptions;
            },
            set: function (value) {
                this.$store.commit('setStaffOptions', value);
            },
        },
        staff_headers() {
            let headers = []
            headers.push({
                text: this.$t('staff.code'),
                value: 'code',
                align: 'left',
                sortable: true,
                selected: false
            })
            headers.push({
                text: this.$t('staff.surname'),
                value: 'surname',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.name'),
                value: 'name',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.taxcode'),
                value: 'taxcode',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.address'),
                value: 'address',
                align: 'left',
                sortable: true,
                selected: false
            })
            headers.push({
                text: this.$t('staff.city'),
                value: 'city',
                align: 'left',
                sortable: true,
                selected: false
            })
            headers.push({
                text: this.$t('staff.postcode'),
                value: 'postcode',
                align: 'left',
                sortable: true,
                selected: false
            })
            headers.push({
                text: this.$t('staff.phone_number'),
                value: 'phone_number',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.private_email'),
                value: 'private_email',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.gender'),
                value: 'gender',
                align: 'left',
                sortable: true,
                selected: false
            })
            headers.push({
                text: this.$t('staff.birthday'),
                value: 'birthday',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.role_id'),
                value: 'role_id',
                align: 'left',
                sortable: true,
                selected: true
            })
            headers.push({
                text: this.$t('staff.type_id'),
                value: 'type_id',
                align: 'left',
                sortable: true,
                selected: true
            })
            return {data: headers, id: 'staff_headers'}
        },
        staff_loading() {
            return this.$store.getters.staff_loading;
        },
        staff() {
            return this.$store.getters.getStaff;
        },
        showCreateEditStaff(){
            return this.$store.getters.showCreateEditStaff
        }
    },
    methods: {
        createStaff(){
            let element={
                date_start: moment().format('YYYY-MM-DD'),
                role_id: 1,
                type_id: 1,
                gender: 'M',
                morning_starttime: '09:00',
                morning_endtime: '13:00',
                afternoon_starttime: '14:00',
                afternoon_endtime: '18:00'
            }
            this.$store.dispatch('setCurrentModelForAttachment',{
                object: element,
                filecategory: 'staff'
            })
            this.$store.dispatch('showAction','showCreateEditStaff')
        },
        editStaff(item){
            let cloned =_.cloneDeep(item)
            if (!cloned.morning_starttime){
                cloned.morning_starttime= '09:00',
                cloned.morning_endtime= '13:00',
                cloned.afternoon_starttime= '14:00',
                cloned.afternoon_endtime= '18:00'
            }
            this.$store.dispatch('setCurrentModelForAttachment',{
                object: _.cloneDeep(item),
                filecategory: 'staff'
            })
            this.$store.dispatch('showAction','showCreateEditStaff')
        },
        loadStaff() {
            if (!this.$functions.ObjectAreEqual(this.originalstaff_options, this.staff_options)) {
                this.originalstaff_options = _.cloneDeep(this.staff_options)
                this.$store.dispatch('fetchStaff');
            }
        },
    },
    watch: {
        staff_options: {
            handler() {
                this.loadStaff()
            },
            deep: true,
        },
    }
}
</script>
