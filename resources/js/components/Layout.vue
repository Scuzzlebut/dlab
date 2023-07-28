<template>
  <v-app>
    <v-banner single-line v-model="showOnlineBanner" dark :color="isOffline ? 'blue' : 'success'" rounded transition="slide-y-transition">{{ isOffline ? 'Connessione internet assente' : 'Di nuovo online!'}}</v-banner>
    <v-app-bar app clipped-right flat dense color="secondary" elevation="1">
      <v-app-bar-nav-icon @click.stop="primaryDrawer = !primaryDrawer">
      </v-app-bar-nav-icon>
      <v-toolbar-title class="font-weight-medium" v-if="$vuetify.breakpoint.smAndUp">{{toolbartitle}}</v-toolbar-title>
      <v-spacer></v-spacer>
        <v-avatar tile v-if="$appOptions.appLogoQuadrato()" class="mr-2 pa-1">
          <v-img :src="$appOptions.appLogoQuadrato()" contain  alt="Logo"></v-img>
        </v-avatar>
        <div v-if="user.name">
          <span class="text-caption">{{user.name}}</span>
        </div>
      <v-menu bottom left offset-y origin="top right" transition="scale-transition" v-model="userMenu">
        <template v-slot:activator="{ attrs, on }">
          <v-btn class="ml-2" min-width="0" small icon v-bind="attrs" @click="userMenu=!userMenu">
            <v-icon>fas fa-caret-down</v-icon>
          </v-btn>
        </template>
        <v-list tile nav dense v-if="userMenu">
          <template v-for="(item, i) in userMenuItems">
            <base-listitemgroup v-if="item.children" :key="`group-${i}`" :item="item">
            </base-listitemgroup>
            <base-listitem v-else :key="`item-${i}`" :item="item"></base-listitem>
          </template>
          <v-list-item dense link @click="logout()">
            <v-list-item-icon>
              <v-icon>fas fa-sign-out-alt</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>
    <v-navigation-drawer v-model="primaryDrawer" fixed color="background" app dense id="main-navigation-drawer">
      <v-img :src="$appOptions.appLogoQuadrato()" contain @click="$router.push({name:'dashboard'})" max-height="80" class="clickarea">
        <template v-slot:placeholder>
            <v-sheet>
            <v-skeleton-loader type="image"></v-skeleton-loader>
            </v-sheet>
        </template>
      </v-img>
      <v-divider class="mb-2"></v-divider>
      <v-list expand nav dense>
        <template v-for="(item, i) in drawerItems">
          <base-listitemgroup v-if="item.children" :key="`group-${i}`" :item="item">
          </base-listitemgroup>
          <base-listitem v-else :key="`item-${i}`" :item="item"></base-listitem>
        </template>
      </v-list>
    </v-navigation-drawer>
    <v-main>
      <v-row dense align="center" justify="center" style="height:100%;" v-if="!loaded">
          <v-progress-circular indeterminate width="2"></v-progress-circular>
      </v-row>
      <router-view v-else/>
      <communication-toread v-if="$route.name!='communication'"></communication-toread>
      <material-snackbar></material-snackbar>
      <material-dialog></material-dialog>
      <attachments v-if="showAttachment"></attachments>
    </v-main>
  </v-app>
</template>

<script>
export default {
  data: () => ({
    userMenu: false,
    showOnlineBanner: false,
  }),
  computed: {
    showAttachment(){
      return this.$store.getters.showAttachment
    },
    loaded(){
      if (this.user){
        if (this.user.staff){
          if (this.user.staff.id!=null){
            return true
          }
        }
      }
      return false
    },
    user() {
      return this.$store.getters.getUser;
    },
    usermenuRoutes(){
      let routes=[]
      routes.push({
          icon: 'fas fa-user',
          title: this.$t('user.me'),
          to: '/user'
        })
      routes.push({
          icon: 'fas fa-unlock-alt',
          title: this.$t('auth.changepassword'),
          to: '/changepassword'
        })
      return routes
    },
    userMenuItems(){
      return this.usermenuRoutes.map(this.mapItem)
    },
    drawerItems () {
      return this.mainRoutes.map(this.mapItem)
    },
    mainRoutes(){
      let routes = []
      routes.push({
        icon: 'fas fa-person-digging',
        title: this.$t('dashboard.pagetitle'),
        to: 'dashboard'
      })
      routes.push({
        icon: 'fas fa-umbrella-beach',
        title: this.$t('attendance.pagetitle'),
        to: 'attendance'
      })
      routes.push({
        icon: 'fas fa-calendar-alt',
        title: this.$t('attendance.calendar'),
        to: 'calendar'
      })
      routes.push({
        icon: 'fas fa-bell',
        title: this.$t('communication.pagetitle'),
        to: 'communication'
      })
      routes.push({
        icon: 'fas fa-sack-dollar',
        title: this.$t('paysheet.pagetitle'),
        to: 'paysheet'
      })
      if (this.$can('staff-list')){
        routes.push({
          icon: 'fas fa-building-user',
          title: this.$t('staff.pagetitle'),
          to: 'staff'
        })
      }
      routes.push({
        icon: 'fas fa-clock',
        title: this.$t('timetracking.pagetitle'),
        to: 'staff'
      })
      return routes
    },
    toolbartitle(){
      let title=this.$t('global.appname')
      const self=this
      if (this.$route){
        let finded = this.mainRoutes.find(obj=>obj.to==self.$route.path)
        if (finded){
          title = finded.title
        }
      }
      return title
    },
    primaryDrawer: {
      get() {
        return this.$store.getters.getPrimaryDrawer;
      },
      set(val) {
        if (val!=this.$store.getters.getPrimaryDrawer){
          this.$store.commit('setPrimaryDrawer',val);
        }
      }
    },
    isOffline(){
      return this.$store.getters.isOffline
    },
  },
  methods: {
    mapItem (item) {
      return {
        ...item,
        children: item.children ? item.children.map(this.mapItem) : undefined,
        title: item.title,
      }
    },
    logout(){
      this.$store.dispatch('logout').then((res)=>{
        this.$router.push('/')
      })
    }
  },
  watch: {
    isOffline(val){
        const self=this
        if (val==true){
            self.showOnlineBanner=true
        }
        else {
            setTimeout(function () {
                self.showOnlineBanner=false
            }, 1000)
        }
    },
  },
  mounted() {
    moment.locale('it')
    if (('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)) {
      this.$store.commit('setTouchState', true)
    }
    window.addEventListener('online', () => this.$store.commit('setOfflineState', false));
    window.addEventListener('offline', () => this.$store.commit('setOfflineState', true));
  },
  beforeCreate() {
    if (this.$store.getters.isAuthenticated && _.isEmpty(this.$store.getters.getUser)){
      this.$store.dispatch('fetchUser')
    }
  }
}
</script>
