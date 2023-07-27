<template>
    <ValidationProvider :vid="vid" :name="name" rules="" v-slot="{ errors,field }">
        <v-card outlined @click="openFileDialog()" @drop.prevent="onDrop" @dragover="onDragOver" @dragenter="onDragOver" @dragleave="onDragEnd" :class="{ 'grey lighten-2': dragover }">
            <v-card-text :class="{ 'error--text' : errors[0] }">
                <v-row class="d-flex flex-column" dense align="center" justify="center" v-if="!uploading">
                    <span v-if="$functions.renderErrorMessage(errors, vid, name)">{{$functions.renderErrorMessage(errors, vid, name)}}</span>
                    <v-icon :class="[dragover ? 'mt-2, mb-6' : 'mt-5']" size="60">fas fa-cloud-upload-alt</v-icon>
                    <p v-if="filename!=null">{{ $t('attachment.fileselected') }}{{filename}}</p>
                    <p v-else>{{ $t('attachment.drag_file_or_click') }}</p>
                </v-row>
                <v-row dense align="center" justify="center" v-else>
                    <v-progress-linear :value="percentualUpload" height="25">
                        <strong>{{ percentualUpload }}%</strong>
                    </v-progress-linear>
                </v-row>
                <input type="file" style="display:none" ref="fileupload" :accept="accept" id="file-upload" :multiple="multiple" @change="onDrop">
            </v-card-text>
        </v-card>
    </ValidationProvider>
</template>

<script>
export default {
    data: () => ({
      dragover: false,
      filename: null
    }),
    props: {
        multiple: {
            type: Boolean,
            default: false
        },
        uploading: {
            type: Boolean,
            default: false
        },
        types: {
            type: Array,
            default: () => ['csv','xls']
        },
        vid: {
            type: String,
            default: null
        },
        name: {
            type: String,
            default: null
        },
    },
    computed: {
        accept(){
            let file_types = [];
            if (this.types.indexOf('csv')!=-1){
                file_types.push('text/csv');
                file_types.push('csv');
            }

            if (this.types.indexOf('xls')!=-1){
                file_types.push('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                file_types.push('application/vnd.ms-excel');
            }

            if (this.types.indexOf('image')!=-1){
                file_types.push('image/*');
            }

            if (this.types.indexOf('pdf')!=-1){
                file_types.push('application/pdf');
            }

            return file_types
        },
        progress(){
            return this.$store.getters.getProgress
        },
        percentualUpload(){
            return Math.ceil(this.progress.loaded / this.progress.total * 100)
        },
        source(){
            return this.$store.getters.getSource
        }
    },
    methods: {
        onDragOver(e){
            e.preventDefault();
            this.dragover=true
        },
        onDragEnd(e){
            e.preventDefault();
            this.dragover=true
        },
        onDrop(e) {
            e.preventDefault();
            const self=this
            this.dragover = false;
            let droppedFiles = e.target.files || e.dataTransfer.files;
            if(!droppedFiles) return;
            let files = ([...droppedFiles])
            if (!this.multiple && files.length > 1) {
                this.$store.commit('showSnackbar', {
                    message: self.$t('attachment.multiple_upload_not_allowed'),
                    color: 'error',
                    duration: 3000
                })
            } else{
                let can_upload = true;

                files.forEach(element => {
                    // faccio il controllo preciso sul MIME (le immagini vengono accettate tutte quindi)
                    if (this.accept.indexOf(element.type) == -1 && (element.type.indexOf('image/') == -1 || this.accept.indexOf('image/*') == -1)){
                        can_upload = false;
                    }
                });

                if (can_upload){
                    this.$emit('fileuploaded',files)
                    this.filename=files[0].name
                } else {
                    this.$store.commit('showSnackbar', {
                        message: self.$t('attachment.extension_not_allowed'),
                        color: 'error',
                        duration: 3000
                    })
                }
            }
            this.$refs.fileupload.value=null;
        },
        openFileDialog() {
            document.getElementById('file-upload').click();
        },
    },
}
</script>