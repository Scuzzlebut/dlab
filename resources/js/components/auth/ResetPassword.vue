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
                    {{$t('auth.setnewpassword')}}
                </div>
                <span>{{$t('auth.setnewpasswordmessage')}}</span>
                <v-form>
                    <v-row dense class="justify-center">
                        <v-col cols="12" xs="12">
                            <ValidationProvider vid='email' :name="$t('user.email')" rules="required|email" v-slot="{ errors,field }">
                                <v-text-field :label="$t('user.email')" v-model="user.email" :error-messages="errors" >
                                </v-text-field>
                            </ValidationProvider>
                        </v-col>
                        <v-col cols="12" xs="12">
                            <ValidationProvider vid='password' :name="$t('auth.password')" rules="required|min:6" v-slot="{ errors,field }">
                                <v-text-field :label="$t('auth.password')" v-model="user.password" :error-messages="errors" type="password">
                                </v-text-field>
                            </ValidationProvider>
                        </v-col>
                        <v-col cols="12" xs="12">
                            <ValidationProvider vid='password' :name="$t('auth.password_confirmation')" rules="required|min:6" v-slot="{ errors,field }">
                                <v-text-field :label="$t('auth.password_confirmation')" v-model="user.password_confirmation" :error-messages="errors" type="password">
                                </v-text-field>
                            </ValidationProvider>
                        </v-col>
                    </v-row>
                </v-form>
                <template slot="actions">
                <v-col cols="6" xs="6">
                    <material-button @click="login()" outlined :disabled="auth_loading" :text="$t('auth.login')"></material-button>
                </v-col>
                <v-col cols="6" xs="6">
                    <material-button color="success" @click="resetPassword()" :loading="auth_loading" :disabled="failed" :text="$t('global.confirm')"></material-button>
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
                email:null,
                password: "",
                password_confirmation: "",
                token: null,
            },
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
            resetPassword(){
                const self = this;
                this.$refs.observer.validate().then((isValid) => {
                    if (isValid == true) {
                        this.$store.dispatch('resetPassword',this.user).then((res) => {
                            if (res.message){
                                this.$router.push('/')
                            }
                        })
                    }
                })
            },
            login() {
                const self = this;
                self.$router.push({
                    path: '/'
                })
            },
        },
        watch: {
        apierrors: {
            handler: function (val) {
                this.$functions.ApiErrorsWatcher(val,this.$refs.observer)
            },
            deep: true
        },
        },
        mounted() {
            if (this.$route.query){
                this.user.email = this.$route.query.email
                this.user.token=this.$route.query.token
            }
            else {
                this.$store.commit('showSnackbar', {
                    message: self.$t('global.not_valid_link'),
                    color: 'error',
                    duration: 3000
                })
                this.$router.push({name:'login'})
            }
        },
        beforeCreate() {
            if (this.$store.getters.isAuthenticated) {
                this.$store.dispatch('logout')
            }
        }
    }
</script>
