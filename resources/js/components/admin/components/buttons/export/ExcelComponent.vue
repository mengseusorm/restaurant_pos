<template>
    <a href="#" @click.prevent="excelDownload" class="db-card-filter-dropdown-menu">
        <i class="lab lab-file-excel lab-font-size-15"></i>
        {{ $t(title) }}
    </a>
</template>

<script>

export default {
    name: "ExcelComponent",
    props: {
        method: { type: Function },
        title: {
            type: String,
            default: 'button.excel'
        }
    },
    methods: {
        excelDownload: function () {
            this.method();
        }
    }
}
</script>
