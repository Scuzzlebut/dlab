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
                            {{$t('auth.forgotpasswordtitle')}}
                        </div>
                        <span>{{$t('auth.forgotpasswordmessage')}}</span>
                        <v-form>
                            <v-row dense class="justify-center">
                                <v-col cols="12" xs="12">
                                    <ValidationProvider vid='email' :name="$t('user.email')" rules="required|email" v-slot="{ errors,field }">
                                        <v-text-field :label="$t('user.email')" v-model="email" :error-messages="errors" >
                                        </v-text-field>
                                    </ValidationProvider>
                                </v-col>
                            </v-row>
                        </v-form>
                        <template slot="actions">
                            <v-col cols="6" xs="6">
                                <material-button color="undo" @click="login()" outlined :text="$t('global.undo')"></material-button>
                            </v-col>
                            <v-col cols="6" xs="6">
                                <material-button color="primary" @click="askReset()" :loading="auth_loading" :disabled="failed" :text="$t('auth.resetpassword')"></material-button>
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
      email: ""
  }),
  computed: {
    auth_loading() {
        return this.$store.getters.auth_loading;
    },
    apierrors() {
        return this.$store.getters.getApiErrors;
    },
  },
  methods: {
    login() {
        const self = this;
        self.$router.push({
            path: '/'
        })
    },
    askReset() {
        const self = this;
        this.$refs.observer.validate().then((isValid) => {
            if (isValid == true) {
                this.$store.dispatch('askResetPassword', {email:this.email}).then((res) => {
                    if (res.message) {
                        self.$store.commit('showDialog', {
                            type: "advice",
                            title: this.$t('auth.passwordresetsent_title'),
                            message: this.$t('auth.passwordresetsent_message'),
                            okCb: () => {
                                self.$router.push('/login')
                            },
                        });
                    }
                })
            }
        })
    },
  },
  watch: {
    email: {
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
  },
};
</script>