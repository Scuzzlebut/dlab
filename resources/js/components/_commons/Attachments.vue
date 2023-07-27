<template>
    <material-popup :dialogShowModel="showAttachment" @escintent="closeAttachment()">
        <ValidationObserver ref="attachmentobserver" tag="div" v-slot="{ failed }">
            <material-card  icon="fas fa-paperclip" :title="$t('attachment.attachments')" dismissable @dismissed="closeAttachment()">
                <v-row dense>
                    <v-col cols="12" xs="12" align="right" class="py-0" v-if="!showAddAttachment && canCreateOrDelete">
                        <v-btn @click="addNewAttachment()" small outlined>{{$t('attachment.add_attachment')}}<v-icon small class="pl-1">fas fa-plus</v-icon></v-btn>
                    </v-col>
                    <v-col cols="12" xs="12" v-if="showAddAttachment">
                        <ValidationProvider vid='title' :name="$t('attachment.file_title')" rules="max:100" v-slot="{ errors,field }">
                            <v-text-field  :label="$t('attachment.file_title')" v-model="title" :error-messages="errors">
                            </v-text-field>
                        </ValidationProvider>
                    </v-col>
                    <v-col cols="12" xs="12" v-if="showAddAttachment">
                        <ValidationProvider vid='description' :name="$t('attachment.file_description')" rules="max:255" v-slot="{ errors,field }">
                            <v-text-field :label="$t('attachment.file_description')" v-model="description" :error-messages="errors">
                            </v-text-field>
                        </ValidationProvider>
                    </v-col>
                    <v-col cols="12" xs="12" v-if="showAddAttachment">
                        <upload-file vid="attachment" :name="$t('attachment.attachment')" @fileuploaded="storeTempForUpload" :uploading="uploadattachment_loading" :types="['image','pdf']"></upload-file>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" v-if="!showAddAttachment && currentDisplayAttachment" class="pb-2">
                        <image-zoom v-if="currentDisplayAttachment.thumbnail_link  && currentAttachmentModel.id" :src="currentDisplayAttachment.thumbnail_link" @click="$functions.openLink(currentDisplayAttachment.link)"></image-zoom>
                        <v-card v-else-if="currentAttachmentModel.id" outlined @click="currentAttachmentModel.id ? downloadOrViewAttachment() : null">
                            <v-card-text v-intersect="loadAttachmentPreview()">
                                <v-row class="d-flex flex-column" dense align="center" justify="center">
                                    <v-icon class='mt-5 mb-3' size="60">fas fa-hat-wizard</v-icon>
                                    <v-progress-linear indeterminate height="25">
                                        <strong>{{$t('attachment.preview_loading')}}</strong>
                                    </v-progress-linear>
                                </v-row>
                            </v-card-text>
                        </v-card>
                        <image-zoom  v-else-if="currentDisplayAttachment.filetype.indexOf('image')!=-1" :src="currentDisplayAttachment.thumbnail_link"></image-zoom>
                        <v-card v-else outlined>
                            <v-card-text>
                                <v-row class="d-flex flex-column" dense align="center" justify="center">
                                    <v-icon class='mt-5' size="60">{{$functions.iconFromFileType(currentDisplayAttachment.filetype)}}</v-icon>
                                    <p>{{$t('attachment.preview_loaded_after_save')}}</p>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" v-if="!showAddAttachment && currentDisplayAttachment">
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('attachment.file_title')}}:</strong>{{currentDisplayAttachment.title}}</span></div>
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('attachment.filename')}}:</strong>{{currentDisplayAttachment.filename}}</span></div>
                        <div><span class="subtitle-1"><strong class="pr-1">{{$t('attachment.filesize')}}:</strong>{{$formatters.fileSize(currentDisplayAttachment.filesize)}}</span></div>
                        <v-row dense>
                            <v-col cols="12" xs="12" align="right" v-if="canCreateOrDelete">
                                <material-button small class="mr-2" color="warning" @click="showUpdateAttachment()" outlined :text="$t('global.edit')"><v-icon small class="pl-1">far fa-edit</v-icon></material-button>
                                <material-button small @click="deleteAttachment()" outlined :text="$vuetify.breakpoint.xsOnly ? null : $t('global.delete')" :disabled="attachment_loading"><v-icon small class="pl-1">fas fa-trash-alt</v-icon></material-button>
                            </v-col>
                        </v-row>
                        <instant-edit v-model="single_attachment_edit" icon="fas fa-paperclip" title="Modifica" v-if="showAttachment">
                            <v-row justify="center" dense>
                                <v-col cols="12" xs="12">
                                    <ValidationProvider ref="title" vid='title' :name="$t('attachment.file_title')" rules="max:100" v-slot="{ errors,field }">
                                        <v-text-field :label="$t('attachment.file_title')" v-model="currentAttachment.title" :error-messages="errors">
                                        </v-text-field>
                                    </ValidationProvider>
                                </v-col>
                                <v-col cols="12" xs="12" sm="12">
                                    <ValidationProvider ref="filename" vid='filename' :name="$t('attachment.filename')" rules="" v-slot="{ errors,field }">
                                        <v-text-field :label="$t('attachment.filename')" v-model="currentAttachment.filename" :error-messages="errors">
                                        </v-text-field>
                                    </ValidationProvider>
                                </v-col>
                                <v-col cols="12" xs="12">
                                    <ValidationProvider ref="description" vid='description' :name="$t('attachment.file_description')" rules="max:255" v-slot="{ errors,field }">
                                        <v-text-field :label="$t('attachment.file_description')" v-model="currentAttachment.description" :error-messages="errors">
                                        </v-text-field>
                                    </ValidationProvider>
                                </v-col>
                            </v-row>
                            <template slot="instantactions">
                                <v-col cols="6" xs="6" sm="4" align="right">
                                    <material-button large @click="single_attachment_edit=false" outlined :text="$t('global.undo')"></material-button>
                                </v-col>
                                <v-col cols="6" xs="6" sm="4" align="right">
                                    <material-button large @click="updateAttachment()" color="success" :text="$t('global.ok')" :disabled="attachment_loading" :loading="attachment_loading"></material-button>
                                </v-col>
                            </template>
                        </instant-edit>
                    </v-col>
                    <v-col cols="12" xs="12" v-if="!currentDisplayAttachment && !showAddAttachment">
                            <div class="my-10">
                                <v-row justify="center">
                                    <v-col cols="12" align="center">
                                        <v-icon size="70" color="warning">fas fa-folder-open</v-icon>
                                    </v-col>
                                    <v-col cols="12" align="center">
                                        <div class="text-h3 font-weight-bold warning--text" v-html="$t('attachment.no_attachments')"></div>
                                        <div class="text-h6" v-html="$t('attachment.no_attachments_message')"></div>
                                    </v-col>
                                </v-row>
                            </div>
                    </v-col>
                </v-row>
                <template slot="actions">
                    <v-col cols="12" align="center" v-if="!showAddAttachment && currentDisplayAttachment">
                        <v-pagination v-model="current_attachment_index" :length="currentAttachmentModel.id ? currentAttachmentModel.attachments.length : temporaryAttachment.length"></v-pagination>
                    </v-col>
                    <v-col cols="6" xs="6" sm="3" v-if="!showAddAttachment">
                        <material-button large @click="showAttachment=false" color="success" :text="$t('global.ok')"></material-button>
                    </v-col>
                    <v-col cols="6" xs="6" sm="3" v-if="showAddAttachment && !uploadattachment_loading">
                        <material-button large @click="closeAddAttachment()" outlined :text="$t('global.undo')"></material-button>
                    </v-col>
                    <v-col cols="6" xs="6" sm="3" v-if="showAddAttachment">
                        <material-button large @click="uploadAttachment" color="success" :text="$t('global.upload')" :disabled="!file_selected" :loading="uploadattachment_loading"></material-button>
                    </v-col>
                </template>
            </material-card>
        </ValidationObserver>
    </material-popup>
</template>

<script>
export default {
    data: () => ({
        single_attachment_edit: false,
        description: null,
        title: null,
        current_attachment_index: 1,
        selfloading: false,
        selfpreview_loading: false,
        loadPreviewTimeout: null,
        file_selected: false,
    }),
    props: {
    },
    computed: {
        attachmentscategory(){
            return this.$store.getters.getAttachmentCategory
        },
        canCreateOrDelete(){
            switch (this.attachmentscategory) {
                case 'attendance':
                    return this.$can('attachmentmanagement-attendance')
                    break;
                case 'communication':
                    return this.$can('attachmentmanagement-communication')
                    break;
                case 'staff':
                    return this.$can('attachmentmanagement-staff')
                    break;
                case 'paysheet':
                    return this.$can('attachmentmanagement-paysheet')
                    break;
                default:
                    return false
                    break;
            }

        },
        currentAttachment: {
            get: function () {
                return this.$store.getters.getCurrentAttachment;
            },
            set: function (value) {
                this.$store.commit('setCurrentAttachment', value)
            },
        },
        currentDisplayAttachment(){
            if (this.currentAttachmentModel.id){
                if (!_.isEmpty(this.currentAttachmentModel.attachments)){
                    return this.currentAttachmentModel.attachments[this.current_attachment_index - 1]
                }
            }
            else {
                if (!_.isEmpty(this.temporaryAttachment)){
                    let tempFormData= this.temporaryAttachment[this.current_attachment_index - 1]
                    let responseFormData={
                        file: tempFormData.get('attachment'),
                        title: tempFormData.get('title'),
                        filename: tempFormData.get('filename'),
                        description: tempFormData.get('description')
                    }
                    responseFormData.filetype = responseFormData.file.type
                    responseFormData.filesize = responseFormData.file.size
                    if ( window.webkitURL ) {
                        responseFormData.link = window.webkitURL.createObjectURL(new Blob([responseFormData.file]));
                        responseFormData.thumbnail_link = responseFormData.link
                    } else if ( window.URL && window.URL.createObjectURL ) {
                        responseFormData.link = window.URL.createObjectURL(new Blob([responseFormData.file]));
                        responseFormData.thumbnail_link = responseFormData.link
                    }
                    return responseFormData
                }
            }
            return null
        },
        attachment_loading(){
            return this.$store.getters.attachment_loading
        },
        uploadattachment_loading(){
            return this.$store.getters.uploadattachment_loading
        },
        apierrors() {
            return this.$store.getters.getApiErrors;
        },
        currentAttachmentModel: {
            get: function () {
                return this.$store.getters.getCurrentAttachmentModel;
            },
            set: function (value) {
                this.$store.commit('setCurrentAttachmentModel', value)
            },
        },
        showAttachment: {
            get: function () {
                return this.$store.getters.showAttachment;
            },
            set: function (value) {
                if (value == false) {
                    this.$store.dispatch('hideAction','hideAttachment');
                }
            },
        },
        showAddAttachment: {
            get: function () {
                return this.$store.getters.showAddAttachment;
            },
            set: function (value) {
                if (value == false) {
                    this.$store.dispatch('hideAction','hideAddAttachment');
                }
            },
        },
        temporaryAttachment: {
            get: function () {
                return this.$store.getters.getTemporaryAttachment;
            },
            set: function (value) {
                this.$store.commit('setTemporaryAttachment', value)
            },         
        },
        attachmentpreview_loading() {
            return this.$store.getters.attachmentpreview_loading;
        }
    },
    methods: {
        loadAttachmentPreview(){
            const self = this
            if (!self.selfpreview_loading){
                if (self.currentDisplayAttachment.thumbnail_link) {
                } else {
                    if (!self.attachmentpreview_loading && self.currentDisplayAttachment.id){
                        self.selfpreview_loading=true
                        self.$store.dispatch('loadAttachmentPreview',self.currentDisplayAttachment.id)
                    }
                    self.loadPreviewTimeout = setTimeout(function () {
                        self.selfpreview_loading=false
                        self.loadAttachmentPreview()
                    }, 5000);
                }
            }

        },
        closeAddAttachment(){
            if (this.currentAttachmentModel.id){
                if (_.isEmpty(this.currentAttachmentModel.attachments)){
                    this.closeAttachment()
                }
            }
            else {
                if (_.isEmpty(this.temporaryAttachment)){
                    this.closeAttachment()
                }
            }
            this.showAddAttachment=false
        },
        closeAttachment(){
            this.showAddAttachment=false
            this.showAttachment=false
        },
        addNewAttachment(){
            if (this.canCreateOrDelete){
                this.description=null
                this.title=null
                this.$store.dispatch('showAction','showAddAttachment');
            }
            else {
                this.showAddAttachment=false
            }
        },
        showUpdateAttachment(){
            this.currentAttachment = _.cloneDeep(this.currentDisplayAttachment)
            this.single_attachment_edit=true
        },
        updateAttachment(){
            const self=this
            this.$refs.attachmentobserver.validate().then((isValid) => {
                if (isValid == true) {
                    if (self.currentAttachmentModel.id){
                        self.$store.dispatch('createAttachment').then((res) => {
                            if (res.object){
                                self.single_attachment_edit=false
                            }
                        })
                    }
                    else {
                        let cloned=self.temporaryAttachment[self.current_attachment_index - 1]
                        if (self.currentAttachment.title){
                            cloned.set('title',self.currentAttachment.title)
                        }
                        if (self.currentAttachment.filename){
                            cloned.set('filename',self.currentAttachment.filename)
                        }
                        if (self.currentAttachment.description){
                            cloned.set('description',self.currentAttachment.description)
                        }
                        self.temporaryAttachment[self.current_attachment_index - 1]=cloned
                        self.temporaryAttachment=_.cloneDeep(self.temporaryAttachment)
                        self.single_attachment_edit=false
                    }
                }
            })
        },
        deleteAttachment(){
            const self=this
            let message = self.$t('global.delete_confirm_message_pre')
            message+= self.$t('attachment.question_selected') 
            self.$store.commit('showDialog', {
                type: "confirm",
                title: self.$t('global.delete_confirm_title'),
                message:  message,
                okCb: () => {
                    if (self.currentAttachmentModel.id){
                        self.$store.dispatch('deleteAttachment',self.currentDisplayAttachment.id).then((res) => {
                            if (res.object){
                                self.current_attachment_index=1
                                if (_.isEmpty(self.currentAttachmentModel.attachments)){
                                    self.closeAttachment()
                                }
                            }
                        })
                    }
                    else {
                        const self=this
                        self.temporaryAttachment.splice(self.current_attachment_index-1,1)
                        self.current_attachment_index=1
                        if (_.isEmpty(self.temporaryAttachment)){
                            self.closeAttachment()
                        }
                    }
                },
            });
        },
        storeTempForUpload(files){
            this.formData = new FormData();
            this.formData.append('attachment', files[0]);
            this.file_selected=true
        },
        uploadAttachment(){
            const self=this
            if (this.description){
                this.formData.append('description',this.description);
            }
            if (this.title){
                this.formData.append('title',this.title);
            }
            if (self.currentAttachmentModel.id){
                self.$store.dispatch('uploadAttachment',self.formData).then((res)=>{
                    if (res.object){
                        self.showAddAttachment=false
                        self.current_attachment_index=self.currentAttachmentModel.attachments.length
                        self.file_selected=false
                    }
                })
            }
            else {
                self.formData.append('index',self.temporaryAttachment.length)
                self.temporaryAttachment.push(self.formData)
                self.showAddAttachment=false
                self.file_selected=false
                self.current_attachment_index=self.temporaryAttachment.length
            }
        },
        downloadOrViewAttachment() {
            if (this.currentDisplayAttachment.link && params.action=='view'){
                this.$functions.openLink(this.currentDisplayAttachment.link)
            }
            else {
                this.$functions.openLink(this.currentDisplayAttachment.link,'download',this.currentDisplayAttachment.filename)
            }
        }
    },
    created() {
        this.current_attachment_index=1
        if (this.currentAttachmentModel.id){
            if (_.isEmpty(this.currentAttachmentModel.attachments)){
                this.addNewAttachment()
            }
            else {
                this.showAddAttachment=false
            }
        }
        else {
            if (_.isEmpty(this.temporaryAttachment)){
                this.addNewAttachment()
            }
            else {
                this.showAddAttachment=false
            }
        }
    },
    watch: {
        apierrors: {
            handler: function (val) {
                this.$functions.ApiErrorsWatcher(val,this.$refs.attachmentobserver)
            },
            deep: true
        },
        currentAttachmentModel: {
            handler: function(val){
                if (val.id){
                    if (_.isEmpty(val.attachments)){
                        this.addNewAttachment()
                    }
                }
                else {
                    if (_.isEmpty(this.temporaryAttachment)){
                        this.addNewAttachment()
                    } 
                }
            },
            deep: true
        },
    },
    beforeDestroy() {
        clearTimeout(this.loadPreviewTimeout)
    }
}
</script>
