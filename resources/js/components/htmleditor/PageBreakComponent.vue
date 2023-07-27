<template>
  <node-view-wrapper class="pagebreakcomponent">
    <v-divider class="my-1 error--text" style="border-top: 2px dashed;"></v-divider>
  </node-view-wrapper>
</template>

<script>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-2'

export default {
  components: {
    NodeViewWrapper,
  },
  props: {
      nodeViewProps
  },
}
</script>
