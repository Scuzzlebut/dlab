<template>
    <v-btn :color="color" @click.stop="clicked()" :small="small" :outlined="outlined" :large="large" :block="(large || block) && !small" :disabled="disabled" :loading="loading"><span v-if="text">{{text}}</span>
        <slot/>
    </v-btn>
</template>

<script>
export default {
    data: () => ({}),
    props: {
        text: {
            type: String,
            default: null
        },
        color: {
            type: String,
            default: null
        },
        large: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        outlined: {
            type: Boolean,
            default: false
        },
        small: {
            type: Boolean,
            default: false
        },
        block: {
            type: Boolean,
            default: true
        },
    },
    methods: {
        clicked() {
            this.$emit("click");
        }
    }
}
</script>
