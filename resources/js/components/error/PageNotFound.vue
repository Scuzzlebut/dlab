<template>
    <div>
        <h1>404 Error</h1>
        <p>The resource could not be found.</p>
    </div>
</template>

<script>
export default {
    beforeCreate() {
        window.location.href = '/notfound'
    },
}

</script>