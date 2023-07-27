<template>
    <v-btn v-if="!$vuetify.breakpoint.xsOnly" color="primary" class="ml-2 px-2" min-width="0" outlined @click.stop="exportModel('xls')" :disabled="model_exporting" :loading="model_exporting">
        <v-icon>fas fa-download</v-icon>
    </v-btn>
</template>

<script>
export default {
    data: () => ({}),
    props: {
        element: {
            type: String,
            default: null,
        },
        types: {
            type: Array,
            default: () => {
                return array("xls");
            },
        },
    },
    computed: {
        model_exporting() {
            return this.$store.getters[this.element.toLowerCase() + "_exporting"];
        },
    },
    methods: {
        exportModel(type) {
            this.$store.dispatch("export" + this.element, type);
        },
    },
};
</script>
