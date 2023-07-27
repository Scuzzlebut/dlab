import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import {Intersect, ClickOutside} from 'vuetify/lib/directives'
import { VueMaskDirective } from 'v-mask'

Vue.use(Vuetify)
Vue.directive('intersect',Intersect)
Vue.directive('click-outside', ClickOutside)
Vue.directive('mask', VueMaskDirective);

const opts = {
    theme: {
        themes: {
            light: {
                primary: '#3366CC', // blu primario
                secondary: '#3AA5F7', // blu secondario per le interfacce
                maingrey: '#8E8E8E', // grigio per i testi principali
                title: '#3C4650', // colore testi per i titoli
                success: '#00CC66', // verde per la conferma / attivazione / password forte
                yellow: '#FFCC00', // giallo per attenzione generica
                warning: '#FF9933', // arancione per gli alert / password media
                error: '#FF3333', // rosso per gli errori / disattivazione / password debole
                savebarcolor: '#BDBDBD', // grigio per la barra di salvataggio
                background: '#E6E9F0' //sfondo tendente al blu
            }
        },
        options: {
            customProperties: true,
        },
        customVariables: ['@/assets/styles/variables'],
        treeShake: true
    },
    icons: {
        iconfont: 'fa',
    }
}

export default new Vuetify(opts)