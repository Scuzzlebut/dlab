<template>
  <v-snackbar elevation="2" timeout="-1" class="communication clickarea" content-class="pa-2" :value="displayedMessage.show" v-if="displayedMessage.show" text outlined bottom :color="displayedMessage.color">
        <v-row no-gutters align="center" class="previewcontent" @click.stop="showCommunicationDetails()">
            <v-col cols="12" class="communicationheader">
                <v-icon small class="pr-1">far fa-clock</v-icon>
                <span v-html="humanizeCreationTime(displayedMessage.created_at)"></span>
            </v-col>
            <v-col cols="12" xs="12" class="h5 font-weight-bold" v-html="displayedMessage.title"></v-col>
            <v-col cols="12" xs="12" class="communicationcontent font-italic"><span v-html="displayedMessage.body"></span></v-col>
            <v-col cols="12" xs="12" class="d-flex flex-wrap mt-2">
                <v-icon small class="pr-1">far fa-user</v-icon>
                <span class="font-weight-bold" v-html="displayedMessage.creator_name"></span>
            </v-col>
        </v-row>
        <v-row no-gutters>
          <v-col cols="12" xs="12" align="right">
              <material-button outlined class="mr-1" @click="closeCommunication()" small :disabled="communicationtoread_loading" :loading="communicationtoread_loading">{{$t('communication.archive')}}<v-icon small class="pl-1">fas fa-box-archive</v-icon></material-button>
          </v-col>
        </v-row>
  </v-snackbar>
</template>

<script>
export default {
  data: () => ({
  }),
  computed: {
    communicationtoread() {
      return this.$store.getters.getCommunicationToRead;
    },
    communicationtoread_loading(){
      return this.$store.getters.communicationtoread_loading;
    },
    displayedMessage(){
      let snack={
        show:false
      }
      if (!_.isEmpty(this.communicationtoread)){
        snack = _.cloneDeep(this.communicationtoread)
        snack.show=true
      }
      return snack
    }
  },
  methods: {
    closeCommunication(){
      this.$store.dispatch('closeCommunication',this.displayedMessage.id)
    },
    communicationColor(item){
        return 'maingrey'
    },
    communicationIcon(item){
        return 'far fa-comment-dots'
    },
    humanizeCreationTime(time){
        return moment(time).fromNow()
    },
    showCommunicationDetails(){
        this.$store.commit('setCurrentCommunication',_.cloneDeep(this.displayedMessage))
        this.$router.push({name: 'communication'})
        this.$store.dispatch('showAction','showCreateEditCommunication')
    },
  },
  created() {
    this.$store.dispatch('fetchCommunicationToRead')
  },
}
</script>
