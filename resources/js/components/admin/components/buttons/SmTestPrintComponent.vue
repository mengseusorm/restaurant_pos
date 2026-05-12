<template>
    <router-link class="db-btn-outline sm primary modal-btn m-0.5"
        :to="{ name: this.$props.link, params: { id: this.$props.id } }">
        <i class="lab lab-printer"></i> <span>{{ $t("button.test_print") }}</span>
    </router-link>
</template>

<script>
export default {
    name: "SmTestPrintComponent",
    props: {
        link: String,
        id: Number
    }
}
</script>

<style scoped>

</style>
