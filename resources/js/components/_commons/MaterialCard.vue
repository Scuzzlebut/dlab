<template>
  <v-card
    v-bind="$attrs"
    :class="classes"
    class="v-card--material d-flex flex-column"
    :style="$vuetify.breakpoint.xsOnly ? '{margin-top: 40px !important; }' : ''"
  >
    <v-card-title v-if="logo" v-intersect="{handler: onIntersect,options: {threshold: thresholdList }}">
      <v-row dense align="center" justify="center">
        <v-col cols="12" xs="12" align="center" justify="center">
          <v-img :src="logo" width="300">
            <template v-slot:placeholder>
                <v-sheet>
                <v-skeleton-loader type="image"></v-skeleton-loader>
                </v-sheet>
            </template>
          </v-img>
        </v-col>
      </v-row>
    </v-card-title>

    <div class="d-flex flex-wrap" v-else-if="!noheading" :class="{'dense-card': dense}" v-intersect="{handler: onIntersectGlobal,options: {threshold: thresholdList}}">
      <v-avatar v-if="avatar" size="128" class="mx-auto v-card--material__avatar elevation-6" color="grey">
        <v-img :src="avatar" />
      </v-avatar>
      <v-sheet v-else-if="dense" :color="color" max-height="64" width="auto" elevation="6" class="pa-4 text-start mb-2" dark align="center">
        <slot v-if="$slots.heading" name="heading"/>
        <div v-else-if="title && !icon" class="display-1 font-weight-light" v-text="title"/>
        <v-icon v-else-if="icon" size="32" v-text="icon"/>
        <div v-if="text" class="headline font-weight-thin" v-text="text"/>
      </v-sheet>
      <v-sheet v-else :class="{'pa-7': !$slots.image, 'offset-start' : icon && !isDense}" :color="color" :max-height="icon ? 90 : undefined" :width="icon ? 'auto' : '100%'" elevation="6" class="text-start v-card--material__heading mb-n7" dark>
        <slot v-if="$slots.heading" name="heading"/>
        <slot v-else-if="$slots.image" name="image"/>
        <div v-else-if="title && !icon" class="display-1 font-weight-light" v-text="title"/>
        <v-icon v-else-if="icon" size="32" v-text="icon"/>
        <div v-if="text" class="headline font-weight-thin" v-text="text"/>
      </v-sheet>
      <div v-if="icon && (title || $slots['title'])" class="mx-4">
        <div class="font-weight-normal" v-if="dense">
          <span class="primary--text" v-text="title" ></span>
          <slot name="append-title" />
        </div>
        <v-scroll-x-transition hide-on-leave v-else>
          <div class="card-title font-weight-normal" v-if="selectedCount==0" key="noselect">
            <span class="primary--text" v-text="title" ></span>
            <slot name="append-title" />
          </div>
          <div class="card-title font-weight-bold" v-else key="select">
            <span class="primary--text" v-text="selectedCount"></span>
            <v-btn icon link color="primary" @click="unselectAll()">
              <v-icon>fas fa-times-circle</v-icon>
            </v-btn>
          </div>
        </v-scroll-x-transition>
      </div>
      <v-spacer></v-spacer>
      <v-scroll-x-reverse-transition hide-on-leave>
        <v-btn v-if="can_display_main_action && can_display_duplicate" :id="main_action.id" @click="mainAction()" :loading="main_action.loading" :rounded="$vuetify.breakpoint.xsOnly" :small="$vuetify.breakpoint.xsOnly" color="primary" class="main_action_button" :class="{'mobile-action-button': $vuetify.breakpoint.xsOnly}" outlined :elevation="$vuetify.breakpoint.xsOnly ? 2 : 0" :right="$vuetify.breakpoint.xsOnly" :bottom="$vuetify.breakpoint.xsOnly" :fixed="$vuetify.breakpoint.xsOnly">{{$t('global.duplicate')}}<v-icon small class="pl-1">far fa-copy</v-icon></v-btn>
        <v-btn v-else-if="can_display_main_action" :id="main_action.id" @click="mainAction()" :loading="main_action.loading":rounded="$vuetify.breakpoint.xsOnly" color="primary" class="main_action_button" :class="{'mobile-action-button': $vuetify.breakpoint.xsOnly}" outlined :elevation="$vuetify.breakpoint.xsOnly ? 2 : 0" :right="$vuetify.breakpoint.xsOnly" :bottom="$vuetify.breakpoint.xsOnly" :fixed="$vuetify.breakpoint.xsOnly">
            <v-icon left v-if="main_action.icon">
              {{main_action.icon}}
            </v-icon>
            <span v-if="main_action.text">{{main_action.text}}</span>
        </v-btn>
      </v-scroll-x-reverse-transition>
      <v-scroll-x-reverse-transition hide-on-leave>
        <div v-if="selectedCount>0 && $slots['selection-actions']" align="right" :style="selectionActionsStyle">
            <slot name="selection-actions"/>
        </div>
      </v-scroll-x-reverse-transition>
      <material-export v-if="exportElement.element && selectedCount==0" :element="exportElement.element" :types="exportElement.types"></material-export>
      <v-btn icon large color="primary" @click="$emit('dismissed')" v-if="dismissable" class="dismiss-button">
        <v-icon>fas fa-times-circle</v-icon>
      </v-btn>
      <v-scroll-x-reverse-transition hide-on-leave>
        <v-card flat class="ma-0 table-options warning" v-if="$slots['table-actions'] && !$vuetify.breakpoint.xsOnly" height="36">
          <v-row dense justify="center" align="center" class="mr-9">
            <v-col cols="12">
              <slot name="table-actions" />
            </v-col>
          </v-row>
        </v-card>
        <v-card flat class="ma-0 table-options error" v-else-if="$slots['table-actions-error'] && !$vuetify.breakpoint.xsOnly" height="36">
          <v-row dense justify="center" align="center" class="mr-9">
            <v-col cols="12">
              <slot name="table-actions-error" />
            </v-col>
          </v-row>
        </v-card>
      </v-scroll-x-reverse-transition>
    </div>
    <v-scroll-x-reverse-transition hide-on-leave>
      <v-toolbar flat dense transparent v-if="$vuetify.breakpoint.xsOnly && $slots['table-actions']" class="mt-n4 mb-4">
        <v-spacer></v-spacer>
        <v-card  class="ma-0 mb-2 table-options warning pa-2" dark>
          <v-row dense justify="center" class="mr-7">
            <v-col cols="12" align="right">
              <slot name="table-actions" />
            </v-col>
          </v-row>
        </v-card>
      </v-toolbar>
      <v-toolbar flat dense transparent v-else-if="$vuetify.breakpoint.xsOnly && $slots['table-actions-error']" class="mt-n4 mb-4">
        <v-spacer></v-spacer>
        <v-card  class="ma-0 mb-2 table-options error pa-2" dark>
          <v-row dense justify="center" align="center" class="mr-7">
            <v-col cols="12" align="right">
              <slot name="table-actions-error" />
            </v-col>
          </v-row>
        </v-card>
      </v-toolbar>
    </v-scroll-x-reverse-transition>
    <v-card-text class="pa-0 mt-2 flex-grow-1">
      <div v-if="$slots['after-heading']" class="after-heading text-center text-h5 font-weight-light py-2">
        <slot name="after-heading" />
      </div>
      <div v-if="$slots['text-left']" class="after-heading text-left text-h5 font-weight-light py-2 mb-2">
        <slot name="text-left" />
      </div>
      <v-row dense justify="end" align="end" class="mb-2 mt-n4 px-2" v-if="$slots['filters']" v-intersect="{handler: onIntersect,options: {threshold: thresholdList}}">
        <slot name="filters" />
      </v-row>
      <slot />
    </v-card-text>
    <template v-if="$slots.actions">
      <v-spacer></v-spacer>
      <v-divider class="mt-0 mb-2" />
      <v-card-actions class="pa-0" :class="{'pb-12':($vuetify.breakpoint.xsOnly && !$store.getters.isAppUser)}">
        <v-row  align="center" justify="end" dense>
          <slot name="actions" />
        </v-row>
      </v-card-actions>
    </template>
  </v-card>
</template>

<script>
  export default {
    data: () => ({
      isIntersecting: false,
      previousThreshold: 0,
      thresholdList: [0, 0.05,0.10,0.15,0.20,0.25,0.30,0.35,0.40,0.45,0.5,0.55,0.60,0.65,0.70,0.75,0.80,0.85,0.90,0.95, 1.0]
    }), 
    props: {
      noheading: {
        type: Boolean,
        default: false
      },
      avatar: {
        type: String,
        default: '',
      },
      logo: {
        type: String,
        default: '',
      },
      color: {
        type: String,
        default: "primary",
      },
      exportElement: {
        type: Object,
        default: ()=>{ return {element:null}}
      },
      dismissable:{
        type: Boolean,
        default: false,
      },
      icon: {
        type: String,
        default: undefined,
      },
      image: {
        type: Boolean,
        default: false,
      },
      text: {
        type: String,
        default: '',
      },
      title: {
        type: String,
        default: '',
      },
      selected: {
        type: Array,
        default:  () => []
      },
      main_action: {
        type: Object,
        default: null,
      },
      noduplicate: {
        type: Boolean,
        default: false
      },
      dense: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      selectionActionsStyle(){
        if (this.$vuetify.breakpoint.xsOnly){
          return 'width: 100%;'
        }
        return null
      },
      isDense(){
        return this.$vuetify.breakpoint.xsOnly || this.dense
      },
      can_display_duplicate(){
        if (!this.noduplicate){
          if (this.selectedCount==1){
            return true
          }
        }
        return false
      },
      usersettings() {
          return this.$store.getters.getUserSetting;
      },
      selectedCount(){
        if (this.selected){
          return this.selected.length
        }
        return 0
      },
      can_display_main_action(){
        if (this.main_action){
          if (this.$vuetify.breakpoint.xsOnly){
            if (this.selectedCount<=1){
              if (this.selectedCount==0){
                if (this.isIntersecting){
                  return true
                }
              }
              else {
                if ((this.isIntersecting)&&(!this.noduplicate)){
                  return true
                }
              }
            }
          }
          else {
            if (this.selectedCount<=1){
              if (this.selectedCount==0){
                return true
              }
              else {
                if (!this.noduplicate){
                  return true
                }
              }
            }
          }
        }
        return false
      },
      classes () {
        return {
          'v-card--material--has-heading': this.hasHeading,
          'pa-2': this.isDense,
          'pa-3': !this.isDense && this.$vuetify.breakpoint.smAndUp,
          'v-card--material-dense': this.dense
        }
      },
      hasHeading () {
        return Boolean(this.$slots.heading || this.title || this.icon)
      },
      hasAltHeading () {
        return Boolean(this.$slots.heading || (this.title && this.icon))
      },
    },
    methods: {
      onIntersect (entries, observer) {
        if (entries[0].intersectionRatio > this.previousThreshold && entries[0].intersectionRatio >= 0.95){
          this.isIntersecting=true
        }
        else {
          this.isIntersecting=false
        }
        this.previousThreshold = entries[0].intersectionRatio
      },
      onIntersectGlobal(entries, observer) {
        if (!this.$slots['filters']){
          if (entries[0].intersectionRatio > this.previousThreshold && entries[0].intersectionRatio >= 0.95){
            this.isIntersecting=true
          }
          else {
            this.isIntersecting=false
          }
          this.previousThreshold = entries[0].intersectionRatio
        }
      },
      unselectAll(){
        this.$emit('unselectAll')
      },
      mainAction(){
        let duplicate=null
        if (this.selectedCount==1){
          duplicate=_.cloneDeep(this.selected[0])
        }
        this.$emit('mainAction',duplicate)
      }
    },
  }
</script>

<style lang="sass">
  .v-card--material
    &__avatar
      position: relative
      top: -64px
      margin-bottom: -32px

    &__heading
      position: relative
      top: -40px
      transition: .3s ease
  .v-dialog--fullscreen .v-card
    padding: 8px !important
  .v-dialog--fullscreen .oldcardfix
    padding: 0px !important
</style>