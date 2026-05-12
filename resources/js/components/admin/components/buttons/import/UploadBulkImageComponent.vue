<template>
    <button :data-modal="dataModal" type="button" class="db-card-filter-dropdown-menu">
        <i class="lab lab-line-upload-file lab-font-size-17"></i>
        {{ $t('button.bulk_upload_image') }}
    </button>
</template>

<script>
export default {
    name: "UploadBulkImageComponent",
    props: ['dataModal']
}
</script>
