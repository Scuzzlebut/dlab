<template>
    <v-autocomplete v-model="innerValue" hide-details small-chips :loading="relatedstaff_loading" :multiple="multiple" chips :label="multiple ? $t('staff.multiple') : $t('staff.single')" :items="relatedstaff" item-text="fullname" item-value="id" menu-props="closeOnClick, overflowY">
    </v-autocomplete>
</template>

<script>
export default {
    data: () => ({
        innerValue: null,
        nochangeemitted: false
    }),
    props: {
        value: {},
        multiple: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        relatedstaff() {
            return this.$appOptions.relatedStaff()
        },
        relatedstaff_loading() {
            return this.$store.getters.relatedstaff_loading;
        }
    },
    watch: {
        innerValue: {
            handler(newVal,oldVal) {
                this.$emit("input", newVal);
                if (!this.nochangeemitted && oldVal!=newVal){
                    this.$emit("change",newVal);
                }
                this.nochangeemitted=false
            },
            deep: true,
        },
        value: {
            handler(newVal) {
                this.innerValue = newVal;
            },
            deep: true,
        },
    },
    created() {
        this.nochangeemitted = true
        this.innerValue = _.cloneDeep(this.value);
    },
}
</script>
