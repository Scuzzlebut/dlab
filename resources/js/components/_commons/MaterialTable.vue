<template>
  <v-data-table dense :id="id" class="maingrey--tex" @click:row="handleClick" :height="popup ? tableHeight : null" :headers="computedHeaders"  :fixed-header="popup" :footerProps="{'items-per-page-options': [10, 15, 50, -1]}" v-model="temp_selected" v-bind="$attrs" :class="classes" mobile-breakpoint="0" :options.sync="temp_options" :item-key="itemkey">
      <template v-slot:item.data-table-select="{ isSelected, select }">
        <v-scroll-x-transition>
          <v-simple-checkbox
            color="primary"
            on-icon="fas fa-check"
            :ripple="false"
            :value="isSelected"
            @input="select($event)"
          ></v-simple-checkbox>
        </v-scroll-x-transition>
      </template>
      <template v-slot:header.filter="{item}">
        <v-menu bottom right>
          <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" small>
                  <v-icon small>fas fa-filter</v-icon>
              </v-btn>
          </template>
          <v-list>
            <v-list-item-group multiple active-class="">
              <v-list-item v-for="headeritem in temporaryheaders" :key="headeritem.value" v-if="!headeritem.hideselect">
                <v-list-item-action>
                  <v-checkbox v-model="headeritem.selected" @change="value => saveToStorage(headeritem.value,value)"></v-checkbox>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>{{headeritem.selectiontext ? headeritem.selectiontext : headeritem.text}}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-menu>
      </template>
      <template v-for="slot in Object.keys($scopedSlots)" :slot="slot" slot-scope="scope"><slot :name="slot" v-bind="scope"/></template>
      <template slot="no-data">
          <slot-no-data></slot-no-data>
      </template>
  </v-data-table>
</template>

<script>
  export default {
    data: () => ({
    }),
    props: {
        id: {
          type: String,
          default: null
        },
        options: [Object,Array],
        selected: [Object,Array],
        headers: [Object,Array],
        popup: {
            type: Boolean,
            default: false
        },
        itemkey: {
          type: String,
          default: 'id'
        }
    },
    computed: {
      canSavePreference(){
        if (_.isObject(this.headers)){
          if (this.headers.id!=null){
            return true
          }
        }
        return false
      },
      temporaryheaders(){
        if (this.canSavePreference){
          if (this.storedHeadersSelections.length>=0){
            let elements = _.cloneDeep(this.headers.data)
            let selected = _.cloneDeep(this.storedHeadersSelections)
            elements.push(
              {         
                text: '',
                hideselect: true,
                value: 'filter',
                align: 'right',
                sortable: false,
                selected: true,
                width:'1%'
              }
            )
            Object.keys(elements).forEach(elkey => {
              let $finded = selected.findIndex(obj => obj.id == elements[elkey].value)
              if ($finded!=-1){
                elements[elkey].selected = selected[$finded].value
              }
              else {
                if (!elements[elkey].hasOwnProperty('selected')){
                  elements[elkey].selected = true
                }
              }
            });
            return elements
          }
        }
        else {
          return this.headers
        }
      },
      computedHeaders(){
        if (this.canSavePreference){
          return _.filter(this.temporaryheaders, function(obj) {
            return obj.selected==true;
          });
        }
        return this.temporaryheaders
      },
      savedProperies(){
        return this.storedHeadersSelections.length
      },
      headersPreference: {
        get: function(){
          return this.$store.getters.getHeadersPreference
        },
        set: function (value) {
          this.$store.commit('setHeadersPreference', value);
        }
      },
      storedHeadersSelections:{
        get: function(){
          return this.headersPreference[this.headers.id] || []
        },
        set: function (value) {
          let global = _.cloneDeep(this.headersPreference)
          global[this.headers.id]=value
          if (value.length==0){
            delete global[this.headers.id]
          }
          this.headersPreference = JSON.parse(JSON.stringify(global))
        }
      },
      tableHeight(){
          if (this.$vuetify.breakpoint.xsOnly){
              return "65vh"
          }
          else {
              return "50vh"
          }
      },
      temp_options: {
        get: function () {
            return this.options;
        },
        set: function (value) {
            this.$emit('update:options', value);
        },
      },
      temp_selected: {
        get: function () {
          if (this.selected){
            return this.selected;
          }
          return []
        },
        set: function (value) {
            this.$emit('update:selected', value);
        },
      },
      classes () {
        return {
          'mobilepagination': this.isMobile,
          'v-clickable': true,
          'table-popup': this.popup
        }
      },
      isMobile () {
        return Boolean(this.$vuetify.breakpoint.xsOnly)
      },
      hasClickAction(){
        return this.$listeners && this.$listeners['click-action']
      }
    },
    methods: {
      saveToStorage(index,value){
        if (value==null){
          value=false
        }
        let cloned = _.cloneDeep(this.storedHeadersSelections)
        let $finded = cloned.findIndex(obj => obj.id == index)

        let original = this.headers.data.findIndex(obj => obj.value==index)
        let deletefromstorage=false
        if (original!=-1){
          if (this.headers.data[original].selected==true && value==true){
            deletefromstorage=true
          }
          if (this.headers.data[original].selected==false && value==false){
            deletefromstorage=true
          }
        }
        if ($finded!=-1){
          if (deletefromstorage){
            cloned.splice($finded, 1)
          }
          else {
            cloned[$finded].value=value
          }
        }
        else{
          if (!deletefromstorage){
            cloned.push({id: index,value:value})
          }
        }
        this.storedHeadersSelections= JSON.parse(JSON.stringify(cloned))
      },
      handleClick(item,related){
        if (this.temp_selected.length>0){
          related.select(!related.isSelected)
          this.$nextTick(() => {
              this.$emit('changedselect',related)
          })   
        }
        else {
          if (!this.hasClickAction){
            related.select(!related.isSelected)
            this.$nextTick(() => {
                this.$emit('changedselect',related)
            })
          }
          else {
            this.$emit('click-action',item)
          }
        }
      },
    },
    created() {
      if (this.temp_options){
        this.temp_options.itemsPerPage = this.$appOptions.defaultRowsPerPage()
      }
    },
  }
</script>

<style> 
.v-clickable {  
cursor: pointer;
}
</style>
