<template>
  <material-popup :dialogShowModel="showCreateEditModel" @escintent="hideCreateEditModel()" v-bind="$attrs" :fullscreen="isFullscreen">
    <ValidationObserver :ref="observerName" v-slot="{ failed }">
      <material-card  :icon="icon" :title="cardTitle" dismissable @dismissed="hideCreateEditModel()">
        <template v-if="$slots['instructions']" slot="after-heading">
            <slot name="instructions"/>
        </template>
        <v-row dense align="start" class="pt-2" v-if="showUploadProgress">
          <v-col cols="12" xs="12" v-for="(currentAttachment, index) in uploadDataInfo" :key="index">
            <v-alert text outlined icon="fas fa-times" color="error" elevation="0" class="py-1 mb-0" v-if="currentAttachment.errors">
                <span><strong>{{ currentAttachment.filename }}</strong></span>
                <v-divider class="my-1 error" style="opacity: 0.22"></v-divider>
                <span v-html="currentAttachment.errors"></span>
            </v-alert>
            <v-alert v-else text outlined :icon="currentAttachment.complete ? 'fas fa-check' : 'fas-fa-spinner'" :color="currentAttachment.complete ? 'success' : 'primary'" elevation="0" class="py-1 mb-0">
                <span><strong>{{ currentAttachment.filename }}</strong></span>
                <v-progress-linear :value="currentAttachment.progress" :indeterminate="currentAttachment.elaborating" height="25" v-if="!currentAttachment.complete">
                    <strong v-if="currentAttachment.elaborating">{{$t('attachment.processing')}}</strong>
                    <strong v-else>{{ currentAttachment.progress }}%</strong>
                </v-progress-linear>
            </v-alert>
          </v-col>
        </v-row>
        <v-form :disabled="disableedit" autocomplete="off" v-else>
          <v-row dense align="start" class="pt-2">
              <slot v-bind="$attrs"/>
          </v-row>
        </v-form>
        <template slot="actions">
          <v-col cols="6" xs="6" sm="3" v-if="!showUploadProgress && onlyclose">
              <material-button @click="hideCreateEditModel()" outlined :text="$t('global.ok')"></material-button>
          </v-col>
          <v-col cols="6" xs="6" sm="3" v-if="!showUploadProgress && !onlyclose">
            <material-button @click="hideCreateEditModel()" outlined :text="$t('global.undo')"></material-button>
          </v-col>
          <v-col cols="6" xs="6" sm="3" v-if="!showUploadProgress && !onlyclose">
            <material-button @click="createEditActionModel()" :disabled="loadingmodel_loading || failed || disableedit" :loading="loadingmodel_loading" color="success" :text="changes_not_saved ? $t('global.save') : $t('global.close')"></material-button>
          </v-col>
          <v-col cols="6" xs="6" sm="3" v-if="showUploadProgress">
            <material-button @click="hideCreateEditModel()" outlined :disabled="uploadattachment_loading" :text="$t('global.close')"></material-button>
          </v-col>
        </template>
      </material-card>
    </ValidationObserver>
  </material-popup>
</template>

<script>
  export default {
    data: () => ({
      original_var: null,
    }), 
    props: {
      element: {
        type: String,
        default: null
      },
      fullscreen: {
        type: Boolean,
        default: false
      },
      icon: {
        type: String,
        default: null
      },
      disableedit: {
        type: Boolean,
        default:false
      },
      onlyclose: {
        type: Boolean,
        default:false
      },
      emitreload: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      isFullscreen(){
        if (this.fullscreen){
          if (!this.showUploadProgress){
            return true
          }
        }
        return false
      },
      temporaryProgress(){
        return this.$store.getters.getTemporaryProgress
      },
      temporaryAttachment(){
        return this.$store.getters.getTemporaryAttachment
      },
      uploadattachment_loading(){
        return this.$store.getters.uploadattachment_loading
      },
      showUploadProgress(){
        if (!_.isEmpty(this.uploadDataInfo)){
          return true
        }
        return false
      },
      uploadDataInfo(){
        let attachment = []
        if (!_.isEmpty(this.temporaryProgress)){
          Object.keys(this.temporaryAttachment).forEach(index => {
            attachment[index]={}
            let file = this.temporaryAttachment[index].get('attachment')
            attachment[index].filename=file.name
            attachment[index].progress=Math.ceil(this.temporaryProgress[index].loaded / this.temporaryProgress[index].total * 100)
            attachment[index].errors = this.temporaryProgress[index].errors
            attachment[index].complete = this.temporaryProgress[index].complete
            if (attachment[index].progress>99 && !attachment[index].errors && !attachment[index].complete){
              attachment[index].elaborating=true
            }
            else {
              attachment[index].elaborating=false
            }
          });
          let can_close=true
          attachment.forEach(checkAttachment => {
            if ((!checkAttachment.complete) || (checkAttachment.errors)){
              can_close=false
            }
          });
          if (can_close){
            const self=this
            setTimeout(function () {
              self.hideCreateEditModel()
            }, 700);
          }
        }
        return attachment
      },
      source(){
          return this.$store.getters.getSource
      },
      observerName(){
        if (this.element){
          return this.element + 'observer'
        }
      },
      cardTitle(){
        if (this.showUploadProgress){
          return this.$t('attachment.attachments_processing_title')
        }
        else {
          if (this.currentModel.id){
            return this.$t(this.element.toLowerCase() + '.edit_title')
          }
          else {
            return this.$t(this.element.toLowerCase() + '.create_title')
          } 
        }
      },
      changes_not_saved() {
        return this.$store.getters.changes_not_saved;
      },
      apierrors() {
        return this.$store.getters.getApiErrors;
      },
      showCreateEditModel: {
        get: function () {
            if (this.$store.getters['showCreateEdit' + this.element] == true) {
              if (this.fullscreen || this.$vuetify.breakpoint.xsOnly){
                document.getElementsByTagName("html")[0].className = "noscroll";
              }
              this.$nextTick(() => {
                this.ResetForm()
              });
            } else {
              document.getElementsByTagName("html")[0].removeAttribute("class", "noscroll");
            }
            return this.$store.getters['showCreateEdit' + this.element];
        },
        set: function (value) {
          if (!value){
            if (this.emitreload){
              this.$emit('reload')
            }
            document.getElementsByTagName("html")[0].removeAttribute("class", "noscroll");
            this.$store.dispatch('hideAction','hideCreateEdit' + this.element)
          }
        },
      },
      currentModel: {
        get: function () {
          return this.$store.getters['getCurrent' + this.element];
        },
        set: function (value) {
          this.$store.commit('setCurrent' + this.element, value)
        },
      },
      loadingmodel_loading(){
        return this.$store.getters[this.element.toLowerCase() + '_loading']
      },
      currentOmitChanges() {
        return this.$store.getters['getOmitChanges' + this.element] || [];
      }
    },
    methods: {
      hideCreateEditModel() {
        if (this.changes_not_saved) {
          this.$store.commit('showDialog', {
            type: "confirm",
            title: this.$t('global.changes_not_saved_title'),
            message: this.$t('global.changes_not_saved_message'),
            okCb: () => {
              if (this.emitreload){
                this.$emit('reload')
              }
              this.original_var = null;
              this.$store.commit('setChanges_not_saved', false);
              this.ResetForm();
              this.$store.dispatch('hideAction','hideCreateEdit' + this.element)
            },
          });
        } else {
            if (this.emitreload){
              this.$emit('reload')
            }
            this.original_var = null;
            this.ResetForm();
            this.$store.dispatch('hideAction','hideCreateEdit' + this.element)
        }
      },
      ResetForm() {
        if (this.$refs[this.observerName]){
          this.$refs[this.observerName].reset()
        }
      },
      check_changes_not_saved() {
        if (this.original_var != null) {
          let current = _.cloneDeep(this.currentModel)
          let original = _.cloneDeep(this.original_var)
          this.currentOmitChanges.forEach(omitelement => {
            current = _.omit(current,omitelement)
            original = _.omit(original,omitelement)
          });
          this.$store.commit('setChanges_not_saved', !this.$functions.ObjectAreEqual(current,original));
        }
      },
      createEditActionModel() {
        const self = this
        if (this.changes_not_saved){
          this.$refs[this.observerName].validate().then((isValid) => {
            if (isValid == true) {
              this.$store.dispatch('create' + this.element).then((res)=>{
                if (res.object){
                  if (self.emitreload){
                    self.$emit('reload')
                  }
                  self.$emit('duplicatecomplete',res.object)
                  self.original_var = _.cloneDeep(self.currentModel)
                  self.check_changes_not_saved();
                  if (_.isEmpty(this.temporaryAttachment)){
                    self.$store.dispatch('hideAction','hideCreateEdit' + this.element)
                  }
                }
              })
            }
            else {
              self.$functions.scrollToError(self)
            }
          })
        }
        else {
          this.hideCreateEditModel()
        }
      },
    },
    watch: {
      currentModel: {
        handler: function (val) {
          if (val != null) {
            if (this.original_var == null) {
              this.original_var = _.cloneDeep(val)
            } else {
              if (this.original_var.id != val.id) {
                this.original_var = _.cloneDeep(val)
              }
            }
            this.check_changes_not_saved();
          }
        },
        deep: true
      },
      apierrors: {
        handler: function (val) {
          this.$functions.ApiErrorsWatcher(val,this.$refs[this.observerName])
          if (val){
            this.$functions.scrollToError(this)
          }
        },
        deep: true
      }
    },
    created() {
      this.$store.commit('resetTemporaryAttachment')
      this.original_var = _.cloneDeep(this.currentModel)
      this.check_changes_not_saved();
    },
    mounted() {

    },
    beforeDestroy() {
      document.getElementsByTagName("html")[0].removeAttribute("class", "noscroll");
    },
  }
</script>
