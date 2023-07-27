<template>
    <v-app id="inspire">
        <v-main>
            <server-down v-if="serverdown"></server-down>
            <slot name="content" v-else></slot>
            <material-dialog></material-dialog>
        </v-main> 
        <material-snackbar></material-snackbar>
        <material-dialog></material-dialog>
    </v-app>
</template>

<script>

export default {name: 'AuthLayout',
    computed: {
        //serverdown
        serverdown(){
            return this.$store.getters.serverdownmessage!=null
        },
    },
};
</script>