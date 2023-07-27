<template>
    <material-popup :dialogShowModel="showDialog" @escintent="showDialog=false">
        <material-card  :fullscreenadapter="$vuetify.breakpoint.xsOnly" :icon="iconSet" :title="dialogTitle" :color="dialogType=='alert' ? 'warning' : 'primary'" dismissable @dismissed="showDialog=false">
            <div slot="text-left" style="white-space: pre-line; word-break: break-word" v-html="dialogMessage"></div>
            <v-row v-if="dialogType=='typeconfirm'">
                <v-col cols="12" xs="12">
                    <v-text-field v-model="DialogConfirmText"></v-text-field>
                </v-col>
            </v-row>
            <template slot="actions">
                <v-col cols="6" xs="6" sm="3" v-if="dialogType!='alert'&& dialogType!='advice'">
                    <material-button @click="dialogCancel()" outlined :text="$t('global.undo')"></material-button>
                </v-col>
                <v-col cols="6" xs="6" sm="3" v-if="dialogType!='typeconfirm'">
                    <material-button @click="dialogOk()" color="success" :text="$t('global.ok')"></material-button>
                </v-col>
                <v-col cols="12" xs="12" sm="3" v-if="dialogType=='typeconfirm'">
                    <material-button @click="dialogOk()" color="success" :text="$t('global.confirm')"></material-button>
                </v-col>
            </template>
        </material-card>
    </material-popup>
</template>
<script>

export default {
    data: () => ({
    }),
    computed: {
        // dialog
        showDialog: {
            get() {
                return this.$store.getters.showDialog;
            },
            set(val) {
                if (!val) this.$store.commit('hideDialog');
            }
        },
        dialogType() {
            return this.$store.getters.dialogType
        },
        dialogTitle() {
            return this.$store.getters.dialogTitle
        },
        dialogMessage() {
            return this.$store.getters.dialogMessage
        },
        DialogConfirmText: {
            get: function () {
                return this.$store.getters.dialogConfirmText;
            },
            set: function (value) {
                this.$store.commit('setDialogConfirmText', value);
            },
        },
        iconSet() {
            let icon = null
            switch (this.dialogType) {
                case 'alert':
                    icon = 'fas fa-exclamation-triangle'
                    break;
                case 'advice':
                    icon = 'far fa-smile-wink'
                    break;
                case 'confirm':
                    icon = 'fas fa-question-circle'
                    break;
                case 'typeconfirm':
                    icon = 'fas fa-clipboard-check'
                    break;
                case 'info':
                    icon = 'far fa-circle-question'
                    break;
            }
            return icon
        },

    },
    methods: {
        dialogConfirm() {
            this.$store.commit('dialogConfirm', this.DialogConfirmText);
            this.DialogConfirmText = '';
        },
        dialogOk() {
            this.$store.commit('dialogOk');
        },
        dialogCancel() {
            this.$store.commit('dialogCancel');
        },
    },
}
</script>