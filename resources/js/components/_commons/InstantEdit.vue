<template>
    <material-popup v-bind="$attrs" :dialogShowModel="innerValue">
        <material-card :fullscreenadapter="$vuetify.breakpoint.xsOnly" :icon="icon" :title="title" :color="color" dismissable @dismissed="innerValue=false">
            <template slot="after-heading">
                <slot name="afterheading" />
            </template>
            <slot/>
            <template slot="actions">
                <slot name="instantactions" />
                <v-col cols="6" xs="6" sm="3" v-if="!$slots.instantactions">
                    <material-button large @click="closeAndUpdate()" color="success" :text="$t('global.ok')"></material-button>
                </v-col>
            </template>
        </material-card>
    </material-popup>
</template>

<script>
export default {
    data: () => ({
        innerValue: false,
    }),
    props: {
        value: {
            type: Boolean,
            default: false
        },
        icon: {
            type: String,
            default: null
        },
        title: {
            type: String,
            default: null
        },
        color: {
            type: String,
            default: 'primary'
        }
    },
    watch: {
        innerValue(newVal) {
            this.$emit("input", newVal);
            this.$nextTick(() => {
                this.$emit("change");
            })
        },
        value(newVal) {
            this.innerValue = _.cloneDeep(newVal);
        },
    },
    methods: {
        closeAndUpdate(){
            this.$emit('closeandupdate')
            this.innerValue=false
        }
    },
    created() {
        if (this.value) {
            this.innerValue = _.cloneDeep(this.value);
        }
    },
}
</script>
