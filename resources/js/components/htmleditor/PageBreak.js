import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-2'
import Component from './PageBreakComponent.vue'

export default Node.create({
    name: 'pagebreakComponent',
    inline:true,
    group:'inline',
    parseHTML() {
        return [
            {
                tag: 'pagebreak',
            },
        ]
    },
    renderHTML({ HTMLAttributes }) {
        return ['pagebreak', mergeAttributes(HTMLAttributes)]
    },
    addNodeView() {
        return VueNodeViewRenderer(Component)
    },
    addCommands() {
        return {
            addPageBreak: (attrs) => ({ state, dispatch }) => {
                const { selection } = state
                const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos
                const node = this.type.create(attrs)
                const transaction = state.tr.insert(position, node);
                dispatch(transaction);
            }
        }
    }
})