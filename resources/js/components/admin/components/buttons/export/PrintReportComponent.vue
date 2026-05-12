<template>
    <button class="db-card-filter-dropdown-menu">
        <i class="lab lab-printer-line lab-font-size-17"></i>
        {{ $t('button.print') }}
    </button>
</template>
<script> 
export default {
    name: "PrintReportComponent.vue",
    props: ['props'],
}
</script>
