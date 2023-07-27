import Vue from 'vue'
import VueI18n from 'vue-i18n'

import it from '@/assets/locales/it.json'

Vue.use(VueI18n)

const messages = {
    'it': it
};

const i18n = new VueI18n({
    locale: 'it', // set locale
    fallbackLocale: 'en', // set fallback locale
    messages, // set locale messages
});

export default i18n