import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2'
import Components from "unplugin-vue-components/vite";
import {VuetifyResolver} from 'unplugin-vue-components/resolvers'
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
            '@/':`${path.resolve(__dirname, './resources')}/`,
            'src/':`${path.resolve(__dirname, './resources')}/`,
        }
    },
    css: {
        devSourcemap: true,
        preprocessorOptions: {
            sass: {
                additionalData: [
                    '@import "@/assets/styles/overrides"',
                    '@import "@/assets/styles/variables.scss"',
                    '',
                ].join('\n'),
            },
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true
        }),
        vue(),
        Components({
            resolvers: [
                VuetifyResolver()
            ],
            directives: true,
            dts: true
        }),
    ]
});


