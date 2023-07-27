<template>
  <v-container  :fluid="!centered" :fill-height="centered && !$vuetify.breakpoint.xsOnly" :justify-center="centered" tag="section" :class="{'px-1': $vuetify.breakpoint.xsOnly}">
    <slot />
  </v-container>
</template>

<script>
  export default {
    props: {
      centered:{
        type: Boolean,
        default: false
      }
    },
    computed: {
    },
  }
</script>
