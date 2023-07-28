<template>
    <main-container>
      <material-card icon="fas fa-user" :title="$t('user.me')" class="mb-10">
          <div slot="after-heading" v-if="user.staff" class="font-weight-bold">
              {{user.staff.fullname}}
          </div>
          <v-row dense>
              <v-col cols="12" xs="12" align="right">
                <material-button small color="warning" @click="editUser()" outlined :text="$t('global.edit')"><v-icon small class="pl-1">far fa-edit</v-icon></material-button>
              </v-col>
              <v-col cols="12" xs="12" sm="6">
                <content-value icon="fas fa-hashtag" :name="$t('staff.code')" :value="user.staff.code"></content-value>
                <content-value icon="fas fa-address-card" :name="$t('staff.taxcode')" :value="user.staff.taxcode"></content-value>
                <content-value icon="fas fa-birthday-cake" :name="$t('staff.birthday')" :value="$formatters.frontEndDateFormat(user.staff.birthday)"></content-value>
                <content-value icon="fas fa-hospital" :name="$t('staff.birthplace')" :value="user.staff.birthplace"></content-value>
              </v-col>
              <v-col cols="12" xs="12" sm="6">
                <content-value icon="fas fa-envelope" :name="$t('staff.private_email')" :value="user.staff.private_email"></content-value>
                <content-value icon="fas fa-phone" :name="$t('staff.phone_number')" :value="user.staff.phone_number"></content-value>
                <content-value icon="fas fa-building-columns" :name="$t('staff.iban')" :value="user.staff.iban"></content-value>
                <content-value icon="fas fa-house" :name="$t('staff.residence')" :value="formattedResidence"></content-value>
              </v-col>
            </v-row>
      </material-card>
      <material-card icon="fas fa-info" :title="$t('user.info')" class="mb-10">
          <v-row dense>
              <v-col cols="12" xs="12">
                <content-value icon="far fa-clock" :name="$t('user.timetable')" :value="formattedTimeTable"></content-value>
              </v-col>
            </v-row>
      </material-card>
      <create-edit-activity v-if="showCreateEditActivity"></create-edit-activity>
    </main-container>
</template>

<script>
export default {
  data: () => ({
  }),
  computed: {
    user(){
      return this.$store.getters.getUser;
    },
    formattedResidence(){
      let residence = ''
      if (this.user.staff){
        if (this.user.staff.address){
          residence = this.user.staff.address
        }
        if (this.user.staff.postcode){
          if (residence!=''){
            residence += '<br>'
          }
          residence+= this.user.staff.postcode
        }
        if (this.user.staff.city){
          if (residence!=''){
            residence += ' - '
          }
          residence+= this.user.staff.city
        }
        if (this.user.staff.state){
          residence += ' (' + this.user.staff.city + ')'
        }
      }
      return residence
    },
    formattedTimeTable(){
      let timetable=""
      if (this.user.staff){
        timetable+= moment(this.user.staff.morning_starttime, 'HH:mm:ss').format('HH:mm')
        timetable+= ' - '
        timetable+= moment(this.user.staff.morning_endtime, 'HH:mm:ss').format('HH:mm')
        timetable+= ' / '
        timetable+= moment(this.user.staff.afternoon_starttime, 'HH:mm:ss').format('HH:mm')
        timetable+= ' - '
        timetable+= moment(this.user.staff.afternoon_endtime, 'HH:mm:ss').format('HH:mm')
      }
      return timetable
    },
    showCreateEditActivity(){
      return this.$store.getters.showCreateEditActivity
    }
  },
  methods: {
    editUser(){
      this.$store.dispatch('setCurrentModelForAttachment',{
          object: _.cloneDeep(this.user.staff),
          filecategory: 'staff'
      })
      this.$store.dispatch('showAction','showCreateEditActivity')
    }
  }
}
</script>
