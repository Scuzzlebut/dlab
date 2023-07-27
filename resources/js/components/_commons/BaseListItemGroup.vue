<template>
  <v-list-group
    :group="group"
    :prepend-icon="item.icon"
    :sub-group="subGroup"
    @click.stop
  >
    <template v-slot:activator>
      <v-list-item-icon
        v-if="text"
        class="v-list-item__icon--text"
        v-text="computedText"
      />

      <v-list-item-avatar
        v-else-if="item.avatar"
        class="align-self-center"
        color="white"
        contain
      >
        
      </v-list-item-avatar>

      <v-list-item-content>
        <v-list-item-title v-text="item.title" />
      </v-list-item-content>
    </template>

    <template v-for="(child, i) in children">
      <base-listitem
        :key="`item-${i}`"
        :item="child"
        text
      />
    </template>
  </v-list-group>
</template>

<script>
  export default {
    props: {
      item: {
        type: Object,
        default: () => ({
          avatar: undefined,
          group: undefined,
          title: undefined,
          children: [],
        }),
      },
      subGroup: {
        type: Boolean,
        default: false,
      },
      text: {
        type: Boolean,
        default: false,
      },
    },

    computed: {
      children () {
        return this.item.children.map(item => ({
          ...item,
          to: !item.to ? undefined : `${this.item.group}/${item.to}`,
        }))
      },
      computedText () {
        if (!this.item || !this.item.title) return ''

        let text = ''

        this.item.title.split(' ').forEach(val => {
          text += val.substring(0, 1)
        })

        return text
      },
      group () {
        return this.genGroup(this.item.children)
      },
    },

    methods: {
      genGroup (children) {
        return children
          .filter(item => item.to)
          .map(item => {
            const parent = item.group || this.item.group
            let group = `${parent}/${_.kebabCase(item.to)}`

            if (item.children) {
              group = `${group}|${this.genGroup(item.children)}`
            }

            return group
          }).join('|')
      },
    },
  }
</script>

<style>
.v-list-group__activator p {
  margin-bottom: 0;
}
</style>
