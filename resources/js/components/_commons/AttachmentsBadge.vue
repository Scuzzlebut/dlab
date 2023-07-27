<template>
  <v-badge :value="attachmentCount>0" color="primary" overlap right :class="{'mr-2': attachmentCount>0}" class="mb-1">
      <template v-slot:badge>
          <span>{{attachmentCount}}</span>
      </template>
      <v-btn @click="showAttachmentDialog" small outlined  :color="attachmentCount>0 ? 'primary' : null">{{label ? label : $t('attachment.attachments')}}<v-icon small class="pl-1">fas fa-paperclip</v-icon></v-btn>
  </v-badge>
</template>

<script>
  export default {
    props: {
      element: {
        type: String,
        default: null
      },
      label: {
        type: String,
        default: null
      },
    },
    computed: {
      currentAttachmentModel(){
        return this.$store.getters.getCurrentAttachmentModel
      },
      temporaryAttachment: {
          get: function () {
              return this.$store.getters.getTemporaryAttachment;
          },
          set: function (value) {
              this.$store.commit('setTemporaryAttachment', value)
          },         
      },
      attachmentCount(){
        if (this.currentAttachmentModel.id){
          if (this.currentAttachmentModel.attachments){
            return this.currentAttachmentModel.attachments.length
          }
          if (this.currentAttachmentModel.attachments_count){
            return this.currentAttachmentModel.attachments_count
          }
        }
        else{
          return this.temporaryAttachment.length
        }
      },
    },
    methods: {
      showAttachmentDialog(){
        if(this.element){
          let current=this.$store.getters['getCurrent' + this.element]
          let category=this.$store.getters.getAttachmentCategory
          this.$store.dispatch('setCurrentModelForAttachment',{
              object: _.cloneDeep(current),
              filecategory: category
          })
          this.$store.dispatch('showAction','showAttachment');
        }
      }
    },
    mounted() {
      this.temporaryAttachment=[]
    },
  }
</script>
