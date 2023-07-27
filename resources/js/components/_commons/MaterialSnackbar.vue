<template>
    <v-snackbar :timeout="snackbarDuration" :color="snackbarColor" top v-model="showSnackbar">
        {{ snackbarMessage }}
    </v-snackbar>
</template>

<script>
  export default {
    computed: {
        showSnackbar: {
            get() {
                return this.$store.getters.showSnackbar;
            },
            set(val) {
                if (!val) this.$store.commit('hideSnackbar');
            }
        },
        snackbarMessage() {
            return this.$store.getters.snackbarMessage;
        },
        snackbarColor() {
            return this.$store.getters.snackbarColor;
        },
        snackbarDuration() {
            return this.$store.getters.snackbarDuration;
        },
    },
  }
</script>