<template>
    <create-edit-layout v-if="showCreateEditCommunication && loaded" element="Communication" icon="fas fa-bell" @duplicatecomplete="$emit('duplicatecomplete')">
        <template v-if="currentCommunication.id && !editactivated">
            <v-col cols="12" xs="12" align="right" class="py-0">
                <attachments-badge element="Communication"></attachments-badge>
                <template v-if="$can('communication-management')">
                    <material-button small class="ml-1" color="warning" @click="editactivated=true" outlined :text="$t('global.edit')"><v-icon small class="pl-1">far fa-edit</v-icon></material-button>
                    <material-button class="ml-1" outlined @click="deleteCommunication()" small :disabled="communication_loading" :loading="self_deleting"><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                </template>
            </v-col>
            <v-col cols="12" xs="12" class="communication px-4">
                <div class="previewcontent">
                    <div class="communicationheader d-flex flex-wrap">
                        <v-icon small class="pr-1">far fa-clock</v-icon>
                        <span v-html="humanizeCreationTime(currentCommunication.created_at)"></span>
                        <v-spacer></v-spacer>
                    </div>
                    <div class="h5 font-weight-bold" v-html="currentCommunication.title"></div>
                    <div class="communicationcontentcomplete font-italic"><span v-html="currentCommunication.body"></span></div>
                    <div class="d-flex flex-wrap mt-2">
                        <v-icon small class="pr-1">far fa-user</v-icon>
                        <span class="font-weight-bold" v-html="currentCommunication.creator_name"></span>
                    </div>
                </div>
            </v-col>
        </template>
        <template v-else>
            <v-col cols="12" xs="12" align="right" class="py-0">
                <attachments-badge element="Communication"></attachments-badge>
            </v-col>
            <v-col cols="12" xs="12" sm="8">
                <ValidationProvider vid='title' :name="$t('communication.title')" rules="" v-slot="{ errors,field }">
                    <v-text-field :label="$t('communication.title')" v-model="currentCommunication.title" :error-messages="errors">
                    </v-text-field>
                </ValidationProvider>
            </v-col>
            <v-col cols="12" xs="12" sm="4">
                <ValidationProvider ref='roleIds' vid='roleIds' :name="$t('communication.roleIds')" rules="required" v-slot="{ errors,field }">
                    <v-select multiple :label="$t('communication.roleIds')" v-bind:items="$appOptions.staffRoles()" v-model="currentCommunication.roleIds" item-text="role_name" item-value="id" small-chips :error-messages="errors">
                    </v-select>
                </ValidationProvider>
            </v-col>
            <v-col cols="12" xs="12">
                <ValidationProvider ref="body" vid='body' :name="$t('communication.body')" rules="required" v-slot="{ errors,field }">
                    <html-editor toolbaralwaysvisible v-model="currentCommunication.body" vid='body' :name="$t('communication.body')" :errors="errors" :disable="['mention','check','pagebreak','table','divider']"></html-editor>
                </ValidationProvider>
            </v-col>
        </template>
    </create-edit-layout>
</template>

<script>
export default {
    data: () => ({
        self_loading: false,
        self_deleting: false,
        loaded: false,
        editactivated: false
    }),
    computed: {
        showCreateEditCommunication(){
            return this.$store.getters.showCreateEditCommunication
        },
        currentCommunication: {
            get: function () {
                return this.$store.getters.getCurrentCommunication;
            },
            set: function (value) {
                this.$store.commit('setCurrentCommunication', value)
            },
        },
        alltenantuser(){
            return this.$appOptions.allTenantUser()
        },
        communication_loading(){
            return this.$store.getters.communication_loading
        }
    },
    methods: {
        humanizeCreationTime(time){
            return moment(time).fromNow()
        },
        deleteCommunication(){
            this.self_deleting=true
            const self=this
            this.$store.dispatch('deleteCommunication', this.currentCommunication.id).then((res)=>{
                self.self_deleting=false
            }).catch((err)=>{
                self.self_deleting=false
            });
        },
    },
    created(){
        if (this.currentCommunication.id){
            this.$store.dispatch('showCommunicationDetails').then((res)=>{
                this.loaded=true
            })
        }
        else {
            this.loaded=true
        }
    },
}
</script>
