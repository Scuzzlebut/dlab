
<style media="screen">
    .imagezoom {
        background-position: 50% 50%;
        position: relative;
        border: 5px solid white;
        box-shadow: -1px 5px 15px black;
        overflow: hidden;
        cursor: zoom-in;
        opacity:0;
        transition: opacity 0.5s;
    }
    .imagezoom:hover {
        opacity:1
    }
</style>
<template>
    <v-hover v-slot:default="{ hover }">
        <v-img :src="src" max-height="330" contain @mousemove="zoom" @click.stop="$emit('click')">
            <template v-slot:placeholder>
                <v-sheet>
                <v-skeleton-loader type="image"></v-skeleton-loader>
                </v-sheet>
            </template>
            <div class="imagezoom clickarea" :style="'height: 100%; width: 100%; background-image: url(' + src +');'">
            </div>
        </v-img>
    </v-hover>
</template>

<script>
export default {
    data: () => ({}),
    props: {
        src: {
            type: String,
            default: null
        },
    },
    methods: {
        zoom(e){
            var zoomer = document.getElementsByClassName('imagezoom');
            if (zoomer){
                zoomer = zoomer[0]
                let offsetX=0
                let offsetY=0
                if (e.offsetX){
                    offsetX = e.offsetX
                }
                else {
                    if (e.touches){
                        offsetX = e.touches[0].pageX
                    }
                }
                if (e.offsetY){
                    offsetY = e.offsetY
                }
                else {
                    if (e.touches){
                        offsetX = e.touches[0].pageX
                    }
                }
                let x = offsetX/zoomer.offsetWidth*50
                let y = offsetY/zoomer.offsetHeight*50
                zoomer.style.backgroundPosition = x + '% ' + y + '%';
            }

        }
    },
}
</script>
