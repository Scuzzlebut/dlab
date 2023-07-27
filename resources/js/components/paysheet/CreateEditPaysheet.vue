<template>
    <create-edit-layout v-if="showCreateEditPaysheet && loaded" onlyclose element="Paysheet" icon="fas fa-umbrella-beach" @duplicatecomplete="$emit('duplicatecomplete')">
        <div slot="instructions" v-if="$can('paysheet-others')">
            <span v-if="currentPaysheet.id">Dettaglio cedolino di <strong v-if="currentPaysheet.staff">{{currentPaysheet.staff.fullname}}</strong></span>
        </div>
        <v-col cols="12" xs="12" align="right">
            <attachments-badge element="Paysheet" class="ml-2"></attachments-badge>
        </v-col>
        <v-col cols="12" xs="12" :sm="$can('paysheet-others') ? 6 : 12">
            <content-value :name="$t('paysheet.reference_year')" :value="currentPaysheet.reference_year"></content-value>
            <content-value :name="$t('paysheet.reference_month')" :value="$appOptions.paysheetMonths(currentPaysheet.reference_month)"></content-value>
        </v-col>
        <v-col cols="12" xs="12" sm="6" v-if="$can('paysheet-others')">
            <content-value  :name="$t('paysheet.staff_id')" :value="currentPaysheet.staff.fullname"></content-value>
            <content-value  :name="$t('paysheet.created_by')" :value="currentPaysheet.creator.name"></content-value>
        </v-col>
    </create-edit-layout>
</template>

<script>
export default {
    data: () => ({
        loaded: false
    }),
    computed: {
        currentPaysheet: {
            get: function () {
                return this.$store.getters.getCurrentPaysheet;
            },
            set: function (value) {
                this.$store.commit('setCurrentPaysheet', value)
            },
        },
        showCreateEditPaysheet() {
            return this.$store.getters.showCreateEditPaysheet;
        },
        paysheet_loading(){
            return this.$store.getters.paysheet_loading
        },
    },
    created(){
        if (this.currentPaysheet.id){
            this.$store.dispatch('showPaysheetDetails').then((res)=>{
                this.loaded=true
            })
        }
        else {
            this.loaded=true
        }
    },
}
</script>
