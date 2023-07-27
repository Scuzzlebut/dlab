<template>
    <div>
        <v-icon small class="pr-1" style="min-width: 24px" v-if="icon">{{icon}}</v-icon>
        <span class="subtitle-1" v-if="name"><strong :class="{'pr-1':value}" v-html="computedName"></strong></span>
        <span v-if="value" v-html="value"></span>
    </div>
</template>

<script>
export default {
    data: () => ({}),
    props: {
        name: {
            type: String,
            default: null
        },
        value: {
            type: [String,Number],
            default: null
        },
        icon: {
            type: String,
            default: null
        },
    },
    computed: {
        computedName(){
            if (this.value){
                return this.name + ':'
            }
            return this.name
        }
    },
}
</script>
