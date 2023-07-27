<template>
    <v-card color="transparent" flat class="TaskDescription TaskDescription--alignedWithLabel" ref="editor_container" v-click-outside="unfocusEditor" >
        <div class="TextEditor2--dynamic TextEditor2--hasStickyBottomBar TaskDescription-textEditor2 TextEditor2" :class="{'TextInput--invalid': $functions.renderErrorMessage(errors, vid, name), 'TextEditor2--focused' : isFocused}" v-if="editor">
            <span class="error--text px-2"  v-if="$functions.renderErrorMessage(errors, vid, name)">{{$functions.renderErrorMessage(errors, vid, name)}}</span>
            <bubble-menu id="bubble-menu" :tippy-options="{ duration: 100, theme: 'light', placement: $store.getters.isTouch ? 'bottom' : 'top' }" :editor="editor" v-if="disable.indexOf('formatting')==-1">
                <template v-if="editor.isActive('link') && disable.indexOf('link')==-1">
                    <span>{{ editor.getAttributes('link').href }}</span>
                    <v-btn small text @click="showInsertLink()">
                        <v-icon small>far fa-edit</v-icon>
                    </v-btn>
                </template>
                <template v-else>
                    <v-btn small text @click.stop="editor.chain().focus().toggleBold().run()" :class="{ 'v-btn--active': editor.isActive('bold') }">
                        B
                    </v-btn>
                    <v-btn small text class="font-italic" @click.stop="editor.chain().focus().toggleItalic().run()" :class="{ 'v-btn--active': editor.isActive('italic') }">
                        I
                    </v-btn>
                    <v-btn small text class="text-decoration-underline" @click.stop="editor.chain().focus().toggleUnderline().run()" :class="{ 'v-btn--active': editor.isActive('underline') }">
                        U
                    </v-btn>
                    <v-btn small text @click.stop="editor.chain().focus().toggleHighlight().run()" :class="{ 'v-btn--active': editor.isActive('highlight') }">
                        <v-icon small>fas fa-highlighter</v-icon>
                    </v-btn>
                </template>
            </bubble-menu>
            <editor-content :editor="editor" class="ProsemirrorEditor TextEditor2-prosemirrorEditor ProseMirror" :class="{'has-error': errors[0]}"/>
            <div id="menubar" class="TextEditorStickyBottomBar--pinned TextEditorStickyBottomBar TextEditor2-stickyBottomBar" :class="{'TextEditorStickyBottomBar--hidden': !isFocused}">
                <div class="TextEditorToolbar TaskDescription-textEditor2Toolbar">
                    <template v-if="disable.indexOf('heading')==-1">
                        <template v-if="isSmallContainer">
                            <v-menu offset-y top max-width="300px" :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn small text :disabled="!isFocused" v-on="on" :class="{ 'v-btn--active': editor.isActive('heading') }">
                                        {{ editor.isActive('heading', { level: 1 }) ? 'H1' : editor.isActive('heading', { level: 2 }) ? 'H2' : editor.isActive('heading', { level: 3 }) ? 'H3' : 'P' }}
                                        <v-icon small class="pl-1 mt-n2">fas fa-caret-up</v-icon>
                                    </v-btn>
                                </template>
                                <v-card flat class="editortableoptions">
                                    <v-card-text class="pa-0 ma-1">
                                        <v-row dense align="center" justify="center">
                                            <v-col cols="12" xs="12">
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 1 }) }">
                                                    H1
                                                </v-btn>
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 2 }) }">
                                                    H2
                                                </v-btn>
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 3 }) }">
                                                    H3
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                </v-card>
                            </v-menu>
                        </template>
                        <template v-else>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 1 }) }">
                                H1
                            </v-btn>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 2 }) }">
                                H2
                            </v-btn>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'v-btn--active': editor.isActive('heading', { level: 3 }) }">
                                H3
                            </v-btn>
                        </template>
                    </template>
                    <template v-if="disable.indexOf('list')==-1">
                        <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleBulletList().run()" :class="{ 'v-btn--active': editor.isActive('bulletList') }">
                            <v-icon small>fas fa-list</v-icon>
                        </v-btn>
                        <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().toggleOrderedList().run()" :class="{ 'v-btn--active': editor.isActive('orderedList') }">
                            <v-icon small>fas fa-list-ol</v-icon>
                        </v-btn>
                    </template>
                    <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()" v-if="disable.indexOf('table')==-1 && !editor.isActive('table')">
                        <v-icon small>fas fa-table</v-icon>
                    </v-btn>
                   <v-btn small text :disabled="!isFocused" @click.stop="insertTwoColumns()" v-if="disable.indexOf('table')==-1 && !editor.isActive('table')">
                        <v-icon small>fas fa-border-none</v-icon>
                    </v-btn>
                    <v-btn small text ref="tablebutton" :disabled="!isFocused" @click.stop="showTableSettings()" v-if="disable.indexOf('table')==-1 && editor.isActive('table')" :class="{ 'v-btn--active': editor.isActive('table') }" class="px-1">
                        <v-icon small>fas fa-table</v-icon>
                        <v-icon small class="pl-1 mt-n2">fas fa-caret-up</v-icon>
                    </v-btn>
                    <v-btn small text :disabled="!isFocused" @click="editor.chain().focus().setHorizontalRule().run()" v-if="disable.indexOf('divider')==-1">
                        <v-icon small>fas fa-minus</v-icon>
                    </v-btn>
                   <v-btn small text :disabled="!isFocused" @click="editor.chain().focus().addPageBreak().run()" v-if="disable.indexOf('pagebreak')==-1">
                        <v-icon small>fas fa-file-waveform</v-icon>
                    </v-btn>
                    <template v-if="disable.indexOf('align')==-1">
                        <v-divider vertical class="ma-0"></v-divider>
                        <template v-if="isSmallContainer">
                            <v-menu offset-y top max-width="300px" :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn small text :disabled="!isFocused" v-on="on" class="v-btn--active px-1">
                                        <v-icon small v-if="editor.isActive({ textAlign: 'left' })">fas fa-align-left</v-icon>
                                        <v-icon small v-if="editor.isActive({ textAlign: 'center' })">fas fa-align-center</v-icon>
                                        <v-icon small v-if="editor.isActive({ textAlign: 'right' })">fas fa-align-right</v-icon>
                                        <v-icon small v-if="editor.isActive({ textAlign: 'justify' })">fas fa-align-justify</v-icon>
                                        <v-icon small class="pl-1 mt-n2">fas fa-caret-up</v-icon>
                                    </v-btn>
                                </template>
                                <v-card flat class="editortableoptions">
                                    <v-card-text class="pa-0 ma-1">
                                        <v-row dense align="center" justify="center">
                                            <v-col cols="12" xs="12">
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('left').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'left' }) }">
                                                    <v-icon small>fas fa-align-left</v-icon>
                                                </v-btn>
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('center').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'center' }) }">
                                                    <v-icon small>fas fa-align-center</v-icon>
                                                </v-btn>
                                                <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('right').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'right' }) }">
                                                    <v-icon small>fas fa-align-right</v-icon>
                                                </v-btn>
                                                <v-btn small text :disabled="!isFocused" @click="editor.chain().focus().setTextAlign('justify').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'justify' }) }">
                                                    <v-icon small>fas fa-align-justify</v-icon>
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                </v-card>
                            </v-menu>
                        </template>
                        <template v-else>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('left').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'left' }) }">
                                <v-icon small>fas fa-align-left</v-icon>
                            </v-btn>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('center').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'center' }) }">
                                <v-icon small>fas fa-align-center</v-icon>
                            </v-btn>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('right').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'right' }) }">
                                <v-icon small>fas fa-align-right</v-icon>
                            </v-btn>
                            <v-btn small text :disabled="!isFocused" @click.stop="editor.chain().focus().setTextAlign('justify').run()" :class="{ 'v-btn--active': editor.isActive({ textAlign: 'justify' }) }">
                                <v-icon small>fas fa-align-justify</v-icon>
                            </v-btn>
                        </template>
                    </template>
                    <template v-if="disable.indexOf('check')==-1">
                        <v-btn small text :disabled="!isFocused" @click.stop="insertSquare()">
                            <v-icon small>far fa-square</v-icon>
                        </v-btn>
                        <v-btn small text :disabled="!isFocused" @click.stop="insertSquareCheck()">
                            <v-icon small>far fa-check-square</v-icon>
                        </v-btn>
                    </template>
                    <template v-if="disable.indexOf('link')==-1 || disable.indexOf('mention')==-1">
                        <v-divider vertical class="ma-0"></v-divider>
                        <v-btn small text ref="linkbutton" :disabled="!isFocused" @click.stop="showInsertLink()" v-if="disable.indexOf('link')==-1">
                            <v-icon small>fas fa-link</v-icon>
                        </v-btn>
                        <v-btn small text :disabled="!isFocused" @click.stop="showInsertMention()" v-if="disable.indexOf('mention')==-1">
                            <v-icon small>fas fa-at</v-icon>
                        </v-btn>
                    </template>
                </div>
            </div>
            <v-menu v-model="showLink" :activator="$refs.linkbutton" offset-y top min-width="300px" :close-on-content-click="false" v-if="showLink">
                <v-card flat>
                    <v-card-text>
                        <v-row dense align="center">
                            <v-col cols="12" xs="12">
                                <v-text-field persistent-hint dense autofocus hint="url (es. https://example.com)" v-model="url.href" id="urlhref" ></v-text-field>
                            </v-col>
                        </v-row>
                        <v-card-actions>
                            <v-row dense align="center" justify="end">
                                <v-col cols="6" xs="6" sm="3">
                                    <material-button small @click="setLink" color="primary" :text="$t('global.ok')"></material-button>
                                </v-col>
                            </v-row>
                        </v-card-actions>
                    </v-card-text>
                </v-card>
            </v-menu>          
            <v-menu v-model="showTable" :activator="$refs.tablebutton" max-width="300px" offset-y top :close-on-content-click="false" v-if="showTable">
                <v-card flat class="editortableoptions">
                    <v-card-text class="pa-0 ma-1">
                        <v-row dense align="center" justify="center">
                            <v-col cols="12" xs="12">
                                <span class="px-1 subheader">colonna</span>
                                <v-btn small text @click="editor.chain().focus().addColumnBefore().run()">
                                    <v-icon small class="pr-1">fas fa-caret-left</v-icon>
                                    aggiungi
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().deleteColumn().run()">
                                    <v-icon small>fas fa-trash-alt</v-icon>
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().addColumnAfter().run()">
                                    aggiungi
                                    <v-icon small class="pl-1">fas fa-caret-right</v-icon>
                                </v-btn>
                                <v-divider class="my-0 mb-1"></v-divider>
                            </v-col>
                            <v-col cols="12" xs="12">
                                <span class="px-1 subheader">riga</span>
                                <v-btn small text @click="editor.chain().focus().addRowBefore().run()">
                                    <v-icon small class="pr-1">fas fa-caret-up</v-icon>
                                    aggiungi
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().deleteRow().run()">
                                    <v-icon small class="pl-1">fas fa-trash-alt</v-icon>
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().addRowAfter().run()">
                                    aggiungi
                                    <v-icon small class="pl-1">fas fa-caret-down</v-icon>
                                </v-btn>
                                <v-divider class="my-0 mb-1"></v-divider>
                            </v-col>
                            <v-col cols="12" xs="12">
                                <span class="px-1 subheader">tabella</span>
                                <v-btn small text @click="editor.chain().focus().mergeCells().run()">
                                    unisci
                                    <v-icon small class="pl-1">fas fa-object-group</v-icon>
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().splitCell().run()">
                                    dividi
                                    <v-icon small class="pl-1">fas fa-table-columns</v-icon>
                                </v-btn>
                                <v-btn small text @click="editor.chain().focus().deleteTable().run()">
                                    elimina
                                    <v-icon small class="pl-1">fas fa-trash-alt</v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-menu>
        </div>
    </v-card>
</template>

<script>
import { Editor, EditorContent, BubbleMenu,VueRenderer } from '@tiptap/vue-2'
import TextAlign from '@tiptap/extension-text-align'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Heading from '@tiptap/extension-heading'
import Text from '@tiptap/extension-text'
import Highlight from '@tiptap/extension-highlight'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Mention from '@tiptap/extension-mention'
import Table from '@tiptap/extension-table'
import TableRow from '@tiptap/extension-table-row'
import TableCell from '@tiptap/extension-table-cell'
import TableHeader from '@tiptap/extension-table-header'
import Gapcursor from '@tiptap/extension-gapcursor'
import Blockquote from '@tiptap/extension-blockquote'
import OrderedList from '@tiptap/extension-ordered-list'
import BulletList from '@tiptap/extension-bullet-list'
import ListItem from '@tiptap/extension-list-item'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import HardBreak from '@tiptap/extension-hard-break'


import tippy from 'tippy.js';
import MentionList from './MentionList.vue'
import pagebreakComponent from "./PageBreak.js";
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light.css';

export default {
    components: {
        EditorContent,
        BubbleMenu
    },
    data: () => ({
        innerValue: null,
        nochangeemitted: false,
        editor: null,
        url: {
            href: null,
        },
        showLink: false,
        showTable: false,
        focused: false,
        isSmallContainer: false
    }),
    props: {
        value: {},
        vid: {
            type: String,
            default: null
        },
        name: {
            type: String,
            default: null
        },
        errors: {
            type: [Array,Object],
            default: () => []
        },
        disable: {
            type: [Array],
            default: () => ['check']
            //possibili:
            //heading,list,table,divider,pagebreak,align,check,link,mention,formatting
        },
        disabled:{
            type: Boolean,
            default: false
        },
        suggestions: {
            type: [Array],
            default: () => [
                {id: 1, label: 'Voglio'},
                {id: 2, label: 'acquistare'},
                {id: 3, label: 'Associami'}
            ]
        },
        toolbaralwaysvisible: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        isFocused(){
            return (this.focused || this.editor.isFocused || this.showLink || this.showTable || this.toolbaralwaysvisible)
        }
    },
    methods: {
        checkContainerWidth(){
            if (this.$vuetify.breakpoint.xsOnly || this.$refs.editor_container.$el.clientWidth<350){
                this.isSmallContainer= true
            }
            else {
                this.isSmallContainer= false
            }
        },
        unfocusEditor(){
            if (!this.showLink && !this.showTable){
                this.focused=false
            }
        },
        showInsertLink(){
            this.url= {
                href: null,
                title: null
            }
            let selected = this.editor.view.state.selection
            if (!selected.empty){
                this.url.title=selected.content().content.firstChild.textContent
            }
            if (this.editor.isActive('link')){
                this.url.href=this.editor.getAttributes('link').href
            }
            this.editor.chain().focus()
            this.showLink=true
        },
        setLink(){
            const self=this
            this.focused=true
            let isValid=false
            if (self.url.href!='' && self.url.href!=null){
                if (self.$functions.isValidURL(self.url.href)){
                    isValid=true
                }
            }
            self.url.href = self.url.href.toLowerCase()
            if (!self.url.href.startsWith('https://') && !self.url.href.startsWith('http://')){
                self.url.href = 'https://' + self.url.href
            }
            if (isValid){
                let selected = this.editor.view.state.selection
                if (!selected.empty){
                    this.editor.chain().focus().setLink({ href: self.url.href, target: '_blank' }).run()
                }
                else {
                    this.editor.chain().focus().insertContent("<a href='" + this.url.href + "'>" + this.url.href + "</a>").extendMarkRange('link')
                    .setLink({ href: self.url.href, target: '_blank' })
                    .run()
                }
                this.showLink=false
            }
            else {
                this.$store.commit('showSnackbar', {
                    message: self.$t('htmleditor.notvalidurl'),
                    color: 'error'
                })
            }
        },
        insertSquare(){
            this.editor.chain().focus().insertContent("⬜").run()
        },
        insertSquareCheck(){
            this.editor.chain().focus().insertContent("⬛").run()
        },
        showInsertMention(){
            this.editor.chain().focus().insertContent('@').run()
        },
        showTableSettings(){
            this.editor.chain().focus()
            this.showTable=true
        },
        insertTwoColumns(){
            this.editor.chain().focus().insertContent("<table class='noborder'><tbody><tr><td> </td><td> </td></tr></tbody></table>").run()
        }
    },
    watch: {
        disabled(val){
            this.editor.setEditable(!val)
        },
        focused(val){
            this.checkContainerWidth()
        },
        innerValue(newVal) {
            this.$emit("input", newVal);
            this.$nextTick(() => {
                if (!this.nochangeemitted){
                    this.$emit("change");
                }
                this.nochangeemitted=false
            })
        },
        value(newVal) {
            this.innerValue = _.cloneDeep(newVal);
            if (newVal != this.editor.getHTML()) {
                if (this.innerValue && this.editor){
                    let replaced = _.cloneDeep(this.innerValue)
                    //convert mentions
                    if (this.disable.indexOf('mention')==-1){
                        if (replaced){
                            this.suggestions.forEach(function (element) {
                                let replace_with ='<span data-type="mention" class="mention" data-id="'+ element.label + '" key="' + element.id + '" contenteditable="false">@' + element.label + '</span>'
                                while (replaced.includes(element.jvalue)) {
                                    replaced = _.replace(replaced, element.jvalue, replace_with);
                                }
                            }) 
                        }
                    }
                    this.editor.commands.setContent(replaced)
                }
            }
        },
    },
    created() {
        if (this.value) {
            this.nochangeemitted = true
            this.innerValue = _.cloneDeep(this.value);
        }
    },
    mounted() {
        const self=this
        const extensions = [
            Document,
            Paragraph,
            Text,
            HardBreak,
            Blockquote,
            Bold,
            Italic,
            Highlight,
            Placeholder.configure({
                placeholder: self.$t('htmleditor.write_something'),
            }),
            Underline,
        ]
        
        if (this.disable.indexOf('divider')==-1){
            extensions.push(HorizontalRule)
        }
        if (this.disable.indexOf('pagebreak')==-1){
            extensions.push(pagebreakComponent)
        }
        if (this.disable.indexOf('list')==-1){
            extensions.push(OrderedList)
            extensions.push(BulletList)
            extensions.push(ListItem)
        }
        if (this.disable.indexOf('heading')==-1){
            extensions.push(Heading)
        }
        if (this.disable.indexOf('table')==-1){
            extensions.push(Gapcursor)
            const CustomTable = Table.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        class: {
                            default: null,
                            // Customize the HTML parsing
                            parseHTML: element => element.getAttribute('class'),
                            // … and customize the HTML rendering
                            renderHTML: attributes => {
                                if (attributes.class) {
                                    return {
                                        'class': attributes.class
                                    }
                                }
                            },
                        },
                        resizable: true
                    }
                },
                renderHTML({ HTMLAttributes }) {
                    return ['div',{class: 'tableWrapper'},['table', HTMLAttributes,['tbody', 0]]]
                }
            });
            extensions.push(CustomTable)
            extensions.push(TableRow)
            extensions.push(TableHeader)
            extensions.push(TableCell)
        }
        if (this.disable.indexOf('align')==-1){
            extensions.push(TextAlign.configure({
                types: ['heading', 'paragraph'],
            }))
        }
        if (this.disable.indexOf('link')==-1){
            extensions.push(Link.configure({
                openOnClick: true,
            }))
        }
        if (this.disable.indexOf('mention')==-1){
            const CustomMention = Mention.extend({
                addAttributes() {
                    return {
                        ...this.parent(),
                        key: {
                            default: null,
                        }
                    }
                }
            })
            extensions.push(CustomMention.configure({
                HTMLAttributes: {
                    class: 'mention',
                },suggestion: {
                    items: ({ query }) => {
                        return self.suggestions.filter(item => item.label.toLowerCase().startsWith(query.toLowerCase()))
                    },
                    render: () => {
                        let component
                        let popup

                        return {
                            onStart: props => {
                                component = new VueRenderer(MentionList, {
                                // using vue 2:
                                    parent: this,
                                    propsData: props,
                                // using vue 3:
                                    //props,
                                    //editor: props.editor,
                                })

                                popup = tippy('body', {
                                    getReferenceClientRect: props.clientRect,
                                    appendTo: () => document.body,
                                    content: component.element,
                                    showOnCreate: true,
                                    interactive: true, 
                                    theme:  'light', 
                                    trigger: 'manual',
                                    placement: 'bottom-start',
                                })
                            },
                            onUpdate(props) {
                                component.updateProps(props)
                                popup[0].setProps({
                                    getReferenceClientRect: props.clientRect,
                                })
                            },
                            onKeyDown(props) {
                                if (props.event.key === 'Escape') {
                                    popup[0].hide()
                                    return true
                                }
                                return component.ref?.onKeyDown(props)
                            },
                            onExit() {
                                popup[0].destroy()
                                component.destroy()
                            },
                        }
                    },
                }
            }))
        }
        let replaced = _.cloneDeep(this.innerValue)
        //convert mentions
        if (this.disable.indexOf('mention')==-1){
            if (replaced){
                this.suggestions.forEach(function (element) {
                    let replace_with ='<span data-type="mention" class="mention" data-id="'+ element.label + '" key="' + element.id + '" contenteditable="false">@' + element.label + '</span>'
                    while (replaced.includes(element.jvalue)) {
                        replaced = _.replace(replaced, element.jvalue, replace_with);
                    }
                }) 
            }
        }
        this.editor = new Editor({
            content: replaced,
            extensions: extensions,
            editable: !this.disabled,
            editorProps: {
                handleDOMEvents: {
                    drop: (view, e) => { 
                        e.preventDefault();
                    },
                }
            },
            onUpdate: () => {
                this.innerValue=this.editor.getHTML()
            },
            onFocus: () => {
                this.focused=true
            }
        })
        this.$nextTick(() => {
            this.checkContainerWidth()
        })
    },
    beforeDestroy() {
        this.editor.destroy()
    },
}
</script>
