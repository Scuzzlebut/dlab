<template>
<v-text-field type="search" :class="{'py-0':dense, 'my-0':dense}" v-model="search" :color="nocolor==false ? 'background' : ''" append-icon="fas fa-search" :label="$t('global.search')" single-line hide-details @click:append="LaunchSearch()" @change="LaunchSearch()" @click:clear="LaunchSearch(true)" clearable :autofocus="autofocus" @input="debouncedMethods"></v-text-field>
</template>

<script>
export default {
    data: () => ({
        search: null,
        innerValue: null,
        debouncedMethods:null,
        nochangeemitted: false
    }),
    props: {
        value: {
            type: String,
            default: null
        },
        dense: {
            type: Boolean,
            default: false
        },
        remember: {
            type: Boolean,
            default: false
        },
        autofocus: Boolean,
        nocolor: Boolean,
    },
    methods: {
        LaunchSearch(reset) {
            if (reset) {
                this.innerValue = null
                this.$nextTick(() => {
                    this.$emit('cleared')
                })
            } else {
                if (this.search != this.innerValue) {
                    this.innerValue = _.cloneDeep(this.search)
                }
            }
        },
        DebounceSearch(){
            this.LaunchSearch();
        }
    },
    computed: {
        is_loading() {
            return this.$store.getters.is_loading;
        },
    },
    watch: {
        innerValue(newVal) {
            this.$emit("input", newVal);
            this.$nextTick(() => {
                if (!this.nochangeemitted){
                    this.$emit("change",newVal);
                }
                this.nochangeemitted=false
            })
        },
        value(newVal) {
            this.innerValue = _.cloneDeep(newVal);
        },
    },
    created() {
        if (this.value) {
            this.nochangeemitted = true
            this.innerValue = _.cloneDeep(this.value);
            this.search=_.cloneDeep(this.value);
        }
        const self=this
        this.debouncedMethods = _.debounce(function(){self.DebounceSearch()}, 1000);
    },
    beforeDestroy() {
        if (!this.remember){
            this.$emit("input", null);
            this.innerValue = null
        }
    },
}
</script>
