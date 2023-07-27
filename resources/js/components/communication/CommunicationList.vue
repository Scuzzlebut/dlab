<template>
    <main-container>
        <material-card icon="fas fa-bell" :title="$t('communication.pagetitle')" class="mt-9 v-card--wizard" :main_action="main_action" @mainAction="createCommunication">
            <v-tabs v-model="current_tab" :class="{'wizardslider' : !$vuetify.breakpoint.xsOnly}" hide-slider grow active-class="primary white--text" :center-active="$vuetify.breakpoint.xsOnly" :show-arrows="$vuetify.breakpoint.xsOnly">
                <v-tab  v-for="tab in steps" :key="tab.id" :id="tab.tourid">
                    {{tab.name}}
                </v-tab>
            </v-tabs>
            <v-divider class="my-0"></v-divider>
            <v-tabs-items v-model="current_tab" :touchless="$vuetify.breakpoint.xsOnly">
                <v-tab-item v-for="tab in steps"  :key="tab.id">
                    <template v-if="tab.id==0">
                        <div class="communication_container">
                            <div v-for="(item,index) in communicationtotal.data" :key="item.id" class="communication_wrapper">
                                <div class="communication clickarea" @click="showCommunicationDetails(item)">
                                    <div class="previewcontent" v-if="$vuetify.breakpoint.xsOnly">
                                        <div class="communicationheader d-flex flex-wrap">
                                            <v-icon small class="pr-1">far fa-clock</v-icon>
                                            <span v-html="humanizeCreationTime(item.created_at)"></span>
                                            <v-spacer></v-spacer>
                                            <material-button outlined @click="deleteCommunication(item)" small :disabled="communication_loading" :loading="communication_loading && deleting_id==item.id"><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                                        </div>
                                        <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                        <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                        <div class="d-flex flex-wrap mt-2">
                                            <v-icon small class="pr-1">far fa-user</v-icon>
                                            <span class="font-weight-bold" v-html="item.creator_name"></span>
                                        </div>
                                    </div>
                                    <div class="previewcontent" v-else>
                                        <v-row align="center">
                                            <v-col cols="1" align="center">
                                                <v-icon large :color="communicationColor(item)">{{communicationIcon(item)}}</v-icon>
                                            </v-col>
                                            <v-col cols="8">
                                                <div class="communicationheader">
                                                    <v-icon small class="pr-1">far fa-clock</v-icon>
                                                    <span v-html="humanizeCreationTime(item.created_at)"></span>
                                                </div>
                                                <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                                <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                                <div class="d-flex flex-wrap mt-2">
                                                    <v-icon small class="pr-1">far fa-user</v-icon>
                                                    <span class="font-weight-bold" v-html="item.creator_name"></span>
                                                </div>
                                            </v-col>
                                            <v-col cols="3" align="right">
                                                <material-button outlined color="maingrey" @click="deleteCommunication(item)" small :disabled="communication_loading" :loading="communication_loading && deleting_id==item.id"><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </div>
                            </div>
                            <v-row justify="center" class="my-10" v-if="communicationtotal.data.length<communicationtotal.total">
                                <v-progress-circular size="24" class="ma-1 text--disabled" width="2" indeterminate v-intersect="loadMoreCommunicationTotal"></v-progress-circular>
                            </v-row>
                            <div class="my-10" v-if="communicationtotal.total==0">
                                <v-row justify="center">
                                    <v-col cols="12" align="center">
                                        <v-icon size="70" color="success">far fa-thumbs-up</v-icon>
                                    </v-col>
                                    <v-col cols="12" align="center">
                                        <div class="text-h3 font-weight-bold success--text" v-html="$t('communication.everything_ok')"></div>
                                        <div class="text-h6" v-html="$t('communication.everything_ok_message')"></div>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </template>
                    <template v-if="tab.id==1">
                        <div class="communication_container">
                            <div v-for="(item,index) in communication.data" :key="item.id" class="communication_wrapper">
                                <div class="communication clickarea" @click="showCommunicationDetails(item)">
                                    <div class="previewcontent" v-if="$vuetify.breakpoint.xsOnly">
                                        <div class="communicationheader d-flex flex-wrap">
                                            <v-icon small class="pr-1">far fa-clock</v-icon>
                                            <span v-html="humanizeCreationTime(item.created_at)"></span>
                                            <v-spacer></v-spacer>
                                            <material-button outlined color="maingrey" class="mr-1" @click="setCommunicationRead(item)" small :disabled="communication_loading" :loading="communication_loading && loading_id==item.id">{{$t('communication.archive')}}<v-icon small class="pl-1">fas fa-box-archive</v-icon></material-button>
                                        </div>
                                        <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                        <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                        <div class="d-flex flex-wrap mt-2">
                                            <v-icon small class="pr-1">far fa-user</v-icon>
                                            <span class="font-weight-bold" v-html="item.creator_name"></span>
                                        </div>
                                    </div>
                                    <div class="previewcontent" v-else>
                                        <v-row align="center">
                                            <v-col cols="1" align="center">
                                                <v-icon large :color="communicationColor(item)">{{communicationIcon(item)}}</v-icon>
                                            </v-col>
                                            <v-col cols="8">
                                                <div class="communicationheader">
                                                    <v-icon small class="pr-1">far fa-clock</v-icon>
                                                    <span v-html="humanizeCreationTime(item.created_at)"></span>
                                                </div>
                                                <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                                <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                                <div class="d-flex flex-wrap mt-2">
                                                    <v-icon small class="pr-1">far fa-user</v-icon>
                                                    <span class="font-weight-bold" v-html="item.creator_name"></span>
                                                </div>
                                            </v-col>
                                            <v-col cols="3" align="right">
                                                <material-button outlined color="maingrey" class="mr-1" @click="setCommunicationRead(item)" small :disabled="communication_loading" :loading="communication_loading && loading_id==item.id">{{$t('communication.archive')}}<v-icon small class="pl-1">fas fa-box-archive</v-icon></material-button>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </div>
                            </div>
                            <v-row justify="center" class="my-10" v-if="communication.data.length<communication.total">
                                <v-progress-circular size="24" class="ma-1 text--disabled" width="2" indeterminate v-intersect="loadMoreCommunication"></v-progress-circular>
                            </v-row>
                            <div class="my-10" v-if="communication.total==0">
                                <v-row justify="center">
                                    <v-col cols="12" align="center">
                                        <v-icon size="70" color="success">far fa-thumbs-up</v-icon>
                                    </v-col>
                                    <v-col cols="12" align="center">
                                        <div class="text-h3 font-weight-bold success--text" v-html="$t('communication.everything_ok')"></div>
                                        <div class="text-h6" v-html="$t('communication.everything_ok_message')"></div>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </template>
                    <template v-if="tab.id==2">
                        <div class="communication_container">
                            <div v-for="(item,index) in communicationarchived.data" :key="item.id" class="communication_wrapper">
                                <div class="communication">
                                    <div class="previewcontent" v-if="$vuetify.breakpoint.xsOnly">
                                        <div class="communicationheader d-flex flex-wrap">
                                            <v-icon small class="pr-1">far fa-clock</v-icon>
                                            <span v-html="humanizeCreationTime(item.created_at)"></span>
                                            <v-spacer></v-spacer>
                                            <material-button outlined color="maingrey" class="mr-1" @click="setCommunicationNotRead(item)" small :disabled="communication_loading" :loading="communication_loading && loading_id==item.id">{{$t('communication.extract_from_archive')}}<v-icon small class="pl-1">fas fa-rotate-left</v-icon></material-button>
                                        </div>
                                        <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                        <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                        <div class="d-flex flex-wrap mt-2">
                                            <v-icon small class="pr-1">far fa-user</v-icon>
                                            <span class="font-weight-bold" v-html="item.creator_name"></span>
                                        </div>
                                    </div>
                                    <div class="previewcontent" v-else>
                                        <v-row align="center">
                                            <v-col cols="1" align="center">
                                                <v-icon large :color="communicationColor(item)">{{communicationIcon(item)}}</v-icon>
                                            </v-col>
                                            <v-col cols="8">
                                                <div class="communicationheader">
                                                    <v-icon small class="pr-1">far fa-clock</v-icon>
                                                    <span v-html="humanizeCreationTime(item.created_at)"></span>
                                                </div>
                                                <div class="text-h5 font-weight-bold" v-html="item.title"></div>
                                                <div class="communicationcontent font-italic"><span v-html="item.body"></span></div>
                                                <div class="d-flex flex-wrap mt-2">
                                                    <v-icon small class="pr-1">far fa-user</v-icon>
                                                    <span class="font-weight-bold" v-html="item.creator_name"></span>
                                                </div>
                                            </v-col>
                                            <v-col cols="3" align="right">
                                                <material-button outlined color="maingrey" class="mr-1" @click="setCommunicationNotRead(item)" small :disabled="communication_loading" :loading="communication_loading && loading_id==item.id">{{$t('communication.extract_from_archive')}}<v-icon small class="pl-1">fas fa-rotate-left</v-icon></material-button>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </div>
                            </div>
                            <v-row justify="center" class="my-10" v-if="communicationarchived.data.length<communicationarchived.total">
                                <v-progress-circular size="24" class="ma-1 text--disabled" width="2" indeterminate v-intersect="loadMoreCommunicationArchived"></v-progress-circular>
                            </v-row>
                            <div class="my-10" v-if="communicationarchived.total==0">
                                <v-row justify="center">
                                    <v-col cols="12" align="center">
                                        <v-icon size="70" color="success">far fa-thumbs-up</v-icon>
                                    </v-col>
                                    <v-col cols="12" align="center">
                                        <div class="text-h3 font-weight-bold success--text" v-html="$t('communication.everything_ok')"></div>
                                        <div class="text-h6" v-html="$t('communication.everything_ok_message')"></div>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </template>
                </v-tab-item>
            </v-tabs-items>
            <create-edit-communication v-if="showCreateEditCommunication" @duplicatecomplete="reloadCurrentList"></create-edit-communication>
        </material-card>
    </main-container>
</template>

<script>
export default {
    data: () => ({
        loading_id: null,
        deleting_id: null,
        current_tab: null,
        max: 200
    }),
    computed: {
        steps(){
            if (this.$can('communication-management')){
                return [
                    {id:0, name: this.$t('communication.total_title')},
                    {id:1, name: this.$t('communication.toread_title')},
                    {id:2, name: this.$t('communication.archive_title')}
                ]
            }
            return [
                {id:1, name: this.$t('communication.toread_title')},
                {id:2, name: this.$t('communication.archive_title')}
            ]
        },
        communication(){
            let communication = this.$store.getters.getCommunication
            communication.data = _.orderBy(communication.data,'created_at','desc')
            return communication
        },
        communicationarchived(){
            let communication = this.$store.getters.getCommunicationArchived
            communication.data = _.orderBy(communication.data,'created_at','desc')
            return communication
        },
        communicationtotal(){
            let communication = this.$store.getters.getCommunicationTotal
            communication.data = _.orderBy(communication.data,'created_at','desc')
            return communication
        },
        communication_loading(){
            return this.$store.getters.communication_loading;
        },
        communication_options: {
            get: function () {
                return this.$store.getters.getCommunicationOptions;
            },
            set: function (value) {
                this.$store.commit('setCommunicationOptions', value);
            },
        },
        main_action(){
            if (this.$can('communication-management')){
                return {
                    icon: 'fas fa-plus',
                    text: this.$t('communication.mainaction')
                }
            }
            return null
        },
        showCreateEditCommunication(){
            return this.$store.getters.showCreateEditCommunication
        },
        currentCommunication(){
            return this.$store.getters.getCurrentCommunication
        }
    },
    methods: {
        showCommunicationDetails(item){
            this.$store.dispatch('setCurrentModelForAttachment',{
                object: _.cloneDeep(item),
                filecategory: 'communication'
            })
            this.$store.dispatch('showAction','showCreateEditCommunication')
        },
        communicationColor(item){
            return 'maingrey'
        },
        communicationIcon(item){
            return 'far fa-comment-dots'
        },
        reloadCurrentList(){
            switch (this.current_tab) {
                case 1:
                    this.communication_options.page=1
                    this.$store.dispatch('fetchCommunicationArchived')
                    break;
                default:
                    this.communication_options.page=1
                    this.$store.dispatch('fetchCommunication')
            }
        },
        humanizeCreationTime(time){
            return moment(time).fromNow()
        },
        setCommunicationRead(item){
            this.loading_id = item.id
            const self=this
            let params = {
                id: item.id,
                read: true
            }
            this.$store.dispatch('toggleArchiveCommunication',params).then((res)=>{
                self.loading_id=null
            }).catch((err)=>{
                self.loading_id=null
            });
        },
        setCommunicationNotRead(item){
            this.loading_id = item.id
            const self=this
            let params = {
                id: item.id,
                read: false
            }
            this.$store.dispatch('toggleArchiveCommunication',params).then((res)=>{
                self.loading_id=null
            }).catch((err)=>{
                self.loading_id=null
            });
        },
        deleteCommunication(item){
            this.deleting_id = item.id
            const self=this
            this.$store.dispatch('deleteCommunication', item.id).then((res)=>{
                self.deleting_id=null
            }).catch((err)=>{
                self.deleting_id=null
            });
        },
        loadMoreCommunication(entries, observer){
            if (entries[0].isIntersecting){
                if (this.communication.data.length<this.communication.total){
                    const self=this
                    if (!this.communication_loading){
                        setTimeout(function () {
                            self.communication_options.page=self.communication_options.page+1
                            self.$store.dispatch('fetchCommunication')
                        }, 100);
                    }
                    else {
                        setTimeout(function () {
                            self.loadMoreCommunication(entries, observer)
                        }, 500);
                    }
                }
            }
        },
        loadMoreCommunicationArchived(entries, observer){
            if (entries[0].isIntersecting){
                if (this.communicationarchived.data.length<this.communicationarchived.total){
                    const self=this
                    if (!this.communication_loading){
                        setTimeout(function () {
                            self.communication_options.page=self.communication_options.page+1
                            self.$store.dispatch('fetchCommunicationArchived')
                        }, 100);
                    }
                    else {
                        setTimeout(function () {
                            self.loadMoreCommunicationArchived(entries, observer)
                        }, 500);
                    }
                }
            }
        },
        loadMoreCommunicationTotal(entries, observer){
            if (entries[0].isIntersecting){
                if (this.communicationtotal.data.length<this.communicationtotal.total){
                    const self=this
                    if (!this.communication_loading){
                        setTimeout(function () {
                            self.communication_options.page=self.communication_options.page+1
                            self.$store.dispatch('fetchCommunicationTotal')
                        }, 100);
                    }
                    else {
                        setTimeout(function () {
                            self.loadMoreCommunicationTotal(entries, observer)
                        }, 500);
                    }
                }
            }
        },
        createCommunication(){
            let selected={
                title: null,
                body: null
            }
            this.$store.dispatch('setCurrentModelForAttachment',{
                object: selected,
                filecategory: 'communication'
            })
            this.$store.dispatch('showAction','showCreateEditCommunication')
        },
    },
    watch: {
        current_tab:{
            handler: function (val) {
                if (this.$can('communication-management')){
                    switch (val) {
                        case 0:
                            this.$store.commit('resetCommunicationTotal')
                            this.$store.dispatch('fetchCommunicationTotal')
                            break;
                        case 2:
                            this.$store.commit('resetCommunicationArchived')
                            this.$store.dispatch('fetchCommunicationArchived')
                            break;
                        default:
                            this.$store.commit('resetCommunication')
                            this.$store.dispatch('fetchCommunication')
                            break;
                    }
                }
                else {
                    switch (val) {
                        case 1:
                            this.$store.commit('resetCommunicationArchived')
                            this.$store.dispatch('fetchCommunicationArchived')
                            break;
                        default:
                            this.$store.commit('resetCommunication')
                            this.$store.dispatch('fetchCommunication')
                            break;
                    }
                }

            },
            deep: true
        }
    },
    mounted(){
        this.$store.commit('resetCommunication')
        this.$store.commit('resetCommunicationArchived')
        this.$store.commit('resetCommunicationTotal')
        this.current_tab=0
    }
}
</script>

