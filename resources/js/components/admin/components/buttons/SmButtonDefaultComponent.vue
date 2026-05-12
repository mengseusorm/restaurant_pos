<template>
    <button :class="['db-btn-outline h-[37px] m-0.5 bg-gray-100 text-gray-700', customClass]" type="button">
        <i :class="{ [this.$props.icon]: true }"></i>
        <span>{{ this.$props.span }}</span>
    </button>
</template>

<script>
export default {
    name: "SmButtonDefaultComponent",
    props: {
        span: String,
        icon: String,
        customClass: {
            type: String,
            default: ''
        }
    }
}
</script>

<style scoped>

</style>
