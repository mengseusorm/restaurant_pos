<template>
    <button class="db-btn h-[37px] text-white bg-primary m-0.5" type="button">
        <i :class="{ [this.$props.icon]: true }"></i>
        <span>{{ this.$props.span }}</span>
    </button>
</template>

<script>
export default {
    name: "SmButtonComponent",
    props: {
        span: String,
        icon: String
    }
}
</script>

<style scoped>

</style>
