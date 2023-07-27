
<template>
    <auth-layout>
      <v-responsive min-height="100vh" class="d-flex align-center" slot="content">
        <main-container centered>
          <ValidationObserver ref="observer" v-slot="{ failed }">
            <material-card class="px-5 py-3" max-width="400" color="white">
              <v-img :src="$appOptions.appLogoQuadrato()" slot="image" class="py-5" max-height="150" contain>
                <template v-slot:placeholder>
                    <v-sheet>
                    <v-skeleton-loader type="image"></v-skeleton-loader>
                    </v-sheet>
                </template>
              </v-img>
              <div slot="after-heading">
                {{$t('auth.login_title')}}
              </div>
              <v-form>
                <v-row dense class="justify-center">
                  <v-col cols="12" xs="12">
                    <ValidationProvider vid='email' :name="$t('user.email')" rules="required|email" v-slot="{ errors,field }">
                        <v-text-field @keyup.enter="login" type="text" prepend-icon="fas fa-user" :label="$t('user.email')" v-model="user.email" name="email" :error-messages="errors" @change="resetValidator()">
                        </v-text-field>
                    </ValidationProvider>
                  </v-col>
                  <v-col cols="12" xs="12">
                    <ValidationProvider vid='password' :name="$t('auth.password')" rules="required|min:6" v-slot="{ errors,field }">
                        <v-text-field @keyup.enter="login" ref="password" id="password" prepend-icon="fas fa-unlock-alt" name="password" :error-messages="errors" :label="$t('auth.password')" type="password" v-model="user.password" @change="resetValidator()">
                        </v-text-field>
                    </ValidationProvider>
                  </v-col>
                </v-row>
              </v-form>
              <template slot="actions">
                <v-col cols="12" xs="12" sm="6">
                  <router-link :to="{ name: 'askreset' }">{{$t('auth.askreset')}}</router-link>
                </v-col>
                <v-col cols="6" xs="6">
                  <material-button color="success" @click="login" :loading="auth_loading" :disabled="failed" :text="$t('auth.login')"></material-button>
                </v-col>
              </template>
            </material-card>
          </ValidationObserver>
        </main-container>
      </v-responsive>
    </auth-layout>
</template>

<script>

export default {
  data: () => ({
      user: {
        email: "",
        password: "",
        name: "webapp"
      }
  }),
  computed: {
    auth_loading() {
        return this.$store.getters.auth_loading;
    },
    isAuthenticated(){
      return this.$store.getters.isAuthenticated;
    },
    apierrors() {
      return this.$store.getters.getApiErrors;
    },
  },
  methods: {
    resetValidator() {
        this.$refs.observer.reset()
    },
    signup() {
      this.$router.push({
          path: '/register'
      })
    },
    login: function () {
        const self = this;
        this.$refs.observer.validate().then((isValid) => {
            if (isValid == true) {
              this.$store.dispatch('login', this.user).then(() => {
                  if (this.isAuthenticated) {
                      self.$router.push({
                          name: 'dashboard'
                      })
                  }
              })
            }
        })
    }
  },
  watch: {
    'user.email': {
        handler: function (val) {
          if (val.indexOf(' ') != -1) {
              this.user.email = val.replace(/\s/, '')
          }
        }
    },
    apierrors: {
      handler: function (val) {
          this.$functions.ApiErrorsWatcher(val,this.$refs.observer)
      },
      deep: true
    },
  }
};
</script>