<template>
    <v-dialog @keydown.esc="closeDialog()" v-show="dialogShowModel" v-model="dialogShowModel" :width="computedWidth" :fullscreen="$vuetify.breakpoint.xsOnly || fullscreen" absolute persistent :content-class="classes" overlay-opacity="0.5" v-bind="$attrs">
        <slot/>
    </v-dialog>
</template>
<script>

export default {
    props: {
        dialogShowModel: {
            type: Boolean,
            default: false
        },
        large: {
            type: Boolean,
            default: false
        },
        fullscreen: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        classes(){
            if (this.$vuetify.breakpoint.xsOnly || this.fullscreen){
                return 'fullscreen-material-dialog material-dialog'
            }
            return 'material-dialog'
        },
        isLarge(){
            if (this.large){
                return true
            }
            if (this.$vuetify.breakpoint.mdAndDown){
                return true
            }
            return false
        },
        computedWidth(){
            let screenwidth = this.$vuetify.breakpoint.width
            let minimum = 0
            let percentage=0
            if (this.isLarge){
                percentage=80
                minimum = 1000
                if (screenwidth*percentage/100 < minimum){
                    percentage = minimum
                }
                else {
                    percentage += '%'
                }
            }
            else {
                percentage=55
                minimum = 800
                if (screenwidth*percentage/100 < minimum){
                    percentage = minimum
                }
                else {
                    percentage += '%'
                }
            }
            return percentage
        }
    },
    methods: {
        closeDialog(){
            this.$emit('escintent')
        }
    },
}
</script>