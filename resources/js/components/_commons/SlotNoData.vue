<template>
    <div class="text-center">
        <v-alert :value="true" color="transparent">
            <template v-slot:prepend>
                <v-icon large color="warning">fas fa-exclamation</v-icon>
            </template>
            <span class="maingrey--text" v-if="message">{{message}}</span>
            <span class="maingrey--text" v-else>{{ $t('global.nothing_to_display') }}</span>
            <template v-slot:append>
                <v-icon large color="warning">fas fa-exclamation</v-icon>
            </template>
        </v-alert>
    </div>
</template>

<script>
export default {
    data: () => ({}),
    props: {
        message: String,
    },
}
</script>
