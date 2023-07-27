<template>
    <v-container fluid fill-height justify-center tag="section">
        <ValidationObserver ref="observer" v-slot="{ failed }">
            <v-row justify="center">
                <material-card max-width="500" icon="fas fa-unlock-alt" :title="$t('auth.changepassword')">
                    <div slot="after-heading">
                        {{$t('auth.setnewpasswordmessage')}}
                    </div>
                    <v-form ref="changePassword" flat>
                        <v-text-field v-model="user.email" type="email" autocomplete="email" class="hidden">
                        </v-text-field>
                        <v-row dense>
                            <v-col cols="12" xs="12">
                                <ValidationProvider vid='password' :name="$t('auth.newpassword')" rules="required|min:6" v-slot="{ errors,field }">
                                    <v-text-field :label="$t('auth.newpassword')" @keydown.space.prevent v-model="change.password" :error-messages="errors" type="password" @change="resetValidator" @input="updateStrength" autocomplete="new-password">
                                        <v-progress-circular slot="append-outer"  size="50" :rotate="90" :value="score" :color="scoreColor" v-if="change.password">{{ displayedScore }}</v-progress-circular>
                                    </v-text-field>
                                </ValidationProvider>
                                <span class="text-caption" :style="{'color':scoreColor}" v-if="change.password">{{scoreMessage}}</span>
                            </v-col>
                            <v-col cols="12" xs="12">
                                <ValidationProvider vid='password' :name="$t('auth.password_confirmation')" rules="required|min:6" v-slot="{ errors,field }">
                                    <v-text-field @keyup.enter="ChangePassword()" @keydown.space.prevent :label="$t('auth.password_confirmation')" v-model="change.password_confirmation" :error-messages="errors" type="password" @change="resetValidator" autocomplete="new-password">
                                    </v-text-field>
                                </ValidationProvider>
                            </v-col>
                        </v-row>
                    </v-form>
                    <template slot="actions">
                        <v-col cols="6" xs="6">
                            <material-button @click="$router.push({name:'dashboard'})" outlined :text="$t('global.undo')"></material-button>
                        </v-col>
                        <v-col cols="6" xs="6">
                            <material-button @click="ChangePassword()" color="success" :text="$t('auth.changepassword')" :loading="auth_loading" :disabled="auth_loading || failed"></material-button>
                        </v-col>
                    </template>
                </material-card>
            </v-row>
        </ValidationObserver>
    </v-container>
</template>

<script>
export default {
    data: () => ({
        score: 0,
        change: {
            password: '',
            password_confirmation: '',
        },
    }),
    computed: {
        displayedScore(){
            return Math.floor(this.score/10)
        },
        scoreMessage(){
            return this.$functions.passwordScoreMessage(this.displayedScore)
        },
        scoreColor(){
            return this.$functions.passwordScoreColor(this.displayedScore)
        },
        auth_loading() {
            return this.$store.getters.auth_loading;
        },
        user() {
            return this.$store.getters.getUser;
        },
        apierrors() {
            return this.$store.getters.getApiErrors;
        },
    },
    methods: {
        updateStrength(password) {
            this.score = this.$functions.checkPassword(password)
        },
        resetValidator() {
            this.$refs.observer.reset()
        },
        ChangePassword() {
            const self = this;
            this.change.id = this.user.id
            this.$refs.observer.validate().then((isValid) => {
                if (isValid == true) {
                    self.$store.dispatch('changePassword', self.change).then((res) => {
                        if (res.message) {
                            self.$router.push({name:'dashboard'})
                        }
                    })
                }
            })
        }
    },
    watch: {
        apierrors: {
            handler: function (val) {
                this.$functions.ApiErrorsWatcher(val,this.$refs.observer)
            },
            deep: true
        },
    }
}
</script>
